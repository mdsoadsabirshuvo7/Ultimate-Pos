<!doctype html>
<html lang="{{ config('app.locale') }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title')</title>

        <!-- Fonts -->
        <!-- <link href="https://fonts.googleapis.com/css?family=Raleway:100,300,600" rel="stylesheet" type="text/css"> -->
        
        <link rel="stylesheet" href="{{ asset('css/vendor.css') }}">

        <!-- Styles -->
        <style>
            body {
                min-height: 100vh;
                background-color: #243949;
                color: #fff;
                background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%239C92AC' fill-opacity='0.12'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            }
            .navbar-default {
                background-color: transparent;
                border: none;
            }
            .navbar-static-top {
                margin-bottom: 19px;
            }
            .navbar-default .navbar-nav>li>a {
                color: #fff;
                font-weight: 600;
                font-size: 15px
            }
            .navbar-default .navbar-nav>li>a:hover{
                color: #ccc;
            }
            .navbar-default .navbar-brand {
                color: #ccc;
            }
        </style>
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
        @include('layouts.partials.home_header')
        <div class="container">
            <div class="content">
                @yield('content')
            </div>
        </div>
        @include('layouts.partials.javascripts')

    <!-- Scripts -->
    <script src="{{ asset('js/login.js?v=' . $asset_v) }}"></script>
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