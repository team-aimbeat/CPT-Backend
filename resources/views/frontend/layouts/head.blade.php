<!-- Meta Tag -->
@yield('meta')
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>@yield("title")</title>
<meta name="csrf-token" id="csrf-token" content="{{ csrf_token() }}">

<link rel="icon" type="image/x-icon" href="{{ getSingleMedia(appSettingData('get'), 'site_favicon', null) }}">

<link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Lato&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lobibox@1.2.7/dist/css/lobibox.min.css" integrity="sha256-G6lAoPYyo1Z6p0k+ZvAW+EX1jz+v9CvqeUDfLp//Xv0=" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>

<link rel="stylesheet" href="{{asset("asset/css/style.css")}}" />
<link rel="stylesheet" href="{{asset("asset/css/modal.css")}}" />
@stack('styles')
