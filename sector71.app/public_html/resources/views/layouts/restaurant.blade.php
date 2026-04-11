@inject('request', 'Illuminate\Http\Request')

@php
    $pos_layout = true;
@endphp

<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{in_array(session()->get('user.language', config('app.locale')), config('constants.langs_rtl')) ? 'rtl' : 'ltr'}}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <!-- Tell the browser to be responsive to screen width -->
        <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title') - {{ Session::get('business.name') }}</title> 

        @include('layouts.partials.css')

        @yield('css')
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

    <body class="hold-transition lockscreen">
        <div class="wrapper">
            <script type="text/javascript">
                if(localStorage.getItem("upos_sidebar_collapse") == 'true'){
                    var body = document.getElementsByTagName("body")[0];
                    body.className += " sidebar-collapse";
                }
            </script>
        
            <!-- Content Wrapper. Contains page content -->
            <div class="container-fluid">
             @include('layouts.partials.header-restaurant')

                <!-- Add currency related field-->
                <input type="hidden" id="__code" value="{{session('currency')['code']}}">
                <input type="hidden" id="__symbol" value="{{session('currency')['symbol']}}">
                <input type="hidden" id="__thousand" value="{{session('currency')['thousand_separator']}}">
                <input type="hidden" id="__decimal" value="{{session('currency')['decimal_separator']}}">
                <input type="hidden" id="__symbol_placement" value="{{session('business.currency_symbol_placement')}}">

                <input type="hidden" id="__orders_refresh_interval" value="{{config('constants.orders_refresh_interval', 600)}}">
                <!-- End of currency related field-->

                @if (session('status'))
                    <input type="hidden" id="status_span" data-status="{{ session('status.success') }}" data-msg="{{ session('status.msg') }}">
                @endif
                @yield('content')
                @if(config('constants.iraqi_selling_price_adjustment'))
                    <input type="hidden" id="iraqi_selling_price_adjustment">
                @endif

                <!-- This will be printed -->
                <section class="invoice print_section" id="receipt_section">
                </section>
                
            </div>
            @include('home.todays_profit_modal')
            <!-- /.content-wrapper -->

            @include('layouts.partials.footer-restaurant')

        </div>

        @include('layouts.partials.javascripts')
        <script src="{{ asset('js/restaurant.js?v=' . $asset_v) }}"></script>
        <div class="modal fade view_modal" tabindex="-1" role="dialog" 
        aria-labelledby="gridSystemModalLabel"></div>
    
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