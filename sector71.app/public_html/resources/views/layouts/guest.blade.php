<!DOCTYPE html>
<html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title')</title> 

    <link rel="stylesheet" href="{{ asset('css/vendor.css?v='.$asset_v) }}">

    <!-- app css -->
    <link rel="stylesheet" href="{{ asset('css/app.css?v='.$asset_v) }}">

    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
@if(!empty($__system_settings['facebook_pixel']))
    {!! $__system_settings['facebook_pixel'] !!}
@endif

@if(!empty($__system_settings['google_analytics_id']))
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $__system_settings['google_analytics_id'] }}"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', '{{ $__system_settings['google_analytics_id'] }}');
    </script>
@endif
</head>

<body>
    <div id="app"></div>
    @if (session('status'))
        <input type="hidden" id="status_span" data-status="{{ session('status.success') }}" data-msg="{{ session('status.msg') }}">
    @endif
    @yield('content')

    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js?v=$asset_v"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js?v=$asset_v"></script>
    <![endif]-->

    <!-- jQuery 2.2.3 -->
    <script src="{{ asset('js/vendor.js?v=' . $asset_v) }}"></script>
    <script src="{{ asset('js/functions.js?v=' . $asset_v) }}"></script>
    @yield('javascript')

@if(!empty($__system_settings['whatsapp_number']))
    <!-- WhatsApp Floating Widget -->
    <style>
        .wa-float {
            position: fixed;
            width: 60px;
            height: 60px;
            bottom: 40px;
            left: 40px; 
            background-color: #25d366;
            color: #FFF !important;
            border-radius: 50px;
            text-align: center;
            font-size: 30px;
            box-shadow: 2px 2px 3px #999;
            z-index: 999999;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        .wa-float:hover {
            background-color: #128C7E;
            color: #FFF !important;
            transform: scale(1.1);
        }
        .wa-float i {
            margin-top: 2px;
        }
        /* Hide default chat widgets that might conflict */
        .cp-whatsapp-wrapper { z-index: 99999 !important; }
    </style>
    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $__system_settings['whatsapp_number']) }}?text={{ urlencode($__system_settings['whatsapp_greeting'] ?? 'Hello!') }}" class="wa-float" target="_blank" title="Chat with us on WhatsApp">
        <i class="fab fa-whatsapp"></i>
    </a>
@endif
</body>

</html>