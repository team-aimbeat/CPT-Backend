<x-app-layout :assets="$assets ?? []">
    <div>
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <div class="header-title"><h4 class="card-title">{{ $pageTitle }}</h4></div>
                        <div class="card-action">
                            <a href="{{ route('companies.create') }}" class="btn btn-sm btn-primary" role="button">Add Company</a>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Code</th>
                                        <th>Free Days</th>
                                        <th>Max Employees</th>
                                        <th>Domains</th>
                                        <th>Employee Emails</th>
                                        <th>Used Access</th>
                                        <th>Validity</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($companies as $index => $company)
                                        <tr>
                                            <td>{{ $companies->firstItem() + $index }}</td>
                                            <td>{{ $company->name }}</td>
                                            <td>{{ $company->code }}</td>
                                            <td>{{ $company->free_access_days }}</td>
                                            <td>{{ $company->max_employees ?? '-' }}</td>
                                            <td>{{ $company->domains_count ?? 0 }}</td>
                                            <td>{{ $company->employees_count ?? 0 }}</td>
                                            <td>{{ $company->employee_accesses_count ?? 0 }}</td>
                                            <td>
                                                {{ $company->valid_from ? $company->valid_from->format('Y-m-d') : '-' }}
                                                to
                                                {{ $company->valid_to ? $company->valid_to->format('Y-m-d') : '-' }}
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $company->status === 'active' ? 'primary' : 'warning' }}">
                                                    {{ ucfirst($company->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <a href="{{ route('companies.edit', $company->id) }}" class="btn btn-sm btn-primary">Edit</a>
                                                <a href="#" class="btn btn-sm btn-danger"
                                                   onclick="event.preventDefault();
                                                   if (confirm('Are you sure you want to delete this company?')) {
                                                       document.getElementById('delete-company-{{ $company->id }}').submit();
                                                   }">
                                                    Delete
                                                </a>
                                                <form id="delete-company-{{ $company->id }}"
                                                      action="{{ route('companies.destroy', $company->id) }}"
                                                      method="POST"
                                                      style="display: none;">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="11" class="text-center">No companies found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $companies->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
