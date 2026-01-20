{{ Form::open(['method' => 'POST','route' => ['envSetting'],'data-toggle'=>'validator']) }}
    <div class="col-md-12 mt-20">
        <div class="row">

        {{ Form::hidden('id', null, ['class' => 'form-control'] ) }}
        {{ Form::hidden('page', $page, ['class' => 'form-control'] ) }}
        {{ Form::hidden('type', 'login', ['class' => 'form-control'] ) }}
        {{ Form::hidden('ENV[GOOGLE_CALLBACK_URL]', route('web.login.google.callback'), ['class' => 'form-control'] ) }}
        {{ Form::hidden('ENV[FACEBOOK_CALLBACK_URL]', route('web.login.facebook.callback'), ['class' => 'form-control'] ) }}
            @foreach(config('constant.LOGIN_SETTING') as $key => $value)
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-control-label text-capitalize">{{ strtolower(str_replace('_',' ',$key)) }}</label>
                        @if(auth()->user()->hasRole('admin'))
                            <input type="{{($key=='GOOGLE_CLIENT_SECRET' || $key=='FACEBOOK_CLIENT_SECRET')?'password':'text'}}" value="{{ $value }}" name="ENV[{{$key}}]" class="form-control" placeholder="{{ config('constant.LOGIN_PLACEHOLDER.'.$key) }}">
                        @else
                            <input type="{{($key=='GOOGLE_CLIENT_SECRET' || $key=='FACEBOOK_CLIENT_SECRET')?'password':'text'}}" value="" name="ENV[{{$key}}]" class="form-control" placeholder="{{ config('constant.LOGIN_PLACEHOLDER.'.$key) }}">
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
<hr>
{{ Form::submit(__('message.save'), ['class' => 'btn btn-md btn-primary float-md-end' ]) }}
{{ Form::close() }}