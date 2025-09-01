@inject('request', 'Illuminate\Http\Request')

@if (
    $request->segment(1) == 'pos' &&
        ($request->segment(2) == 'create' || $request->segment(3) == 'edit' || $request->segment(2) == 'payment'))
    @php
        $pos_layout = true;
    @endphp
@else
    @php
        $pos_layout = false;
    @endphp
@endif

@php
    $whitelist = ['127.0.0.1', '::1'];
@endphp

@php
    $hoverBgColors = [
        'blue'    => '#2563eb', // blue-700
        'orange'  => 'rgb(185 56 21)', // orange-700
        'red'     => 'rgb(180 35 24)', // red-700
        'green'   => 'rgb(6 118 71)', // green-700
        'yellow'  => 'rgb(181 71 8)', // yellow-700
        'purple'  => 'rgb(89 37 220)', // purple-700
        'gray'    => 'rgb(105 117 134)', // gray-700
        'sky'     => 'rgb(2 106 162)', // sky-700
        'primary' => 'rgb(0 78 235)', // fallback if session is 'primary'
    ];
    $bgColors = [
        'blue'    => '#2563eb', // blue-700
        'orange'  => 'rgb(147 47 25)', // orange-700
        'red'     => 'rgb(145 32 24)', // red-700
        'green'   => 'rgb(8 93 58)', // green-700
        'yellow'  => 'rgb(147 55 13)', // yellow-700
        'purple'  => 'rgb(74 31 184)', // purple-700
        'gray'    => 'rgb(105 117 134)', // gray-700
        'sky'     => 'rgb(6 89 134)', // sky-700
        'primary' => 'rgb(0 64 193)', // fallback if session is 'primary'
    ];
    $focusColors = [
        'blue'    => '#1e40af', // blue-900
        'orange'  => '#7c2d12', // orange-900
        'red'     => '#7f1d1d', // red-900
        'green'   => '#14532d', // green-900
        'yellow'  => '#78350f', // yellow-900
        'purple'  => '#4c1d95', // purple-900
        'gray'    => '#1f2937', // gray-900
        'sky'     => '#0c4a6e', // sky-900
        'primary' => '#073763', // custom darker primary
    ];
    
    $rawTheme = session('business.theme_color') ?? 'primary';
    $hoverBgColor = $hoverBgColors[$rawTheme];
    $bgColor = $bgColors[$rawTheme];
    $focusColor = $focusColors[$rawTheme];

@endphp

<!DOCTYPE html>
<html class="tw-bg-white tw-scroll-smooth" lang="{{ app()->getLocale() }}"
    dir="{{ in_array(session()->get('user.language', config('app.locale')), config('constants.langs_rtl')) ? 'rtl' : 'ltr' }}">
