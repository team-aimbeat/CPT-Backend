@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show text-center mb-0" role="alert">
        {{session('success')}}
        <button type="button" class="btn-close pt-0" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif


@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show text-center mb-0"  role="alert">
        {{session('error')}}
        <button type="button" class="btn-close pt-0" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

