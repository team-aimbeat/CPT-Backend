<x-app-layout :assets="$assets ?? []">
    <div>
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <div class="header-title"><h4 class="card-title">{{ $pageTitle }}</h4></div>
                        <div class="card-action">
                            <a href="{{ route('coupons.create') }}" class="btn btn-sm btn-primary" role="button">Add Coupon</a>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Code</th>
                                        <th>Status</th>
                                        <th>Description</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($coupons as $index => $coupon)
                                        <tr>
                                            <td>{{ $coupons->firstItem() + $index }}</td>
                                            <td>{{ $coupon->code }}</td>
                                            <td>{{ $coupon->status }}</td>
                                            <td>{{ \Illuminate\Support\Str::limit($coupon->description, 80) }}</td>
                                            <td>
                                                <a href="{{ route('coupons.edit', $coupon->id) }}" class="btn btn-sm btn-primary">Edit</a>
                                                <a href="#" class="btn btn-sm btn-danger"
                                                   onclick="event.preventDefault();
                                                   if (confirm('Are you sure you want to delete this coupon?')) {
                                                       document.getElementById('delete-form-{{ $coupon->id }}').submit();
                                                   }">
                                                    Delete
                                                </a>
                                                <form id="delete-form-{{ $coupon->id }}"
                                                      action="{{ route('coupons.destroy', $coupon->id) }}"
                                                      method="POST"
                                                      style="display: none;">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center">No coupons found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $coupons->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
