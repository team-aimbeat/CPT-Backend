@push('styles')
<link rel="stylesheet" type="text/css" href="{{asset('package/swiper/swiper-bundle.min.css')}}" media="screen" />
  />
@endpush
@push('scripts')
    {{ $dataTable->scripts() }}
    <script src="{{asset('package/swiper/swiper-bundle.min.js')}}"></script>

    <script>
      var gridPagination = {
         current_page:1,
         per_page: 6,
      }
      function swapSlider(id){
         new Swiper(id+" .hpSwiper-img", {
            loop: true,
            spaceBetween: 10,
            navigation: {
                nextEl: id+" .swiper-button-next",
                prevEl: id+" .swiper-button-prev",
            },
            on: {
                            transitionStart: function(){
                            
                              //   var videos = document.querySelectorAll('video');

                              //   if(videos){
                              //       Array.prototype.forEach.call(videos, function(video){
                              //           video.pause();
                              //       });
                              //   }

                                var $videos = $(id+" video");

                                 if ($videos.length > 0) {
                                    $videos.each(function() {
                                       $(this).get(0).play(); // or this.pause();
                                    });
                                 }
                            },
                            
                            transitionEnd: function(){
                            
                                var activeIndex = this.activeIndex;
                                var activeSlide = document.getElementsByClassName(id+" swiper-slide")[activeIndex];
                                if(activeSlide && activeSlide.getElementsByTagName(id+" video")){
                                    var activeSlideVideo = activeSlide.getElementsByTagName(id+" video")[0];
                                    activeSlideVideo.play();
                                }
                            
                            },
                        
                        },
            thumbs: {
                swiper: new Swiper(id+" .hpSwiper-small", {
                    loop: true,
                    spaceBetween: 10,
                    slidesPerView: 4,
                    freeMode: true,
                    watchSlidesProgress: true,
                    
                }),
            },
        });
      }
      function getNestedValue(data, key) {
         const keys = key.split('.');
         let current = data;

         for (const part of keys) {
            if (typeof current === 'object' && current !== null && part in current) {
               current = current[part];
            } else {
               return null; // Key not found
            }
         }

         return current;
      }
      function replaceDotsWithDashes(key) {
         return key.replace(/\./g, '-');
      }
      function getGridData(page=1) {
         $(".grid-view").addClass('loading');
         var postData = {
            start: (page-1) * gridPagination.per_page,
            length: gridPagination.per_page,
            columns: {!! json_encode($gridColumn) !!},
            search: {
               regex: false,
               value: $("#searchGridBox").val()
            }
         };
         $.ajax({
            type: 'GET',
            url: "{{ route('workout.list.paginate') }}",
            data: postData,
            dataType: "JSON",
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            success: function(resultData) {
               // swapSlider();
               var gridhtml = `
               <div>
                     <div class="col-md-6 col-lg-4">
                        <div class="card">
                           <div class="card-header">
                           </div>
                           <div class="card-body">`;

               postData.columns.forEach(col => {
                  gridhtml += `
                              <div class="row">
                                 <div class="col-5">
                                    <label>${col.data!='action' ? col.title : ''}</label>
                                 </div>
                                 <div class="col-7 details dt-${replaceDotsWithDashes(col.data)}">-</div>
                              </div>`;
               });
               gridhtml += `
                           </div>
                        </div>
                     </div>
                     </div>
                  `; 
               var fullHtml = '<div class="row">';
                  
               resultData.data.forEach(item => {
                  var html = gridhtml;
                  var $html = $(html);
                  $html.find('.card-header').html(item.img);
                  postData.columns.forEach(col => {
                     $html.find('.dt-'+replaceDotsWithDashes(col.data)).html(getNestedValue(item, col.data));
                  });
                  $html.find('.dt-level').html(item.level.title);
                  $html.find('.dt-workout_type').html(item.workout_type.title);
                  $html.find('.dt-status').html(item.status);
                  $html.find('.dt-created_at').html(item.created_at);
                  $html.find('.dt-updated_at').html(item.updated_at);
                  $html.find('.dt-action').html(item.action);
                  fullHtml += $html.html();
               });
               fullHtml += '</div><div id="pagination-container"><ul class="pagination justify-content-center"></ul></div>';
               $(".grid-responsive").html(fullHtml);
               resultData.data.forEach(item => {
                  swapSlider(".images-box.box-id-"+item.id);
               });

               generatePagination(resultData.recordsTotal, postData.length, page);
               $('#pagination-container').on('click', 'a.page-link', function(event) {
                  event.preventDefault();
                  var page = $(this).data('page');
                  if (page) {
                     currentPage = page;
                     getGridData(currentPage);
                  }
               });
               $(".grid-view").removeClass('loading');
            }
         });
      }
      function generatePagination(totalItems, itemsPerPage, currentPage) {
         var totalPages = Math.ceil(totalItems / itemsPerPage);
         var paginationContainer = $('#pagination-container ul');
         paginationContainer.empty(); // Clear previous pagination

         if (totalPages <= 1) return; // No pagination needed

         // Previous link
         paginationContainer.append('<li class="page-item ' + (currentPage === 1 ? 'disabled' : '') + '"><a class="page-link" href="#" data-page="' + (currentPage - 1) + '">Previous</a></li>');

         // Page links
         for (var i = 1; i <= totalPages; i++) {
            paginationContainer.append('<li class="page-item ' + (i === currentPage ? 'active' : '') + '"><a class="page-link" href="#" data-page="' + i + '">' + i + '</a></li>');
         }

         // Next link
         paginationContainer.append('<li class="page-item ' + (currentPage === totalPages ? 'disabled' : '') + '"><a class="page-link" href="#" data-page="' + (currentPage + 1) + '">Next</a></li>');
      }
       $(document).ready(function(){
         getGridData();
         
         $(".list-grid-btn").click((e)=>{
            let view = $(".list-grid-btn.active").data('view');
            $(".list-grid-btn").removeClass('active');
            if(view == 'list'){
               $(".grid-view").removeClass('d-none');
               $(".list-view").addClass('d-none');
               $(".list-grid-btn:eq(1)").addClass('active');
            }else{
               $(".list-view").removeClass('d-none');
               $(".grid-view").addClass('d-none');
               $(".list-grid-btn:eq(0)").addClass('active');
            }
         })

         $("#searchGridBox").on('keypress',function(e) {
            if(e.which == 13) {
               getGridData();
            }
         });
       });
    </script>
