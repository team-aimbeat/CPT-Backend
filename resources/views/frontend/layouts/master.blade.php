<!DOCTYPE html>
<html lang="en">
<head>
	@include('frontend.layouts.head')	
</head>
<body>
<!-- Header -->
@include('frontend.layouts.header')
<!--/ End Header -->

<div class="body-main-div">

    {{-- @include('frontend.layouts.notification') --}}
    @yield('main-content')
</div>
@include('frontend.layouts.footer')


</body>
</html>