<x-app-layout :assets="$assets ?? []">
    <div>
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <div class="header-title"><h4 class="card-title">{{ $pageTitle }}</h4></div>
                        <div class="card-action">
                            <a href="{{ route('exercise.video.create') }}" class="btn btn-sm btn-primary" role="button">Add Video</a>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Equipment</th>
                                        <th>{{ __('message.language') }}</th>
                                        <th>Video URL</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($equipmentVideos as $index => $video)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ optional($video->equipment)->title ?? 'N/A' }}</td>
                                            <td>{{ optional($video->languageList)->language_name ?? 'N/A' }}</td>
                                            @php
                                                $videoPath = $video->video_url;
                                                $videoHref = $videoPath ? cloudfrontUrl($videoPath) : '';
                                            @endphp
                                            <td>
                                                @if($videoHref)
                                                    <a href="{{ $videoHref }}" target="_blank" rel="noopener noreferrer">
                                                        {{ Str::limit($videoHref, 50) }}
                                                    </a>
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                            <td>
                                                <a href="#" class="btn btn-sm btn-danger"
                                                   onclick="event.preventDefault();
                                                   if (confirm('Are you sure you want to delete this video?')) {
                                                       document.getElementById('delete-form-{{ $video->id }}').submit();
                                                   }">
                                                    Delete
                                                </a>
                                                <form id="delete-form-{{ $video->id }}"
                                                      action="{{ route('exercise.equipment_video.destroy', $video->id) }}"
                                                      method="POST"
                                                      style="display: none;">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center">No videos found.</td>
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
