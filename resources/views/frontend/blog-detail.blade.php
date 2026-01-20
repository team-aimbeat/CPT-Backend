@extends('frontend.layouts.master')
@section('title','Blogs')
@section('main-content')

<div class="container-fluid h-100 bg-black p-4 detail-page">
 
    
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
    <div class="row">
      <div class="col-md-8">
        <h1 class="section-title">{{$data->title}}</h1>
        <ul class="blog-info-list">
          <li><i class="far fa-calendar"></i> {{$data->created_at->format('M d, Y')}}
          </li>
    
          <li>
            <i class="fa-solid fa-tag"></i>
            @foreach ($tagsList as $tag)
              
                {{$tag->title}}
            @endforeach
          </li>
        </ul>
        <div class="blog-details-div">
          {!! $data->description !!}
        </div>
      </div>
      <div class="col-md-4">
        <div class="recent-post">
          <h4 class="head-title">Recent Posts</h4>
          @foreach ($recentPostList as $post)
            <a href="{{route('web.blog.detail', $post->id)}}" class="text-decoration-none post-detail">
              <div class="title">{{$post->title}}</div>
              <div class="other">
                <i class="far fa-calendar"></i> {{$post->created_at->format('M d, Y')}}
              </div>
            </a>
          @endforeach
        </div>

        <div class="recent-post">
          <h4 class="head-title">Featured Posts</h4>
          @foreach ($featuredPostList as $post)
            <a href="{{route('web.blog.detail', $post->id)}}" class="text-decoration-none post-detail">
              <div class="title">{{$post->title}}</div>
              <div class="other">
                <i class="far fa-calendar"></i> {{$post->created_at->format('M d, Y')}}
              </div>
            </a>
          @endforeach
        </div>

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
        swapSlider(".box-id-{{$data->id}}");
      });
</script>
@endpush