<head>
    <!-- Tell the browser to be responsive to screen width -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no"
        name="viewport">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title') - {{ Session::get('business.name') }}</title>
    
    @include('layouts.partials.css')
    

    @include('layouts.partials.extracss')
    @stack('styles')

    @yield('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css"/>
    <link rel="manifest" href="/manifest.json">
<meta name="theme-color" content="#000000">

    
    <!-- ionicon library -->
    <!-- <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script> -->
</head>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0"></script>
<!-- <body
    class="tw-font-sans tw-antialiased tw-text-gray-900 tw-bg-gray-100 @if ($pos_layout) hold-transition lockscreen @else hold-transition skin-@if (!empty(session('business.theme_color'))){{ session('business.theme_color') }}@else{{ 'blue-light' }} @endif sidebar-mini @endif" > -->
<body
    class="tw-font-sans tw-antialiased tw-text-gray-900 tw-bg-gray-100 tw-h-screen @if ($pos_layout) hold-transition lockscreen @else hold-transition skin-@if (!empty(session('business.theme_color'))){{ session('business.theme_color') }}@else{{ 'blue-light' }} @endif sidebar-mini @endif"
>
    <div class="tw-flex">
        <script type="text/javascript">
            if (localStorage.getItem("upos_sidebar_collapse") == 'true') {
                var body = document.getElementsByTagName("body")[0];
                body.className += " sidebar-collapse";
            }
        </script>
        @if (!$pos_layout)
            @include('layouts.partials.sidebar')
        @endif

        @if (in_array($_SERVER['REMOTE_ADDR'], $whitelist))
            <input type="hidden" id="__is_localhost" value="true">
        @endif

        <!-- Add currency related field-->
        <input type="hidden" id="__code" value="{{ session('currency')['code'] }}">
        <input type="hidden" id="__symbol" value="{{ session('currency')['symbol'] }}">
        <input type="hidden" id="__thousand" value="{{ session('currency')['thousand_separator'] }}">
        <input type="hidden" id="__decimal" value="{{ session('currency')['decimal_separator'] }}">
        <input type="hidden" id="__symbol_placement" value="{{ session('business.currency_symbol_placement') }}">
        <input type="hidden" id="__precision" value="{{ session('business.currency_precision', 2) }}">
        <input type="hidden" id="__quantity_precision" value="{{ session('business.quantity_precision', 2) }}">
        <!-- End of currency related field-->
        @can('view_export_buttons')
            <input type="hidden" id="view_export_buttons">
        @endcan
        @if (isMobile())
            <input type="hidden" id="__is_mobile">
        @endif
        @if (session('status'))
            <input type="hidden" id="status_span" data-status="{{ session('status.success') }}"
                data-msg="{{ session('status.msg') }}">
        @endif
        <main 
            class="tw-flex tw-flex-col tw-flex-1 tw-h-full tw-min-w-0 tw-bg-gray-100"
            style="height:100vh;overflow-y-auto;"
        >

            @if (!$pos_layout)
                @include('layouts.partials.header')
            @else
                @include('layouts.partials.header-pos')
            @endif
            <!-- empty div for vuejs -->
            <div id="app">
                @yield('vue')
            </div>
            <div class="tw-flex-1 tw-overflow-y-auto tw-h-screen" id="scrollable-container">
                @yield('content')
                @if (!$pos_layout)
                
                    @include('layouts.partials.footer')
                @else
                    @include('layouts.partials.footer_pos')
                @endif
            </div>
            <div class='scrolltop no-print'>
                <div class='scroll icon'><i class="fas fa-angle-up"></i></div>
            </div>

            @if (config('constants.iraqi_selling_price_adjustment'))
                <input type="hidden" id="iraqi_selling_price_adjustment">
            @endif

            <!-- This will be printed -->
            <section class="invoice print_section" id="receipt_section">
            </section>
        </main>

        @include('home.todays_profit_modal')
        <!-- /.content-wrapper -->



        <audio id="success-audio">
            <source src="{{ asset('/audio/success.ogg?v=' . $asset_v) }}" type="audio/ogg">
            <source src="{{ asset('/audio/success.mp3?v=' . $asset_v) }}" type="audio/mpeg">
        </audio>
        <audio id="error-audio">
            <source src="{{ asset('/audio/error.ogg?v=' . $asset_v) }}" type="audio/ogg">
            <source src="{{ asset('/audio/error.mp3?v=' . $asset_v) }}" type="audio/mpeg">
        </audio>
        <audio id="warning-audio">
            <source src="{{ asset('/audio/warning.ogg?v=' . $asset_v) }}" type="audio/ogg">
            <source src="{{ asset('/audio/warning.mp3?v=' . $asset_v) }}" type="audio/mpeg">
        </audio>

        @if (!empty($__additional_html))
            {!! $__additional_html !!}
        @endif

        @include('layouts.partials.javascripts')

        <!-- SweetAlert2 -->
 
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <!-- Toastr -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

        <div class="modal fade view_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>

        @if (!empty($__additional_views) && is_array($__additional_views))
            @foreach ($__additional_views as $additional_view)
                @includeIf($additional_view)
            @endforeach
        @endif
        <div>

            <div class="overlay tw-hidden"></div>
            <style>

                body {
                    font-family: Arial, sans-serif;
                    margin: 0;
                    padding: 0;
                    -ms-overflow-style:none;
                }
                ::-webkit-scrollbar {
                    display: none;
                }

                ::-webkit-scrollbar-button {
                    display: none;
                }
                small{
                    font-weight: 500 !important
                }
                .whatsapp-button {
                    position: fixed;
                    bottom: 20px;
                    right: 20px;
                    background-color: #25D366;
                    color: white;
                    border: none;
                    border-radius: 50%;
                    width: 50px;
                    height: 50px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
                    cursor: pointer;
                    transition: background-color 0.3s;
                }
                .whatsapp-button:hover {
                    background-color: #128C7E;
                }
                .whatsapp-icon {
                    width: 30px;
                    height: 30px;
                }
                /* select custom style */
                .select2-container {
                    width: 100% !important;
                }
                /* .select2-container--default .select2-selection--single {
                    border-radius: 0.5rem !important;
                    height: 2.2rem !important;
                    padding: 4px 12px !important;
                } */
                .select2-container .select2-selection--single .select2-selection__rendered{
                    margin-top: -2px !important;
                }
                .select2-container--default .select2-selection--single:hover{
                    background-color : oklch(97% 0.014 254.604);
                }
                .select2-dropdown {
                    border-radius: 0.5rem !important;
                    overflow: hidden;
                }
                .select2-dropdown--below{
                    margin-top: 2px !important;
                }
                .select2-dropdown--above{
                    margin-bottom: 2px !important;
                }
                .select2-results{
                    font-size : 0.75rem !important;
                }
                /* dt custom style */
                .dataTables_scrollFootInner{
                    width : 100% !important;
                }
                .dataTables_scrollHeadInner{
                    width : 100% !important;
                }

                /* inputs select custom style and the filter input */
                .dataTables_length > label > select,
                .dataTables_filter > label > input {
                    border-radius: 0.5rem !important;
                    overflow: hidden;
                    border: 1px solid #ddd !important;
                    /* padding : 0px 1rem !important; */
                }
                
                .dataTables_scroll{
                    /* border-radius: 0.5rem;
                    overflow: hidden; */
                    border : 1px solid #e3e8ef !important;
                }
                /* data table custom style */
                .table {
                    width : 100% !important
                }
                .table > thead {
                    font-size: 0.9rem !important;
                }
                .table > tbody,
                .table > tfoot > tr {
                    font-size : 0.8rem !important;
                }
                .dataTables_wrapper {
                    padding : 0px 4px !important;
                }
                
                /* dw btn dropdown list style */
                ul.dt-button-collection.dropdown-menu{
                    margin-top: 0.5rem !important;
                    background-color: #fff !important;
                    padding: 0.25rem 0.5rem !important;
                    border-radius: 0.5rem !important;
                    font-size : 0.7rem !important;
                }
                ul.dt-button-collection{
                    background-color: #fff !important;
                    padding : 0.25rem 0.5rem !important;
                }
                ul.dt-button-collection.dropdown-menu > li {
                    border-radius : 0.5rem !important;
                    background-color : white !important;
                    margin : 0.25rem 0px !important;
                }
                ul.dt-button-collection.dropdown-menu > li:hover{
                    background-color: #f9fafb !important;
                }
                .dropdown-menu>.active>a, 
                .dropdown-menu > a{
                    border-radius: 0.35rem !important;
                }
                .dropdown-menu>li>a{
                    padding : 6px 3px !important;
                }
                .dt-section-toolbar{
                    display : flex;
                    align-items: center;
                    justify-content: space-between;
                    margin-bottom: 1rem;
                    padding : 0px 4px !important;
                }
                div.dt-buttons {
                    margin-bottom: 0px !important;
                    display: flex;
                    justify-content: flex-start;
                    column-gap: 0.5rem;
                }

                .dt-buttons > .custom-dw-btn
                ,.select2-container--default .select2-selection--single 
                ,.form-control > select
                {
                    border-radius: .5rem;
                    background-color : white !important;
                    color: #666666;
                    line-height: 1.25rem;
                    display: flex;
                    align-items: center;
                    padding: .75rem 1rem;
                    text-align: center;
                    cursor: pointer;
                    user-select: none;
                    -webkit-user-select: none;
                    touch-action: manipulation;
                }
                .dataTables_length > label > select {
                    height : 2rem !important;
                    padding: 0px 0.25rem !important;
                    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
                }
                .dataTables_length > label {
                    padding-top: 4px  !important;
                    font-weight: 500 !important;
                    font-size: 0.8rem !important;
                }
                .dataTables_filter > label > input {
                    height: 2rem !important;
                    padding: 0px 1rem !important;
                    background : #f4f4f4 !important;
                    color : #333 !important;
                    width : 16rem !important;
                }
                .table.dataTable{
                    margin-top : 0px !important;
                    margin-bottom : 0px !important;
                }
                /* table header icons */
                table.dataTable thead .sorting:after
                ,table.dataTable thead .sorting_desc:after
                ,table.dataTable thead .sorting_asc:after
                ,table.dataTable thead > tr > th > i
                {
                    opacity: 0.8 !important;
                    color: white !important;
                    font-size: smaller !important;
                    font-weight: 100 !important;
                }
                .dt-buttons > .custom-dw-btn:hover {
                background-color: rgb(249,250,251);
                }

                .dt-buttons > .custom-dw-btn:focus {
                outline: 2px solid transparent;
                outline-offset: 2px;
                }

                .dt-buttons > .custom-dw-btn:focus-visible {
                box-shadow: none;
                }

                .paginate_button > a{
                    background-color: #FFFFFF;
                    border: 0;
                    border-radius: .5rem;
                    box-sizing: border-box;
                    font-size: .7rem;
                    font-weight: 600;
                    display : flex ;
                    align-items: center;
                    padding: .45rem 1rem;
                    text-align: center;
                    /* box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1); */
                }
                
                .dataTables_info{
                    font-size: smaller;
                    font-weight: 500;
                    color: #777;
                    padding-top : 0px !important;
                }
        
                /* user pages section */
                .content-header > h1 > small {
                    font-size: 0.8rem !important;
                    font-weight: 600 !important;
                    color : rgb(154 164 178) !important;
                }

                .content {
                    padding: 0 15px !important;
                }
                @media screen and (max-width: 767px) {
                    .table-responsive{
                        border : none
                    }
                }
                /* form section */
                .error{
                    font-size: 0.7rem !important;
                    font-weight: 600 !important;
                }
                .help-block{
                    font-size: 0.7rem !important;
                    font-weight: 500 !important;
                }
                .form-group > input
                ,.dt-buttons > .custom-dw-btn
                ,.form-group > select
                ,.form-group > textarea
                ,.form-group > .selection
                ,.form-group > .select2-container--default .select2-selection--multiple
                ,.select2-container--default .select2-selection--single 
                ,.input-group > .form-control
                ,.form-group >  .multi-input > select
                ,.form-group >  .multi-input > input
                , .input_inline > .form-control 
                , .input_inline > span > .form-control 
                
                {
                    border-radius: 0.5rem !important;
                    font-size: 0.7rem !important;
                    font-weight: 600 !important;
                    border : 1px solid #ddd !important;
                    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05), 0 1px 2px 0 rgba(0, 0, 0, 0.03);
                }
                .form-group > input:focus
                ,.form-group > select:focus
                ,.form-group > textarea:focus
                ,.form-group > .select2-container--default .select2-selection--multiple:focus
                {
                    border : 2px solid oklch(70.9% 0.01 56.259) !important;
                }
                .input_inline {
                    column-gap : 0.25rem !important;
                }
                .text-white > .fa-info-circle:before{
                    color : white !important;
                }
                
                /* user info card section */
                .profile-user-img{
                    width: 7rem !important;
                    border : none !important
                }
                .user-role{
                    margin-top: -5px;
                    font-weight: 400;
                    font-size: 0.7rem;
                    max-width: fit-content;
                    padding: 0.25rem 0.5rem;
                    color : white;
                    border-radius: 0.5rem;
                }
                .active-status{
                    padding: 0.25rem 0.5rem;
                    font-weight: 600;
                    font-size: 0.7rem;
                    max-width: fit-content;
                    border-radius: 0.5rem;
                }
                .list-group-unbordered>.list-group-item >b{
                    font-size: 0.8rem !important;
                    color: #777 !important;
                    font-weight: 600;
                }
                .list-group-unbordered>.list-group-item >a{
                    font-size: 0.8rem !important;
                    color: #333 !important;
                    font-weight: 600;
                }
                .list-group-unbordered>.list-group-item{
                    border: 1px solid #ddd !important;
                    background: #f4f4f4;
                    margin: 0.5rem 0px;
                    border-radius: 0.5rem;
                    padding: 0.25rem 0.75rem !important;
                    display: flex;
                    flex-direction: column;
                }
                /* the rest od user info section */
                .document_note_body > .btn {
                    background-color: white !important;
                }
                
                #user_info_tab p {
                    color: #4b5563; 
                    font-size: 0.7rem; 
                    font-weight: 600;
                    margin: 0.5rem 0rem;
                }

                #user_info_tab p strong {
                    color: #111827; 
                    font-size: 0.8rem; 
                    font-weight: 700;
                    margin: 0 0.25rem;
                }

                
                #user_info_tab .col-md-4,
                #user_info_tab .col-md-6 {
                margin-bottom: 1.5rem;
                }

                /* custom nav tabs section */
                .custom-nav-tabs > li{
                    border: none !important;
                    color: #4B5563; /* default text color */
                    background-color: #F9FAFB;
                    transition: all 0.3s ease;
                    align-content : center;

                }
                .custom-nav-tabs > li > a {
                    transition: all 0.3s ease;
                }

                .custom-nav-tabs > li > a:hover {
                    background-color: #E5E7EB;
                    color: #1F2937;
                }

                /* Active tab */
                .custom-nav-tabs > li.active > a,{
                    border : none !important;

                }
                .custom-nav-tabs > li > a{ border : none !important}
                .custom-nav-tabs > li.active ,
                .custom-nav-tabs > li.active :focus,
                .custom-nav-tabs > li.active :hover {
                    background-color: #FFFFFF;
                    color: #111827;
                    font-weight: 600;
                }

                /* adding docs in user info page */
                .tox {
                    border-radius: 0.5rem !important;
                    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05), 0 1px 2px 0 rgba(0, 0, 0, 0.03) !important;
                }
                .dropzone {
                    border-radius: 0.5rem !important;
                    margin: auto;
                    font-size: 0.8rem !important;
                    font-weight: 600 !important;
                    border: 1px solid #ddd !important;
                    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05), 0 1px 2px 0 rgba(0, 0, 0, 0.03);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }
                hr{
                    border: 1px solid #ddd !important;
                }

                /* sidebar section */
                .custom_sbb:hover,
                .cutsom_active_sbb,
                .custom_active_wdsbb
                {
                    background-color: {{ $hoverBgColor }};
                    color : white;
                }
                .custom_active_child_sbb
                ,.custom_child_sbb:hover
                {
                    color: #ddd !important;
                    background:{{ $hoverBgColor }};
                    border-radius: 0.25rem;
                }

                /* group input section */
                .input-group
                {
                    display: flex;
                    column-gap: 0.25rem;
                    align-items: center;
                }
                .input-group-addon{
                    border-radius: 0.5rem !important;
                    height: 34px;
                    border : none !important;
                }
                #document{
                    max-width: -webkit-fill-available;
                    height: 2rem;
                    display: flex;
                    align-items: center;
                    padding: 0.2rem;
                }

                /* modal-content */
                .modal-content{
                    border-radius : 0.5rem;
                }
                /* modal footeer buttons */
                .tw-dw-btn
                ,.more_btn
                {
                    min-height: 2rem !important;
                    height: 2.2rem !important;
                    padding: 0 1.5rem !important;
                }
                .more_btn{
                    border-radius: 0.5rem !important;
                }

                /* contact info section */
                .details-title{
                    display : flex;
                    align-items: center;
                    column-gap : 0.25rem;
                }
                .details-small{
                    font-weight : 400;
                    font-size : 0.8rem;
                }
                /* add button */
                .add-btn
                ,.input-group-addon
                {
                    display: inline-flex;
                    cursor: pointer;
                    padding: 0.5rem 1rem;
                    border-radius: 0.75rem;
                    align-items : center;
                    justify-content: center;
                    font-size: .875rem;
                    /* line-height: 1.25rem; */
                    font-weight: 500;
                    color : white;
                    background-color: {{ $bgColor }} !important;

                }
                .add-btn > i{
                    color : white !important;
                }
                .custom-btn-group {
                    display: inline-flex;
                    border-radius: 0.75rem;
                    overflow: hidden; /* to keep children rounded */
                }

                .custom-btn {
                    cursor: pointer;
                    padding: 0.5rem 1rem;
                    border-radius: 0; /* reset default label border-radius to 0 so group radius works */
                    align-items: center;
                    justify-content: center;
                    font-size: 0.8rem;
                    font-weight: 400;
                    color: white !important;
                    background-color: {{ $bgColor }} !important;
                    display: inline-flex;
                    gap: 0.5rem;
                    border: none;
                    transition: background-color 0.3s ease;
                }

                .custom-btn.active,
                .custom-btn:hover {
                    background-color: {{ $hoverBgColor }} !important;
                    color: white !important;
                }

                /* Optional: Fix input inside labels */
                .custom-btn input[type="radio"] {
                    position: absolute;
                    clip: rect(0,0,0,0);
                    pointer-events: none;
                }.custom-btn.active {
                    background-color: {{ $focusColor }} !important;
                    color: #ffd700 !important; /* golden text for active */
                    font-weight: 700;
                }
                .custom-btn:focus,
                .custom-btn:focus-visible,
                .custom-btn:active,
                .custom-btn.active,
                .custom-btn:focus:active,
                .custom-btn.active:focus {
                outline: none !important;
                box-shadow: none !important;
                border-color: transparent !important;
                }
                .add-btn:hover{
                    background-color: {{ $hoverBgColor }} !important;
                }

                .checkbox label{
                    padding-right : 0 !important;
                    padding-left : 0 !important;
                }
                td input.form-control
                ,td select.form-control
                {
                    border-radius: 0.25rem !important;
                }
                .quick_add_unit{
                    border-radius: 0.5rem !important;
                }
                .checkbox input[type=checkbox]{
                    margin-left: 0 !important;
                    margin-right: 0 !important;
                }
                .checkbox b{
                    margin : 0 15px !important;
                }
                .add-product-price-table th{
                    background-color : {{ $bgColor }} !important;
                }
                .box-custom{
                    border-top : 3px solid {{ $bgColor }} !important;
                }
                .custom-header{
                    color: {{ $bgColor }} !important;
                }
                .custom-header:hover {
                    color: {{ $hoverBgColor }} !important;
                }
                .custom-addon{
                    padding: 0.6rem 0.7rem !important;
                    margin: 0 0.2rem;
                }
                .nav-tabs.nav-justified>li>a{
                    font-size: 0.9rem !important;
                }
                .custom-tbtn{
                    border: 1px solid;
                    border-radius: 0.75rem;
                    font-size: x-small;
                    padding: 0.25rem 0.5rem;
                    margin:0.25rem;
                    display: flex;
                    align-items: center;
                    column-gap: 0.25rem;
                }
                .custom-tbtn:disabled, .custom-tbtn[disabled], .custom-tbtn:disabled:hover{
                    --tw-border-opacity: 0;
                    --tw-bg-opacity: 0.2;
                    --tw-text-opacity: 0.2;
                    background-color: var(--fallback-n, oklch(var(--n) / var(--tw-bg-opacity)));
                    color: var(--fallback-bc, oklch(var(--bc) / var(--tw-text-opacity)));
                    border : none !important;
                }
                .option-div{
                    border-radius: 0.5rem !important;
                }
                .file-btn{
                    display : flex;
                    flex-direction : column;
                    text-align : center;
                    color : {{ $bgColor }} !important;
                }
                .file-btn:hover{
                    color : {{ $hoverBgColor }} !important;
                }
                .row{
                    margin : 0 !important;
                }
                .tw-dw-badge-custom{
                    background-color : {{ $bgColor }} !important;
                    color : white !important;
                }
                .subscription-title{
                    color: {{ $hoverBgColor }} !important;
                }
                a.list-group-item.active
                
                {
                    background: {{ $bgColor }} !important;
                    color: white !important;
                    font-weight: bolder;
                }
                a.list-group-item.active:hover
                ,.navbar-default .navbar-nav>.active>a
                {
                    background: {{ $hoverBgColor }} !important;
                    color: white !important;
                }
                .navbar-default .navbar-nav>li>a{
                    border-radius: 0.25rem;
                    font-weight: bolder;
                }
                .navbar-brand{
                    font-weight:bolder;
                    color : {{ $bgColor }} !important;
                }
                .file-caption{
                    width : 75% !important;
                }
                .list-group-item{
                    padding:10px 1px;
                }
                .pos-tab-content{
                    padding : 0 !important;
                }
                .pos-tab{
                    padding : 0 0.5rem !important;
                }
                .pagination>.active>a,
                .dataTables_wrapper .dataTables_paginate .paginate_button.current, .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover{
                    background-color: {{ $bgColor }} !important;
                    color: white !important;
                    border : none !important;
                    margin : 0 4px !important;
                }
                .selectable_td > .row-select{
                    margin-top : 17px !important
                }
                
                /* .div.pos-tab-menu div.list-group > a:hover{
                    background-color: {{ $hoverBgColor }} !important;
                    color: white !important;
                } */
                /* print section */
                @media print {
                    #print-only-content {
                        display: block !important;
                    }
                    .no-print {
                        display: none !important;
                    }
                }
            </style>

