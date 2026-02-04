<x-app-layout :assets="$assets ?? []">
    <div>
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <div class="header-title"><h4 class="card-title">{{ $pageTitle }}</h4></div>
                        <div class="card-action">
                            <div class="btn-group me-2" role="group">
                                <a href="{{ route('coupons.create', [
                                    'type' => 'free_access',
                                    'access_days' => 365,
                                    'max_redemptions' => 2,
                                    'per_user_limit' => 1,
                                    'description' => '2 person yearly: 1 other member gets 6 months free'
                                ]) }}" class="btn btn-sm btn-outline-secondary">Quick: 2P Yearly</a>
                                <a href="{{ route('coupons.create', [
                                    'type' => 'free_access',
                                    'access_days' => 365,
                                    'max_redemptions' => 4,
                                    'per_user_limit' => 1,
                                    'description' => '4 person yearly: 1 other member gets 1 year free'
                                ]) }}" class="btn btn-sm btn-outline-secondary">Quick: 4P Yearly</a>
                                <a href="{{ route('coupons.create', [
                                    'type' => 'free_access',
                                    'access_days' => 365,
                                    'max_redemptions' => 2,
                                    'per_user_limit' => 1,
                                    'description' => '2 person 24 month: 1 other member gets 1 year free'
                                ]) }}" class="btn btn-sm btn-outline-secondary">Quick: 2P 24M</a>
                                <a href="{{ route('coupons.create', [
                                    'type' => 'free_access',
                                    'access_days' => 730,
                                    'max_redemptions' => 4,
                                    'per_user_limit' => 1,
                                    'description' => '4 person 24 month: 1 other member gets 2 years free'
                                ]) }}" class="btn btn-sm btn-outline-secondary">Quick: 4P 24M</a>
                                <a href="{{ route('coupons.create', [
                                    'type' => 'discount',
                                    'value' => 200,
                                    'first_purchase_only' => 1,
                                    'per_user_limit' => 1,
                                    'description' => 'Referral discount (first purchase only)'
                                ]) }}" class="btn btn-sm btn-outline-secondary">Quick: Referral ₹200</a>
                            </div>
                            <a href="{{ route('coupons.create') }}" class="btn btn-sm btn-primary" role="button">Add Coupon</a>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="alert alert-warning">
                            Offers mapping:
                            Free Access / Free Months coupons grant full app access without subscription.
                            Use Max Redemptions for 2-person (2) or 4-person (4) offers.
                            Referral discount should be Discount + First Purchase Only.
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Code</th>
                                        <th>Type</th>
                                        <th>Value</th>
                                        <th>Access Days</th>
                                        <th>Max Redemptions</th>
                                        <th>Per User Limit</th>
                                        <th>Validity</th>
                                        <th>First Purchase</th>
                                        <th>Used</th>
                                        <th>Auto</th>
                                        <th>Source Sub</th>
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
                                            <td>
                                                @if($coupon->type === 'free_access')
                                                    <span class="badge bg-success">Free Access</span>
                                                @elseif($coupon->type === 'discount')
                                                    <span class="badge bg-primary">Discount</span>
                                                @elseif($coupon->type === 'free_months')
                                                    <span class="badge bg-warning text-dark">Free Months</span>
                                                @elseif($coupon->type === 'same_access')
                                                    <span class="badge bg-info text-dark">Same Access</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ $coupon->type }}</span>
                                                @endif
                                            </td>
                                            <td>{{ $coupon->value ?? '-' }}</td>
                                            <td>{{ $coupon->access_days ?? '-' }}</td>
                                            <td>{{ $coupon->max_redemptions ?? '-' }}</td>
                                            <td>{{ $coupon->per_user_limit ?? '-' }}</td>
                                            <td>
                                                {{ $coupon->valid_from ? $coupon->valid_from->format('Y-m-d') : '-' }}
                                                to
                                                {{ $coupon->valid_to ? $coupon->valid_to->format('Y-m-d') : '-' }}
                                            </td>
                                            <td>{{ $coupon->first_purchase_only ? 'Yes' : 'No' }}</td>
                                            <td>{{ $coupon->redemptions_count ?? 0 }}</td>
                                            <td>{{ $coupon->is_auto_generated ? 'Yes' : 'No' }}</td>
                                            <td>{{ $coupon->source_subscription_id ?? '-' }}</td>
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
                                            <td colspan="15" class="text-center">No coupons found.</td>
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
