@extends('frontend.layouts.master')
@section('title','Blogs')
@section('main-content')

<div class="container-fluid h-100 bg-black p-4">
 
      <div class="blog-grid-div">
        <h1 class="section-title">Blogs</h1>
        <div class="grid-responsive ">
          @php
            $total = $postList->total();
            $currentPage = $postList->currentPage();
            $perPage = $postList->perPage();
    
            $from = ($currentPage - 1) * $perPage + 1;
            $to = min($currentPage * $perPage, $total);
          @endphp
          <p class="showing-results">Showing {{$from}}–{{$to}} of {{$total}} results</p>
          <div class="row">
            @if(count($postList))
              @foreach ($postList as $data)
                <div class="col-md-6 col-lg-3">
                  <div class="card">
                      <div class="card-header">
                        <div class="images-box box-id-{{$data->id}}" data-id="{{$data->id}}">
                            
                          <div style="--swiper-navigation-color: #fff; --swiper-pagination-color: #fff" class="swiper hpSwiper2 hpSwiper-img">
                              <div class="swiper-wrapper">
                                  @if( getMediaFileExit($data, 'post_image'))
                                      <div class="swiper-slide">
                                          <img src="{{getSingleMedia($data,'post_image')}}" />
                                      </div>
                                  @endif
                                  @if( $data->video_type=='upload_video' && getMediaFileExit($data, 'post_video'))
                                  <div class="swiper-slide">
                                      <video src="{{getSingleMedia($data, 'post_video')}}" muted></video>
                                  </div>
                                  @endif
                                  @if( $data->video_type=='url' && $data->video_url)
                                  <div class="swiper-slide">
                                      <video src="{{$data->video_url}}" muted></video>
                                  </div>
                                  @endif
                              </div>
                              <div class="swiper-button-next"></div>
                              <div class="swiper-button-prev"></div>
                          </div>
                          <div thumbsSlider="" class="swiper hpSwiper hpSwiper-small">
                              <div class="swiper-wrapper">
                                  @if( getMediaFileExit($data, 'post_image'))
                                      <div class="swiper-slide">
                                          <img src="{{getSingleMedia($data,'post_image')}}" />
                                      </div>
                                  @endif
                                  @if( $data->video_type=='upload_video' && getMediaFileExit($data, 'post_video'))
                                  <div class="swiper-slide">
                                      <video src="{{getSingleMedia($data, 'post_video')}}" muted></video>
                                  </div>
                                  @endif
                                  @if( $data->video_type=='url' && $data->video_url)
                                  <div class="swiper-slide">
                                      <video src="{{$data->video_url}}" muted></video>
                                  </div>
                                  @endif
                              </div>
                          </div>
          
                        </div>
                      </div>
                      <div class="card-body">
                        <a href="{{route('web.blog.detail', $data->id)}}" class="text-decoration-none">
                          <div class="details">{{$data->title}}</div>
                          <div class="date">{{$data->created_at->format('M d, Y')}}</div>
                        </a>
                      </div>
                  </div>
                </div>
              @endforeach
            @else
              <h4 class="text-danger">Sorry, No data available.</h4>
            @endif
          </div>
          {{$postList->links('frontend.item-lists-peginate')}}
        </div>
      </div>
 
</div>


@endsection


@push("styles")
<link rel="stylesheet" type="text/css" href="{{asset('package/swiper/swiper-bundle.min.css')}}" media="screen" />
 
@endpush


@push("scripts")
<script src="{{asset('package/swiper/swiper-bundle.min.js')}}"></script>
<script>
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

      $( document ).ready(function() {
        $( ".grid-responsive .images-box" ).each(function( index ) {
          swapSlider(".box-id-"+$( this ).data('id'));
        });
      });
</script>
@endpush

