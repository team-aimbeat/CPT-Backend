<x-app-layout :assets="$assets ?? []">
    <div>
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <div class="header-title"><h4 class="card-title">{{ $pageTitle }}</h4></div>
                        <div class="card-action">
                            <a href="{{ route('warmup.video.list') }}" class="btn btn-sm btn-primary" role="button">{{ __('message.back') }}</a>
                        </div>
                    </div>

                    <div class="card-body">
                        {!! Form::open(['route' => 'warmup.video.store', 'method' => 'post', 'files' => true]) !!}
                            <div class="row">
                                <div class="form-group col-md-4">
                                    <label for="language_id" class="form-label">{{ __('message.language') }}</label>
                                    <select class="form-control" id="language_id" name="language_id" required>
                                        <option value="">{{ __('message.language') }}</option>
                                        @foreach($languages as $language)
                                            <option value="{{ $language->id }}">{{ $language->language_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('language_id')<div class="text-danger">{{ $message }}</div>@enderror
                                </div>

                                <div class="form-group col-md-4">
                                    <label class="form-control-label" for="video_url">Warmup Video File</label>
                                    <div>
                                        <input class="form-control file-input" type="file" name="video_file" id="video_url" accept="video/*" required />
                                    </div>
                                    @error('video_file')<div class="text-danger">{{ $message }}</div>@enderror
                                </div>

                                <div class="form-group col-md-4 align-self-end">
                                    {{ Form::submit(__('message.save'), ['class' => 'btn btn-md btn-primary']) }}
                                </div>
                            </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
