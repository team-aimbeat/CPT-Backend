

<div class="images-box box-id-{{$data->id}}">
    
        <div style="--swiper-navigation-color: #fff; --swiper-pagination-color: #fff" class="swiper hpSwiper2 hpSwiper-img">
            <div class="swiper-wrapper">
                @if( getMediaFileExit($data, 'workout_image'))
                    <div class="swiper-slide">
                        <img src="{{getSingleMedia($data,'workout_image')}}" />
                    </div>
                @endif
                @if( getMediaFileExit($data, 'workout_video'))
                <div class="swiper-slide">
                    <video src="{{getSingleMedia($data, 'workout_video')}}" muted></video>
                </div>
                @endif
            </div>
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
        </div>
        <div thumbsSlider="" class="swiper hpSwiper hpSwiper-small">
            <div class="swiper-wrapper">
                @if( getMediaFileExit($data, 'workout_image'))
                    <div class="swiper-slide">
                        <img src="{{getSingleMedia($data,'workout_image')}}" />
                    </div>
                @endif
                @if( getMediaFileExit($data, 'workout_video'))
                <div class="swiper-slide">
                    <video src="{{getSingleMedia($data, 'workout_video')}}" muted></video>
                </div>
                @endif
            </div>
        </div>
    
</div>