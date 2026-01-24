<x-app-layout :assets="$assets ?? []">
    <div>
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    {{-- ... (Card Header & Title remains the same) ... --}}
                    <div class="card-header d-flex justify-content-between">
                        <div class="header-title"><h4 class="card-title">{{ $pageTitle }}</h4></div>
                        <div class="card-action">
                            <a href="{{ route('exercise.index') }}" class="btn btn-sm btn-primary" role="button">{{ __('message.back') }}</a>
                        </div>
                    </div>

                    <div class="card-body">
                        {{-- ************************************************* --}}
                        {{--                ADD NEW VIDEO FORM                 --}}
                        {{-- ************************************************* --}}
                        <h5 class="mb-4">Add New Video</h5>
                        
                        {!! Form::open(['route' => 'exercise.store_video', 'method' => 'post', 'files' => true]) !!}
                        
                        {{-- Using the model's ID if Route Model Binding is used --}}
                        <input type="hidden" name="exercise_id" value="{{ $exercise->id }}"> 
                        
                        <div class="row">
                            
                            <div class="form-group col-md-4 video_upload">
                                <label class="form-control-label" for="video_url">Video File</label>
                                <div><input class="form-control file-input" type="file" name="video_file" id="video_url" accept="video/*" required /></div> 
                                @error('video_file')<div class="text-danger">{{ $message }}</div>@enderror
                            </div>
                            
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
                            
                            <div class="form-group col-md-4 align-self-end">
                                {{ Form::submit( __('message.save'), ['class'=>'btn btn-md btn-primary']) }}
                            </div>
                        </div>
                        
                        {!! Form::close() !!} 

                        <hr class="mt-4 mb-4">

                        {{-- ************************************************* --}}
                        {{--              EXISTING VIDEOS TABLE                --}}
                        {{-- ************************************************* --}}
                        <h5 class="mb-4">Existing Videos ({{ $exerciseVideos->count() }})</h5>

                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('message.language') }}</th>
                                        <th>Video</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($exerciseVideos as $index => $video)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            {{-- Assuming the languageList relationship is defined and working --}}
                                            <td>{{ optional($video->languageList)->language_name ?? 'N/A' }}</td>
                                            @php
                                                $videoPath = $video->hls_master_url ?: $video->video_url;
                                                $videoHref = $videoPath ? cloudfrontUrl($videoPath) : '';
                                            @endphp
                                            <td>
                                                @if($videoHref)
                                                    <a href="{{ $videoHref }}" target="_blank" rel="noopener noreferrer">{{ Str::limit($videoHref, 50) }}</a>
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                            <td>{{ $video->transcoding_status ?? 'pending' }}</td>
                                            {{-- This is the table cell action that triggers deletion --}}
                                        <td>
                                            <a href="#" class="btn btn-sm btn-danger" 
                                               onclick="event.preventDefault(); 
                                                        if (confirm('Are you sure you want to delete this video?')) {
                                                            document.getElementById('delete-form-{{ $video->id }}').submit();
                                                        }">
                                                Delete
                                            </a>
                                            
                                            {{-- This is the hidden form required to send a DELETE request --}}
                                            <form id="delete-form-{{ $video->id }}" 
                                                  action="{{ route('exercise.video.destroy', $video->id) }}" 
                                                  method="POST" 
                                                  style="display: none;">
                                                @csrf
                                                @method('DELETE') {{-- This line is crucial for Laravel to recognize the request as DELETE --}}
                                            </form>
                                        </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center">No videos have been added for this exercise yet.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