@endpush
<x-app-layout :assets="$assets ?? []">
<div>
   <div class="row">
      <div class="col-sm-12">
         <div class="card">
            <div class="card-header d-flex justify-content-between">
               <div class="header-title">
                  <h4 class="card-title">{{ $pageTitle ?? 'List'}}</h4>
               </div>
                <div class="card-action">
                    {!! $headerAction ?? '' !!}
                </div>
            </div>
            <div class="card-body px-0">
               <div id="btnContainer" class="text-end mb-3 me-4">
                  <button class="btn list-grid-btn active" data-view="list"><i class="fa fa-bars"></i> List</button> 
                  <button class="btn list-grid-btn" data-view='grid'><i class="fa fa-th-large"></i> Grid</button>
               </div>
               <div class="grid-view d-none">
                  <div class="loader-overlay">
                     <div class="spinner"></div>
                   </div>
                  <div class="row">
                     <div class="col-md-8"></div>
                     <div class="form-group col-md-4">
                        <input type="text" id="searchGridBox" class="form-control form-control-sm float-end me-4" placeholder="Search" />
                     </div>
                  </div>
                  <div class="grid-responsive ">
                     
                  </div>
               </div>
               <div class="table-responsive  list-view">
                    {{ $dataTable->table(['class' => 'table table-striped w-100'],true) }}
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
</x-app-layout>
