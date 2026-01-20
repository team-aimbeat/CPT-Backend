@push('scripts')
@endpush
<x-app-layout :assets="$assets ?? []">
    <div>
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <div class="header-title">
                            <h5 class="font-weight-bold">{{ $pageTitle ?? __('message.list') }}</h5>
                        </div>
                        {{-- <div class="text-center ms-3 ms-lg-0 ms-md-0">
                            @if($auth_user->can('permission-add'))
                                <a href="#" class="float-end btn btn-sm btn-primary" data-modal-form="form" data-size="small" data--href="{{ route('permission.add',[ 'type' => 'permission' ]) }}" data-app-title="{{ __('message.add_form_title',['form' => __('message.permission')]) }}" data-placement="top">{{ __('message.add_form_title', ['form' => __('message.permission')] ) }}</a>
                            @endif
                        </div> --}}
                    </div>       
                    <div class="card-body">
                        <div class="accordion" id="permissionAccordion">
                        {{ Form::open(['route' => 'permission.store', 'method' => 'post']) }}
                            @foreach ($permissions as $parentpermission)
                                @if(is_null($parentpermission['parent_id']))
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="heading{{ $parentpermission['id'] }}">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $parentpermission['id'] }}" aria-expanded="true" aria-controls="collapse{{ $parentpermission['id'] }}">
                                            {{ $parentpermission['title'] }}
                                        </button>
                                    </h2>
                                    <div id="collapse{{ $parentpermission['id'] }}" class="accordion-collapse collapse" aria-labelledby="heading{{ $parentpermission['id'] }}" data-bs-parent="#permissionAccordion">
                                        <div class="accordion-body">
                                                <table class="table table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>{{ __('message.permission') }}</th>
                                                            @foreach ($roles as $role)
                                                                <th class="text-center">{{ $role['title'] }}</th>
                                                            @endforeach
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($permissions as $permission)
                                                            @if($permission['parent_id'] === $parentpermission['id'])
                                                            <tr>
                                                                <td>{{ $permission['title'] }}</td>
                                                                @foreach ($roles as $role)
                                                                    <td class="text-center">
                                                                        <input class="form-check-input" type="checkbox" id="role-{{$role['id']}}-permission-{{$permission['id']}}" name="permission[{{$permission['name']}}][]" value="{{ $role['name'] }}"
                                                                        {{ (AuthHelper::checkRolePermission($role,$permission->name)) ? 'checked' : '' }}>
                                                                    </td>
                                                                @endforeach
                                                            </tr>
                                                            @endif
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                                <div class="text-end">
                                                    <input type="submit" name="Save" value="{{ __('message.save') }}" class="btn btn-md btn-primary ">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        {{ Form::close() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
