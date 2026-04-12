@extends('cms::frontend.layouts.app')
@section('title', 'Terms and Conditions')
@php
    $navbar_btn['text'] = 'Try For Free';
    $navbar_btn['link'] = route('business.getRegister');

    if (isset($__site_details['btns']) && isset($__site_details['btns']['navbar']) && !empty($__site_details['btns']['navbar']['text'])) {
        $navbar_btn['text'] = $__site_details['btns']['navbar']['text'] ?? 'Try For Free';
    }

    if (isset($__site_details['btns']) && isset($__site_details['btns']['navbar']) && !empty($__site_details['btns']['navbar']['link'])) {
        $navbar_btn['link'] = $__site_details['btns']['navbar']['link'] ?? route('business.getRegister');
    }
@endphp
@includeIf('cms::frontend.layouts.header')

@section('meta')
<meta name="description" content="Terms and Conditions for Sector71 Cloud POS services.">
@endsection

@section('css')
<style>
    .s71-terms-wrap {
        background: #f4f8ff;
        padding: 48px 0;
    }

    .s71-terms-card {
        background: #ffffff;
        border: 1px solid #dbe6f7;
        border-radius: 16px;
        box-shadow: 0 10px 28px rgba(7, 33, 72, 0.08);
        padding: 28px;
    }

    .s71-terms-card h1,
    .s71-terms-card h2 {
        color: #0d2f5f;
    }

    .s71-terms-card h2 {
        font-size: 1.2rem;
        margin-top: 1.6rem;
        margin-bottom: 0.7rem;
    }

    .s71-terms-card p,
    .s71-terms-card li {
        color: #24364f;
        line-height: 1.75;
    }

    .s71-terms-card a {
        color: #0f63d8;
        text-decoration: none;
    }

    .s71-terms-card a:hover {
        text-decoration: underline;
    }
</style>
@endsection

@section('content')
@php
    $terms_html = !empty($system_settings['superadmin_register_tc']) ? $system_settings['superadmin_register_tc'] : '';
@endphp
<div class="s71-terms-wrap">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-xl-10">
                <div class="s71-terms-card">
                    @if(!empty($terms_html))
                        {!! $terms_html !!}
                    @else
                        <p class="mb-0">Terms and conditions content is not configured yet in Super Admin Settings.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