<!--
            <a href="https://wa.me/967777335118" class="whatsapp-button" target="_blank">
                <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" alt="WhatsApp" class="whatsapp-icon">
           </a>
-->

<script>
  if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/serviceworker.js')
      .then(function(reg) {
        console.log('Service Worker registered with scope:', reg.scope);
      }).catch(function(err) {
        console.error('Service Worker registration failed:', err);
      });
  }
</script>

<script>
  let deferredPrompt;
  window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPrompt = e;

    const btn = document.getElementById('installBtn');
    if (btn) btn.style.display = 'block';

    btn?.addEventListener('click', () => {
      deferredPrompt.prompt();
      deferredPrompt.userChoice.then((choiceResult) => {
        if (choiceResult.outcome === 'accepted') {
          console.log('User accepted install');
        } else {
          console.log('User dismissed install');
        }
        deferredPrompt = null;
        btn.style.display = 'none';
      });
    });
  });
</script>

<button id="installBtn"
    style="
        position: fixed;
        bottom: 80px;
        right: 20px;
        display: none;
        z-index: 1000;
        background: #000;
        color: white;
        padding: 16px 28px;
        font-size: 18px;
        font-weight: bold;
        border: none;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    ">
    📲 تثبيت ERP Enough
</button>



</body>
<style>
    @media print {
  #scrollable-container {
    overflow: visible !important;
    height: auto !important;
  }
}
</style>
<style>
    .small-view-side-active {
        display: grid !important;
        z-index: 1000;
        position: absolute;
    }
    .overlay {
        width: 100vw;
        height: 100vh;
        background: rgba(0, 0, 0, 0.8);
        position: fixed;
        top: 0;
        left: 0;
        display: none;
        z-index: 20;
    }

    .tw-dw-btn.tw-dw-btn-xs.tw-dw-btn-outline {
        width: max-content;
        margin: 2px;
    }

    #scrollable-container{
        position:relative;
    }
    



</style>

</html>
