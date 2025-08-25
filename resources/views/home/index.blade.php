@php
    $bgColorsDark = [
        'blue'    => '#00359e', // blue-900
        'orange'  => '#772917', // orange-900
        'red'     => '#7a271a', // red-900
        'green'   => '#074d31', // green-900
        'yellow'  => '#7a2e0e ', // yellow-900
        'purple'  => '#3e1c96', // purple-900
        'pink'    => '#831843', // pink-900
        'gray'    => '#111827', // gray-900
        'sky'     => '#0b4a6f', // sky-900
        'primary' => '#00359e', // fallback if session is 'primary'
    ];


    $rawTheme = session('business.theme_color') ?? 'primary';
    $bgColor = $bgColorsDark[$rawTheme];
@endphp
@extends('layouts.app')
@section('title', __('home.home'))
<!-- to be able to use the theme primary color on style section -->

@section('content')

    <!-- <div class="tw-pb-6 tw-bg-gradient-to-r tw-from-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-800 tw-to-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-900 xl:tw-pb-0 "> -->
    <div class="tw-pb-6 tw-bg-gray-100 xl:tw-pb-0 ">
        <div class="tw-px-5 tw-pt-3">
            {{-- <div class="sm:tw-flex sm:tw-items-center sm:tw-justify-between sm:tw-gap-12 ">
                <h1 class="tw-text-2xl tw-font-medium tw-tracking-tight tw-text-white">
                    {{ __('home.welcome_message', ['name' => Session::get('user.first_name')]) }} 
                </h1>
            </div> --}}
                    <div class="sm:tw-flex sm:tw-items-center sm:tw-justify-between tw-flex-col sm:tw-flex-row">
                        <div class="tw-mt-2 tw-pb-4 tw-w-full sm:tw-w-1/2">
                            <!-- <h1 class="tw-text-2xl md:tw-text-4xl tw-tracking-tight tw-text-primary-800 tw-font-semibold text-white tw-mb-6 md:tw-mb-0"> -->
                            <h1 
                                class="tw-text-2xl md:tw-text-4xl tw-tracking-tight tw-text-primary-800 tw-font-semibold tw-mb-6 md:tw-mb-0"
                                style="color:{{ $bgColor }}"
                            >
                                {{ __('home.welcome_message', ['name' => Session::get('user.first_name')]) }} 👋
                            </h1>
                        </div>
    
                        @if (auth()->user()->can('dashboard.data'))
                            @if ($is_admin)
                                <div class="tw-w-full sm:tw-w-1/2 tw-flex tw-flex-col sm:tw-flex-row tw-gap-2 sm:tw-items-center sm:tw-justify-between">
                                    <div class="tw-mt-2 sm:tw-w-1/2 tw-w-full">
                                        @if (count($all_locations) > 1)
                                            {!! Form::select('dashboard_location', $all_locations, null, [
                                                'class' => 'form-control select2 tw-block tw-w-full tw-px-4 tw-py-2 tw-text-base tw-text-gray-700 tw-bg-white tw-border tw-border-gray-300 tw-rounded-lg tw-shadow-sm focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-border-blue-500',
                                                'placeholder' => __('lang_v1.select_location'),
                                                'id' => 'dashboard_location',
                                            ]) !!}
                                        @endif
                                    </div>
                
                                    <div class="tw-mt-2 sm:tw-w-1/2 tw-flex-grow tw-w-full tw-text-right">
                                        @if ($is_admin)
                                            <button type="button" id="dashboard_date_filter"
                                                class="tw-inline-flex tw-items-center tw-justify-between tw-w-full tw-gap-1 tw-px-3 tw-text-xs tw-bg-white sm:tw-w-full hover:tw-tw-text-white tw-bg-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-800-50"
                                                style="padding-top: 0.43rem;padding-bottom: 0.43rem;border-radius: 0.5rem;border: 1px solid #ddd !important;box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05), 0 1px 2px 0 rgba(0, 0, 0, 0.03);">
                                                <div class="tw-flex tw-flex-grow">
                                                    <svg aria-hidden="true" class="tw-size-5" xmlns="http://www.w3.org/2000/svg"
                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none"
                                                        stroke-linecap="round" stroke-linejoin="round">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                        <path
                                                            d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z" />
                                                        <path d="M16 3v4" />
                                                        <path d="M8 3v4" />
                                                        <path d="M4 11h16" />
                                                        <path d="M7 14h.013" />
                                                        <path d="M10.01 14h.005" />
                                                        <path d="M13.01 14h.005" />
                                                        <path d="M16.015 14h.005" />
                                                        <path d="M13.015 17h.005" />
                                                        <path d="M7.01 17h.005" />
                                                        <path d="M10.01 17h.005" />
                                                    </svg>
                                                    <span class="tw-mr-3">
                                                        {{ __('messages.filter_by_date') }}
                                                    </span>
                                                </div>
                                                <svg aria-hidden="true" class="tw-size-4" xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                                    stroke-linecap="round" stroke-linejoin="round">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                    <path d="M6 9l6 6l6 -6" />
                                                </svg>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>
                    @if (auth()->user()->can('dashboard.data'))
                        @if ($is_admin)
                            <div class="tw-grid tw-grid-cols-1 tw-gap-4 tw-mt-6 sm:tw-grid-cols-2 xl:tw-grid-cols-4 sm:tw-gap-5">
                            
                                <div class="newBg tw-transition-all tw-duration-200 tw-bg-white tw-shadow-sm hover:tw-shadow-md tw-rounded-xl hover:tw-translate-y-0.5 tw-ring-1 tw-ring-gray-200">
                                    <div class="tw-p-4 sm:tw-p-5">
                                        <div class="tw-flex tw-items-center tw-gap-4">
                                            <div
                                                class="tw-inline-flex tw-items-center tw-justify-center tw-w-10 tw-h-10 tw-rounded-full sm:tw-w-12 sm:tw-h-12 tw-shrink-0 tw-bg-sky-100 tw-text-sky-500">
                                                <svg aria-hidden="true" class="tw-w-6 tw-h-6" xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                                    stroke-linecap="round" stroke-linejoin="round">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                    <path d="M6 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                                                    <path d="M17 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                                                    <path d="M17 17h-11v-14h-2" />
                                                    <path d="M6 5l14 1l-1 7h-13" />
                                                </svg>
                                            </div>

                                            <div class="tw-flex-1 tw-min-w-0 white-tooltip">
                                                <p
                                                    class="tw-text-sm tw-font-semibold tw-text-white tw-truncate tw-whitespace-nowrap">
                                                    {{ __('home.total_sell') }}
                                                </p>
                                                <p
                                                    class="total_sell tw-font-semibold tw-mt-1 tw-text-xs tw-text-gray-500 smallText">
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- <div class="tw-transition-all tw-duration-200 tw-bg-white tw-shadow-sm hover:tw-shadow-md tw-rounded-xl hover:tw--translate-y-0.5 tw-ring-1 tw-ring-gray-200"> -->
                                <div class="tw-transition-all tw-duration-200 tw-bg-white tw-shadow-sm hover:tw-shadow-md tw-rounded-xl hover:tw--translate-y-0.5 tw-ring-1 tw-ring-gray-200">
                                    <div class="tw-p-4 sm:tw-p-5">
                                        <div class="tw-flex tw-items-center tw-gap-4">
                                            <div
                                                class="tw-inline-flex tw-items-center tw-justify-center tw-w-10 tw-h-10 tw-text-green-500 tw-bg-green-100 tw-rounded-full sm:tw-w-12 sm:tw-h-12 tw-shrink-0">
                                                <svg  xmlns="http://www.w3.org/2000/svg"  class="tw-h-6 tw-w-6" viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-moneybag"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9.5 3h5a1.5 1.5 0 0 1 1.5 1.5a3.5 3.5 0 0 1 -3.5 3.5h-1a3.5 3.5 0 0 1 -3.5 -3.5a1.5 1.5 0 0 1 1.5 -1.5" /><path d="M4 17v-1a8 8 0 1 1 16 0v1a4 4 0 0 1 -4 4h-8a4 4 0 0 1 -4 -4" /></svg>
                                            </div>

                                            <div class="tw-flex-1 tw-min-w-0">
                                                <p
                                                    class="tw-text-sm tw-font-semibold tw-text-black tw-truncate tw-whitespace-nowrap">
                                                    {{ __('lang_v1.net') }} @show_tooltip(__('lang_v1.net_home_tooltip'))
                                                </p>
                                                <p class="net tw-font-semibold tw-mt-1 tw-text-xs">
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="newBg tw-transition-all tw-duration-200 tw-shadow-sm hover:tw-shadow-md tw-rounded-xl hover:tw--translate-y-0.5 tw-ring-1 tw-ring-gray-200">
                                    <div class="tw-p-4 sm:tw-p-5">
                                        <div class="tw-flex tw-items-center tw-gap-4">
                                            <div
                                                class="tw-inline-flex tw-items-center tw-justify-center tw-w-10 tw-h-10 tw-text-yellow-500 tw-bg-yellow-100 tw-rounded-full sm:tw-w-12 sm:tw-h-12 shrink-0">
                                                <svg aria-hidden="true" class="tw-w-6 tw-h-6" xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                                    stroke-linecap="round" stroke-linejoin="round">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                    <path d="M14 3v4a1 1 0 0 0 1 1h4" />
                                                    <path
                                                        d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" />
                                                    <path d="M9 7l1 0" />
                                                    <path d="M9 13l6 0" />
                                                    <path d="M13 17l2 0" />
                                                </svg>
                                            </div>

                                            <div class="tw-flex-1 tw-min-w-0 white-tooltip">
                                                <p
                                                    class="tw-text-sm tw-font-semibold tw-text-white tw-truncate tw-whitespace-nowrap">
                                                    {{ __('home.invoice_due') }}
                                                </p>
                                                <p
                                                    class="invoice_due tw-font-semibold tw-mt-1 tw-text-xs  smallText">
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- <div class="tw-transition-all tw-duration-200 tw-bg-white tw-shadow-sm hover:tw-shadow-md tw-rounded-xl hover:tw--translate-y-0.5 tw-ring-1 tw-ring-gray-200"> -->
                                <div class="tw-bg-white tw-transition-all tw-duration-200 tw-shadow-sm hover:tw-shadow-md tw-rounded-xl hover:tw--translate-y-0.5 tw-ring-1 tw-ring-gray-200">
                                    <div class="tw-p-4 sm:tw-p-5">
                                        <div class="tw-flex tw-items-center tw-gap-4">
                                            <div
                                                class="tw-inline-flex tw-items-center tw-justify-center tw-w-10 tw-h-10 tw-text-red-500 tw-bg-red-100 tw-rounded-full sm:tw-w-12 sm:tw-h-12 shrink-0">
                                                <svg aria-hidden="true" class="tw-w-6 tw-h-6" xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                                    stroke-linecap="round" stroke-linejoin="round">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                    <path d="M21 7l-18 0" />
                                                    <path d="M18 10l3 -3l-3 -3" />
                                                    <path d="M6 20l-3 -3l3 -3" />
                                                    <path d="M3 17l18 0" />
                                                </svg>
                                            </div>

                                            <div class="tw-flex-1 tw-min-w-0">
                                                <p
                                                    class="tw-text-sm tw-font-semibold tw-text-black tw-truncate tw-whitespace-nowrap">
                                                    {{ __('lang_v1.total_sell_return') }}
                                                    <i class="fa fa-info-circle text-info hover-q no-print" aria-hidden="true" data-container="body"
                                                    data-toggle="popover" data-placement="auto bottom" id="total_srp"
                                                    data-value="{{ __('lang_v1.total_sell_return') }}-{{ __('lang_v1.total_sell_return_paid') }}"
                                                    data-content="" data-html="true" data-trigger="hover"></i>
                                                </p>
                                                <p class="total_sell_return tw-font-semibold tw-mt-1 tw-text-xs tw-text-gray-500">
                                                </p>
                                                {{-- <p class="mb-0 text-muted fs-10 mt-5">{{ __('lang_v1.total_sell_return') }}: <span
                                                        class="total_sr"></span><br>
                                                    {{ __('lang_v1.total_sell_return_paid') }}<span class="total_srp"></span></p> --}}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif
              
        </div>
        @if (auth()->user()->can('dashboard.data'))
            @if ($is_admin)
                <div class="tw-relative">
                    <div class="tw-absolute tw-inset-0 tw-grid" aria-hidden="true">
                        <div class="tw-bg-gradient-to-r tw-from-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-800 tw-to-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-900"></div>
                        <div class="tw-bg-gradient-to-r tw-from-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-800 tw-to-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-900 xl:tw-bg-none xl:tw-bg-gray-100">
                        </div>
                    </div>
                    <!-- <div class="tw-px-5 tw-isolate"> -->
                    <div class="tw-px-5 tw-isolate tw-bg-gray-100">
                        <div
                            class="tw-grid tw-grid-cols-1 tw-gap-4 tw-mt-4 sm:tw-mt-6 sm:tw-grid-cols-2 xl:tw-grid-cols-4 sm:tw-gap-5">
                            <!-- <div class="tw-transition-all tw-duration-200 tw-shadow-sm tw-rounded-xl hover:tw-shadow-md hover:tw--translate-y-0.5 tw-ring-1 tw-ring-gray-200"> -->
                            <div class="newBg tw-transition-all tw-duration-200 tw-shadow-sm tw-rounded-xl hover:tw-shadow-md hover:tw--translate-y-0.5 tw-ring-1 tw-ring-gray-200">
                                <div class="tw-p-4 sm:tw-p-5">
                                    <div class="tw-flex tw-items-center tw-gap-4">
                                        <div
                                            class="tw-inline-flex tw-items-center tw-justify-center tw-w-10 tw-h-10 tw-rounded-full sm:tw-w-12 sm:tw-h-12 shrink-0 tw-bg-yellow-100 tw-text-yellow-500">
                                            <svg aria-hidden="true" class="tw-w-6 tw-h-6"
                                                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2"
                                                stroke="currentColor" fill="none" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                <path d="M12 3v12"></path>
                                                <path d="M16 11l-4 4l-4 -4"></path>
                                                <path d="M3 12a9 9 0 0 0 18 0"></path>
                                            </svg>
                                        </div>

                                        <div class="tw-flex-1 tw-min-w-0 white-tooltip">
                                            <p
                                                class="tw-text-sm tw-font-semibold tw-text-white tw-truncate tw-whitespace-nowrap">
                                                {{ __('home.total_purchase') }}
                                            </p>
                                            <p
                                                class="total_purchase tw-font-semibold tw-mt-1 tw-text-xs smallText">
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="tw-transition-all tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-xl hover:tw-shadow-md hover:tw--translate-y-0.5 tw-ring-1 tw-ring-gray-200">
                                <div class="tw-p-4 sm:tw-p-5">
                                    <div class="tw-flex tw-items-center tw-gap-4">
                                        <div
                                            class="tw-inline-flex tw-items-center tw-justify-center tw-w-10 tw-h-10 tw-text-red-500 tw-bg-red-100 tw-rounded-full sm:tw-w-12 sm:tw-h-12 shrink-0">
                                            <svg  xmlns="http://www.w3.org/2000/svg"  class="tw-h-6 tw-w-6"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-basket-code"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 10l-2 -6" /><path d="M7 10l2 -6" /><path d="M11 20h-3.756a3 3 0 0 1 -2.965 -2.544l-1.255 -7.152a2 2 0 0 1 1.977 -2.304h13.999a2 2 0 0 1 1.977 2.304c-.21 1.202 -.37 2.104 -.475 2.705" /><path d="M10 14a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" /><path d="M20 21l2 -2l-2 -2" /><path d="M17 17l-2 2l2 2" /></svg>
                                        </div>

                                        <div>
                                            <p class="tw-text-sm tw-font-semibold tw-text-black">
                                                {{ __('home.purchase_due') }}
                                            </p>
                                            <p
                                                class="purchase_due tw-font-semibold tw-mt-1 tw-text-xs tw-text-gray-500">

                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="newBg tw-transition-all tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-xl hover:tw-shadow-md hover:tw--translate-y-0.5 tw-ring-1 tw-ring-gray-200">
                                <div class="tw-p-4 sm:tw-p-5">
                                    <div class="tw-flex tw-items-center tw-gap-4">
                                        <div
                                            class="tw-inline-flex tw-items-center tw-justify-center tw-w-10 tw-h-10 tw-text-sky-500 tw-bg-sky-100 tw-rounded-full sm:tw-w-12 sm:tw-h-12 shrink-0">
                                            <svg aria-hidden="true" class="tw-w-6 tw-h-6"
                                                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2"
                                                stroke="currentColor" fill="none" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                <path
                                                    d="M5 21v-16a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v16l-3 -2l-2 2l-2 -2l-2 2l-2 -2l-3 2" />
                                                <path d="M15 14v-2a2 2 0 0 0 -2 -2h-4l2 -2m0 4l-2 -2" />
                                            </svg>
                                        </div>

                                        <div class="tw-flex-1 tw-min-w-0 white-tooltip">
                                            <p
                                                class="tw-text-sm tw-font-semibold tw-text-white tw-truncate tw-whitespace-nowrap">
                                                {{ __('lang_v1.total_purchase_return') }}
                                                <i class="fa fa-info-circle text-info hover-q no-print" aria-hidden="true" data-container="body"
                                                data-toggle="popover" data-placement="auto bottom" id="total_prp"
                                                data-value="{{ __('lang_v1.total_purchase_return') }}-{{ __('lang_v1.total_purchase_return_paid') }}"
                                                data-content="" data-html="true" data-trigger="hover"></i>
                                            </p>
                                            <p class="smallText total_purchase_return tw-font-semibold tw-mt-1 tw-text-xs"></p>
                                            {{-- <p class="mb-0 text-muted fs-10 mt-5">
                                                {{ __('lang_v1.total_purchase_return') }}: <span
                                                    class="total_pr"></span><br>
                                                {{ __('lang_v1.total_purchase_return_paid') }}<span
                                                    class="total_prp"></span></p> --}}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="tw-transition-all tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-xl hover:tw-shadow-md hover:tw--translate-y-0.5 tw-ring-1 tw-ring-gray-200">
                                <div class="tw-p-4 sm:tw-p-5">
                                    <div class="tw-flex tw-items-center tw-gap-4">
                                        <div
                                            class="tw-inline-flex tw-items-center tw-justify-center tw-w-10 tw-h-10 tw-text-green-500 tw-bg-green-100 tw-rounded-full sm:tw-w-12 sm:tw-h-12 shrink-0">
                                            <svg aria-hidden="true" class="tw-w-6 tw-h-6"
                                                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2"
                                                stroke="currentColor" fill="none" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                <path
                                                    d="M5 21v-16a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v16l-3 -2l-2 2l-2 -2l-2 2l-2 -2l-3 2">
                                                </path>
                                                <path
                                                    d="M14.8 8a2 2 0 0 0 -1.8 -1h-2a2 2 0 1 0 0 4h2a2 2 0 1 1 0 4h-2a2 2 0 0 1 -1.8 -1">
                                                </path>
                                                <path d="M12 6v10"></path>
                                            </svg>
                                        </div>

                                        <div class="tw-flex-1 tw-min-w-0">
                                            <p
                                                class="tw-text-sm tw-font-semibold tw-text-black tw-truncate tw-whitespace-nowrap">
                                                {{ __('lang_v1.expense') }}
                                            </p>
                                            <p
                                                class="total_expense tw-font-semibold tw-mt-1 tw-text-xs tw-text-gray-500 tw-text-gray-500 ">

                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- @if (!empty($widgets['after_sale_purchase_totals']))
                    @foreach ($widgets['after_sale_purchase_totals'] as $widget)
                        {!! $widget !!}
                    @endforeach
                @endif --}}
            @endif
        @endif
    </div>
    @if (auth()->user()->can('dashboard.data'))
        <div class="tw-px-5 tw-py-6">
            <div class="tw-grid tw-grid-cols-1 tw-gap-4 sm:tw-gap-5 lg:tw-grid-cols-2">
                @if (auth()->user()->can('sell.view') || auth()->user()->can('direct_sell.view'))
                    {{-- @if (!empty($widgets['after_sales_last_30_days']))
                        @foreach ($widgets['after_sales_last_30_days'] as $widget)
                            {!! $widget !!}
                        @endforeach
                    @endif --}}
                        
                    <!-- first chart -->
                    @if (!empty($all_locations))
                        <!-- <div
                            class="tw-transition-all lg:tw-col-span-2 xl:tw-col-span-2 tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-xl tw-ring-1 hover:tw-shadow-md hover:tw--translate-y-0.5 tw-ring-gray-200">
                            <div class="tw-p-4 sm:tw-p-5">
                                <div class="tw-flex tw-items-center tw-gap-2.5">
                                    <div
                                        class="tw-border tw-shadow-sm tw-flex tw-items-center tw-justify-center tw-rounded-full tw-p-2">
                                        <svg aria-hidden="true" class="tw-size-5 tw-text-sky-500 tw-shrink-0"
                                            xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2"
                                            stroke="currentColor" fill="none" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                            <path d="M6 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"></path>
                                            <path d="M17 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"></path>
                                            <path d="M17 17h-11v-14h-2"></path>
                                            <path d="M6 5l14 1l-1 7h-13"></path>
                                        </svg>
                                    </div>

                                    <h3 class="tw-font-bold tw-text-base lg:tw-text-xl">
                                        {{ __('home.sells_last_30_days') }}
                                    </h3>
                                </div>
                                <div class="tw-mt-5">
                                    <div
                                        class="tw-grid tw-overflow-hidden tw-w-full tw-h-100 tw-border tw-border-gray-200 tw-border-dashed tw-rounded-xl tw-bg-gray-50 ">
                                        <p class="tw-text-sm tw-italic tw-font-normal tw-text-gray-400">
                                            {!! $sells_chart_1->container() !!}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div> -->
                        <!-- custom chart.js chart -->
                        <div
                            class="tw-transition-all lg:tw-col-span-2 xl:tw-col-span-2 tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-xl tw-ring-1 hover:tw-shadow-md hover:tw--translate-y-0.5 tw-ring-gray-200">
                            <div class="tw-p-4 sm:tw-p-5">
                                <div class="tw-flex tw-items-center tw-gap-2.5">
                                    <div
                                        class="tw-bg-sky-100 tw-flex tw-items-center tw-justify-center tw-rounded-full tw-p-2">
                                        
                                        <svg  xmlns="http://www.w3.org/2000/svg" class="tw-text-sky-500 tw-size-5" viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-report-money"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 5h-2a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-12a2 2 0 0 0 -2 -2h-2" /><path d="M9 3m0 2a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v0a2 2 0 0 1 -2 2h-2a2 2 0 0 1 -2 -2z" /><path d="M14 11h-2.5a1.5 1.5 0 0 0 0 3h1a1.5 1.5 0 0 1 0 3h-2.5" /><path d="M12 17v1m0 -8v1" /></svg>
                                    </div>

                                    <h3 class="tw-font-bold tw-text-lg">
                                        {{ __('home.sells_last_30_days') }}
                                    </h3>
                                </div>
                                <div class="tw-mt-5 tw-flex tw-justify-center">
                                    <div id="chartContainer" class=" tw-grid tw-w-full tw-overflow-x-auto tw-bg-white tw-px-2 tw-pb-4 tw-pt-2 tw-border-2 tw-border-gray-200 tw-border-dashed tw-rounded-xl tw-bg-gray-50 ">
                                        <canvas id="sellsChart1"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    <!-- second chart -->
                    @if (!empty($all_locations))
                        <!-- <div
                            class="tw-transition-all lg:tw-col-span-2 xl:tw-col-span-2 tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-xl tw-ring-1 hover:tw-shadow-md hover:tw--translate-y-0.5 tw-ring-gray-200">
                            <div class="tw-p-4 sm:tw-p-5">
                                <div class="tw-flex tw-items-center tw-gap-2.5">
                                    <div
                                        class="tw-border tw-shadow-sm tw-flex tw-items-center tw-justify-center tw-rounded-full tw-p-2">
                                        <svg aria-hidden="true" class="tw-size-5 tw-text-sky-500 tw-shrink-0"
                                            xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2"
                                            stroke="currentColor" fill="none" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                            <path d="M6 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"></path>
                                            <path d="M17 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"></path>
                                            <path d="M17 17h-11v-14h-2"></path>
                                            <path d="M6 5l14 1l-1 7h-13"></path>
                                        </svg>
                                    </div>
                                    <h3 class="tw-font-bold tw-text-base lg:tw-text-xl">
                                        {{ __('home.sells_current_fy') }}
                                    </h3>
                                </div>
                                <div class="tw-mt-5">
                                    <div
                                        class="tw-grid tw-w-full tw-h-100 tw-border tw-border-gray-200 tw-border-dashed tw-rounded-xl tw-bg-gray-50 ">
                                        
                                        <p class="tw-text-sm tw-italic tw-font-normal tw-text-gray-400">
                                            {!! $sells_chart_2->container() !!}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div> -->
                        <!-- new chart.js chart -->
                        <div
                            class="tw-transition-all lg:tw-col-span-2 xl:tw-col-span-2 tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-xl tw-ring-1 hover:tw-shadow-md hover:tw--translate-y-0.5 tw-ring-gray-200">
                            <div class="tw-p-4 sm:tw-p-5">
                                <div class="tw-flex tw-items-center tw-gap-2.5">
                                    <div
                                        class="tw-bg-sky-100 tw-flex tw-items-center tw-justify-center tw-rounded-full tw-p-2">
                                        <svg  xmlns="http://www.w3.org/2000/svg" class="tw-text-sky-500 tw-size-5" viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-calendar-dollar"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M13 21h-7a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v3" /><path d="M16 3v4" /><path d="M8 3v4" /><path d="M4 11h12.5" /><path d="M21 15h-2.5a1.5 1.5 0 0 0 0 3h1a1.5 1.5 0 0 1 0 3h-2.5" /><path d="M19 21v1m0 -8v1" /></svg>
                                    </div>
                                    <h3 class="tw-font-bold tw-text-lg">
                                        {{ __('home.sells_current_fy') }}
                                    </h3>
                                </div>
                                <div class="tw-mt-5 tw-flex tw-justify-center">
                                    <div class=" tw-grid tw-w-full tw-overflow-x-auto tw-bg-white tw-px-2 tw-pb-4 tw-pt-2 tw-border-2 tw-border-gray-200 tw-border-dashed tw-rounded-xl tw-bg-gray-50">
                                        <canvas id="sellsChart2"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endif
                {{-- @if (!empty($widgets['after_sales_current_fy']))
                    @foreach ($widgets['after_sales_current_fy'] as $widget)
                        {!! $widget !!}
                    @endforeach
                @endif --}}
                @if (auth()->user()->can('sell.view') || auth()->user()->can('direct_sell.view'))
                    <div
                        class="tw-transition-all lg:tw-col-span-1 tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-xl tw-ring-1 hover:tw-shadow-md hover:tw--translate-y-0.5 tw-ring-gray-200">
                        <div class="tw-p-4 sm:tw-p-5">
                            <!-- TITLE -->
                            <div class="tw-flex tw-items-center tw-gap-2.5">
                                <!--  -->
                                <div class="tw-flex tw-items-center">
                                    <div
                                        class="tw-bg-yellow-100 tw-flex tw-items-center tw-justify-center tw-rounded-full tw-p-2">
                                        <svg  xmlns="http://www.w3.org/2000/svg" class="tw-text-yellow-500 tw-size-5" viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-wallet"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 8v-3a1 1 0 0 0 -1 -1h-10a2 2 0 0 0 0 4h12a1 1 0 0 1 1 1v3m0 4v3a1 1 0 0 1 -1 1h-12a2 2 0 0 1 -2 -2v-12" /><path d="M20 12v4h-4a2 2 0 0 1 0 -4h4" /></svg>
                                    </div>
                                    <div class="tw-px-2">
                                        <h3 class="tw-font-bold tw-text-lg">
                                            {{ __('lang_v1.sales_payment_dues') }}
                                            @show_tooltip(__('lang_v1.tooltip_sales_payment_dues'))
                                        </h3>
                                    </div>
                                </div>    
                                <!-- <div class="tw-w-full sm:tw-w-1/2 md:tw-w-1/2">
                                    {!! Form::select('sales_payment_dues_location', $all_locations, null, [
                                        'class' => 'form-control select2',
                                        'placeholder' => __('lang_v1.select_location'),
                                        'id' => 'sales_payment_dues_location',
                                    ]) !!}
                                </div> -->
                            </div>                            

                            <!-- FILTER DROPDOWN Placeholder (used by DataTables DOM) -->
                            <div id="sales_payment_dues_lf" class="tw-hidden" style="width:14rem">
                                {!! Form::select('sales_payment_dues_location', $all_locations, null, [
                                    'class' => 'form-control select2',
                                    'placeholder' => __('lang_v1.select_location'),
                                    'id' => 'sales_payment_dues_location',
                                ]) !!}
                            </div>
                            <div class="tw-flow-root tw-mt-5  tw-border-gray-200 bla">
                                <div class="tw--mx-4 tw--my-2 scrollable-container tw-overflow-x-auto sm:tw--mx-5">
                                    <div class="tw-inline-block tw-min-w-full tw-py-2 tw-align-middle sm:tw-px-5 ">
                                        
                                        <table class="table my-custom-dt table-striped" id="sales_payment_dues_table"
                                            style="width: 100%;">
                                            <!-- <thead class="tw-text-white tw-bg-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-800"> -->
                                            <thead class="tw-text-white tw-bg-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-800">
                                                <tr>
                                                    <th>@lang('contact.customer')</th>
                                                    <th>@lang('sale.invoice_no')</th>
                                                    <th>@lang('home.due_amount')</th>
                                                    <th>@lang('messages.action')</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                @can('purchase.view')
                    <div
                        class="tw-transition-all lg:tw-col-span-1 tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-xl tw-ring-1 hover:tw-shadow-md hover:tw--translate-y-0.5 tw-ring-gray-200">
                        <div class="tw-p-4 sm:tw-p-5">
                            <div class="tw-flex tw-items-center tw-gap-2.5">
                                <div class="tw-flex tw-items-center ">
                                    <div
                                        class="tw-bg-yellow-100 tw-flex tw-items-center tw-justify-center tw-rounded-full tw-p-2">
                                        
                                        <svg  xmlns="http://www.w3.org/2000/svg" class="tw-text-yellow-500 tw-size-5" viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-basket-check"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 10l-2 -6" /><path d="M7 10l2 -6" /><path d="M11 20h-3.756a3 3 0 0 1 -2.965 -2.544l-1.255 -7.152a2 2 0 0 1 1.977 -2.304h13.999a2 2 0 0 1 1.977 2.304l-.479 2.729" /><path d="M10 14a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" /><path d="M15 19l2 2l4 -4" /></svg>
                                    </div>
                                    <div class="tw-px-2 turnacate">
                                        <h3 class="tw-font-bold tw-text-lg">
                                            {{ __('lang_v1.purchase_payment_dues') }}
                                            @show_tooltip(__('tooltip.payment_dues'))
                                        </h3>
                                    </div>
                                </div>
                            </div>
                            <!-- FILTER DROPDOWN Placeholder (used by DataTables DOM) -->
                            <div id="purchase_payment_dues_lf" class="tw-hidden" style="width:14rem">
                                @if (count($all_locations) > 1)
                                    {!! Form::select('purchase_payment_dues_location', $all_locations, null, [
                                        'class' => 'form-control select2 ',
                                        'placeholder' => __('lang_v1.select_location'),
                                        'id' => 'purchase_payment_dues_location',
                                    ]) !!}
                                @endif
                            </div>
                            <div class="tw-flow-root tw-mt-5  tw-border-gray-200">
                                <div class="tw--mx-4 tw--my-2 tw-overflow-x-auto sm:tw--mx-5">
                                    <div class="tw-inline-block tw-min-w-full tw-py-2 tw-align-middle sm:tw-px-5">
                                        <table class="table table-pr table-striped" id="purchase_payment_dues_table"
                                            style="width: 100%;">
                                            <thead class="tw-text-white tw-bg-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-800">
                                                <tr>
                                                    <th>@lang('purchase.supplier')</th>
                                                    <th>@lang('purchase.ref_no')</th>
                                                    <th>@lang('home.due_amount')</th>
                                                    <th>@lang('messages.action')</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endcan
                @can('stock_report.view')
                    <div
                        class="tw-transition-all lg:tw-col-span-2 tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-xl tw-ring-1 hover:tw-shadow-md hover:tw--translate-y-0.5 tw-ring-gray-200">
                        <div class="tw-p-4 sm:tw-p-5">
                            <div class="tw-flex tw-items-center">
                                <div
                                    class="tw-bg-yellow-100 tw-flex tw-items-center tw-justify-center tw-rounded-full tw-p-2">
                                    <svg  xmlns="http://www.w3.org/2000/svg" class="tw-text-yellow-500 tw-size-5" viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-brand-stackoverflow"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 17v1a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-1" /><path d="M8 16h8" /><path d="M8.322 12.582l7.956 .836" /><path d="M8.787 9.168l7.826 1.664" /><path d="M10.096 5.764l7.608 2.472" /></svg>
                                </div>
                                <div class="tw-flex tw-items-center tw-flex-1 tw-min-w-0 tw-gap-1">
                                    <div class="tw-px-2">
                                        <h3 class="tw-font-bold tw-text-lg">
                                            {{ __('home.product_stock_alert') }}
                                            @show_tooltip(__('tooltip.product_stock_alert'))
                                        </h3>
                                    </div>
                                    
                                </div>
                            </div>
                            <!-- FILTER DROPDOWN Placeholder (used by DataTables DOM) -->
                             <div id="product_stock_alert_lf" class="tw-hidden" style="width:14rem">
                                @if (count($all_locations) > 1)
                                    {!! Form::select('stock_alert_location', $all_locations, null, [
                                        'class' => 'form-control select2',
                                        'placeholder' => __('lang_v1.select_location'),
                                        'id' => 'stock_alert_location',
                                    ]) !!}
                                @endif
                            </div>
                            <div class="tw-flow-root tw-mt-5  tw-border-gray-200">
                                <div class="tw--mx-4 tw--my-2 tw-overflow-x-auto sm:tw--mx-5">
                                    <div class="tw-inline-block tw-min-w-full tw-py-2 tw-align-middle sm:tw-px-5">
                                        <table class="table table-striped ajax_view" id="stock_alert_table"
                                            style="width: 100%;">
                                            <thead class="tw-text-white tw-bg-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-800">
                                                <tr>
                                                    <th>@lang('sale.product')</th>
                                                    <th>@lang('business.location')</th>
                                                    <th>@lang('report.current_stock')</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @if (session('business.enable_product_expiry') == 1)
                        <div
                            class="tw-transition-all lg:tw-col-span-1 tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-xl tw-ring-1 hover:tw-shadow-md hover:tw--translate-y-0.5 tw-ring-gray-200">
                            <div class="tw-p-4 sm:tw-p-5">
                                <div class="tw-flex tw-items-center">
                                    <div
                                        class="tw-bg-yellow-100 tw-flex tw-items-center tw-justify-center tw-rounded-full tw-p-2">
                                        <svg  xmlns="http://www.w3.org/2000/svg" class="tw-text-yellow-500 tw-size-5" viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-bell-x"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M13 17h-9a4 4 0 0 0 2 -3v-3a7 7 0 0 1 4 -6a2 2 0 1 1 4 0a7 7 0 0 1 4 6v2" /><path d="M9 17v1a3 3 0 0 0 4.194 2.753" /><path d="M22 22l-5 -5" /><path d="M17 22l5 -5" /></svg>
                                    </div>
                                    <div class="tw-flex tw-items-center tw-flex-1 tw-min-w-0 tw-gap-1">
                                        <div class="tw-px-2">
                                            <h3 class="tw-font-bold tw-text-lg">
                                                {{ __('home.stock_expiry_alert') }}
                                                @show_tooltip(
                                                __('tooltip.stock_expiry_alert', [
                                                'days'
                                                =>session('business.stock_expiry_alert_days', 30) ]) )
                                            </h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="tw-flow-root tw-mt-5  tw-border-gray-200">
                                    <div class="tw--mx-4 tw--my-2 tw-overflow-x-auto sm:tw--mx-5">
                                        <div class="tw-inline-block tw-min-w-full tw-py-2 tw-align-middle sm:tw-px-5">
                                            <input type="hidden" id="stock_expiry_alert_days"
                                                value="{{ Carbon::now()->addDays((int)session('business.stock_expiry_alert_days', 30))->format('Y-m-d') }}">
                                            <table class="table table-striped" id="stock_expiry_alert_table" style="width: 100%;">
                                                <thead class="tw-text-white tw-bg-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-800">
                                                    <tr>
                                                        <th>@lang('business.product')</th>
                                                        <th>@lang('business.location')</th>
                                                        <th>@lang('report.stock_left')</th>
                                                        <th>@lang('product.expires_in')</th>
                                                    </tr>
                                                </thead>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endcan
                @if (auth()->user()->can('so.view_all') || auth()->user()->can('so.view_own'))
                    <div
                        class="tw-transition-all lg:tw-col-span-2 tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-xl tw-ring-1 hover:tw-shadow-md hover:tw--translate-y-0.5 tw-ring-gray-200">
                        <div class="tw-p-4 sm:tw-p-5">
                            <div class="tw-flex tw-items-center">
                                <div
                                    class="tw-bg-yellow-100 tw-flex tw-items-center tw-justify-center tw-rounded-full tw-p-2">
                                    <svg  xmlns="http://www.w3.org/2000/svg" class="tw-size-5 tw-text-yellow-500" viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-folder-dollar"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M13.5 19h-8.5a2 2 0 0 1 -2 -2v-11a2 2 0 0 1 2 -2h4l3 3h7a2 2 0 0 1 2 2v1.5" /><path d="M21 15h-2.5a1.5 1.5 0 0 0 0 3h1a1.5 1.5 0 0 1 0 3h-2.5" /><path d="M19 21v1m0 -8v1" /></svg>
                                </div>
                                <div class="tw-px-2">
                                    <h3 class="tw-font-bold tw-text-lg">
                                        {{ __('lang_v1.sales_order') }}
                                    </h3>
                                </div>
                            </div>
                            <!-- FILTER DROPDOWN Placeholder (used by DataTables DOM) -->
                            <div id="sales_order_lf" class="tw-hidden" style="width:14rem">
                                @if (count($all_locations) > 1)
                                    {!! Form::select('so_location', $all_locations, null, [
                                        'class' => 'form-control select2',
                                        'placeholder' => __('lang_v1.select_location'),
                                        'id' => 'so_location',
                                    ]) !!}
                                @endif
                            </div>
                            <div class="tw-flow-root tw-mt-5  tw-border-gray-200">
                                <div class="tw--mx-4 tw--my-2 tw-overflow-x-auto sm:tw--mx-5">
                                    <div class="tw-inline-block tw-min-w-full tw-py-2 tw-align-middle sm:tw-px-5">
                                        <table class="table table-striped ajax_view"
                                            id="sales_order_table" style="width: 100%;">
                                            <thead class="tw-text-white tw-bg-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-800">
                                                <tr>
                                                    <th>@lang('messages.action')</th>
                                                    <th>@lang('messages.date')</th>
                                                    <th>@lang('restaurant.order_no')</th>
                                                    <th>@lang('sale.customer_name')</th>
                                                    <th>@lang('lang_v1.contact_no')</th>
                                                    <th>@lang('sale.location')</th>
                                                    <th>@lang('sale.status')</th>
                                                    <th>@lang('lang_v1.shipping_status')</th>
                                                    <th>@lang('lang_v1.quantity_remaining')</th>
                                                    <th>@lang('lang_v1.added_by')</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                @if (
                    !empty($common_settings['enable_purchase_requisition']) &&
                        (auth()->user()->can('purchase_requisition.view_all') || auth()->user()->can('purchase_requisition.view_own')))
                    <div
                        class="tw-transition-all lg:tw-col-span-2 tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-xl tw-ring-1 hover:tw-shadow-md hover:tw--translate-y-0.5 tw-ring-gray-200">
                        <div class="tw-p-4 sm:tw-p-5">
                            <div class="tw-flex tw-items-center">
                                <div
                                    class="tw-bg-yellow-100 tw-flex tw-items-center tw-justify-center tw-rounded-full tw-p-2">
                                    <svg aria-hidden="true" class="tw-text-yellow-500 tw-size-5 tw-shrink-0"
                                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2"
                                        stroke="currentColor" fill="none" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                        <path d="M10 10v4a1 1 0 0 0 1 1h2a1 1 0 0 0 1 -1v-4"></path>
                                        <path d="M9 6h6"></path>
                                        <path d="M10 6v-2a1 1 0 0 1 1 -1h2a1 1 0 0 1 1 1v2"></path>
                                        <circle cx="12" cy="16" r="2"></circle>
                                        <path d="M5 20h14a2 2 0 0 0 2 -2v-10"></path>
                                        <path d="M15 16v4"></path>
                                        <path d="M9 20v-4"></path>
                                    </svg>
                                </div>
                                <div class="tw-px-2">
                                    <h3 class="tw-font-bold tw-text-lg">
                                        @lang('lang_v1.purchase_requisition')
                                    </h3>
                                </div>
                            </div>
                            <!-- FILTER DROPDOWN Placeholder (used by DataTables DOM) -->
                            <div id="purchase_requisition_lf" class="tw-hidden" style="width:14rem">
                                @if (count($all_locations) > 1)
                                    @if (count($all_locations) > 1)
                                        {!! Form::select('pr_location', $all_locations, null, [
                                            'class' => 'form-control select2',
                                            'placeholder' => __('lang_v1.select_location'),
                                            'id' => 'pr_location',
                                        ]) !!}
                                    @endif
                                @endif
                            </div>
                            <div class="tw-flow-root tw-mt-5  tw-border-gray-200">
                                <div class="tw--mx-4 tw--my-2 tw-overflow-x-auto sm:tw--mx-5">
                                    <div class="tw-inline-block tw-min-w-full tw-py-2 tw-align-middle sm:tw-px-5">
                                        <table class="table table-striped ajax_view"
                                            id="purchase_requisition_table" style="width: 100%;">
                                            <thead class="tw-text-white tw-bg-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-800">
                                                <tr>
                                                    <th>@lang('messages.action')</th>
                                                    <th>@lang('messages.date')</th>
                                                    <th>@lang('purchase.ref_no')</th>
                                                    <th>@lang('purchase.location')</th>
                                                    <th>@lang('sale.status')</th>
                                                    <th>@lang('lang_v1.required_by_date')</th>
                                                    <th>@lang('lang_v1.added_by')</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if (
                    !empty($common_settings['enable_purchase_order']) &&
                        (auth()->user()->can('purchase_order.view_all') || auth()->user()->can('purchase_order.view_own')))

                    <div
                        class="tw-transition-all lg:tw-col-span-2 tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-xl tw-ring-1 hover:tw-shadow-md hover:tw--translate-y-0.5 tw-ring-gray-200">
                        <div class="tw-p-4 sm:tw-p-5">
                            <div class="tw-flex tw-items-center">
                                <div
                                    class="tw-bg-yellow-100 tw-flex tw-items-center tw-justify-center tw-rounded-full tw-p-2">
                                    <svg  xmlns="http://www.w3.org/2000/svg" class="tw-text-yellow-500 tw-size-5" viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-shopping-bag-check"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M11.5 21h-2.926a3 3 0 0 1 -2.965 -2.544l-1.255 -8.152a2 2 0 0 1 1.977 -2.304h11.339a2 2 0 0 1 1.977 2.304l-.5 3.248" /><path d="M9 11v-5a3 3 0 0 1 6 0v5" /><path d="M15 19l2 2l4 -4" /></svg>
                                </div>
                                <div class="tw-px-2">
                                    <h3 class="tw-font-bold tw-text-lg">
                                        @lang('lang_v1.purchase_order')
                                    </h3>
                                </div>
                            </div>
                            <!-- FILTER DROPDOWN Placeholder (used by DataTables DOM) -->
                             <div id="purchase_order_lf" class="tw-hidden" style="width:14rem">
                                @if (count($all_locations) > 1)
                                    {!! Form::select('po_location', $all_locations, null, [
                                        'class' => 'form-control select2',
                                        'placeholder' => __('lang_v1.select_location'),
                                        'id' => 'po_location',
                                    ]) !!}
                                @endif
                            </div>
                            <div class="tw-flow-root tw-mt-5  tw-border-gray-200">
                                <div class="tw--mx-4 tw--my-2 tw-overflow-x-auto sm:tw--mx-5">
                                    <div class="tw-inline-block tw-min-w-full tw-py-2 tw-align-middle sm:tw-px-5">
                                        <table class="table table-striped ajax_view"
                                            id="purchase_order_table" style="width: 100%;">
                                            <thead class="tw-text-white tw-bg-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-800">
                                                <tr>
                                                    <th>@lang('messages.action')</th>
                                                    <th>@lang('messages.date')</th>
                                                    <th>@lang('purchase.ref_no')</th>
                                                    <th>@lang('purchase.location')</th>
                                                    <th>@lang('purchase.supplier')</th>
                                                    <th>@lang('sale.status')</th>
                                                    <th>@lang('lang_v1.quantity_remaining')</th>
                                                    <th>@lang('lang_v1.added_by')</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                @endif
                @if (auth()->user()->can('access_pending_shipments_only') ||
                        auth()->user()->can('access_shipping') ||
                        auth()->user()->can('access_own_shipping'))
                    <div
                        class="tw-transition-all lg:tw-col-span-2 tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-xl tw-ring-1 hover:tw-shadow-md hover:tw--translate-y-0.5 tw-ring-gray-200">
                        <div class="tw-p-4 sm:tw-p-5">
                            <div class="tw-flex tw-items-center">
                                <div
                                    class="tw-bg-yellow-100 tw-flex tw-items-center tw-justify-center tw-rounded-full tw-p-2">
                                    <svg aria-hidden="true" class="tw-text-yellow-500 tw-size-5 tw-shrink-0"
                                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2"
                                        stroke="currentColor" fill="none" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                        <path d="M7 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"></path>
                                        <path d="M17 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"></path>
                                        <path d="M5 17h-2v-4m-1 -8h11v12m-4 0h6m4 0h2v-6h-8m0 -5h5l3 5"></path>
                                        <path d="M3 9l4 0"></path>
                                    </svg>
                                    
                                </div>
                                <div class="tw-px-2">
                                    <h3 class="tw-font-bold tw-text-lg">
                                        @lang('lang_v1.pending_shipments')
                                    </h3>
                                </div>
                                    
                            </div>
                            <!-- FILTER DROPDOWN Placeholder (used by DataTables DOM) -->
                            <div id="pending_shipments_lf" class="tw-hidden" style="width:14rem">
                                @if (count($all_locations) > 1)
                                    {!! Form::select('pending_shipments_location', $all_locations, null, [
                                        'class' => 'form-control select2 ',
                                        'placeholder' => __('lang_v1.select_location'),
                                        'id' => 'pending_shipments_location',
                                    ]) !!}
                                @endif
                            </div>
                            <div class="tw-flow-root tw-mt-5  tw-border-gray-200">
                                <div class="tw--mx-4 tw--my-2 tw-overflow-x-auto sm:tw--mx-5">
                                    <div class="tw-inline-block tw-min-w-full tw-py-2 tw-align-middle sm:tw-px-5">
                                        <table class="table table-striped ajax_view" id="shipments_table" style="width: 100%;">
                                            <thead class="tw-text-white tw-bg-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-800">
                                                <tr>
                                                    <th>@lang('messages.action')</th>
                                                    <th>@lang('messages.date')</th>
                                                    <th>@lang('sale.invoice_no')</th>
                                                    <th>@lang('sale.customer_name')</th>
                                                    <th>@lang('lang_v1.contact_no')</th>
                                                    <th>@lang('sale.location')</th>
                                                    <th>@lang('lang_v1.shipping_status')</th>
                                                    @if (!empty($custom_labels['shipping']['custom_field_1']))
                                                        <th>
                                                            {{ $custom_labels['shipping']['custom_field_1'] }}
                                                        </th>
                                                    @endif
                                                    @if (!empty($custom_labels['shipping']['custom_field_2']))
                                                        <th>
                                                            {{ $custom_labels['shipping']['custom_field_2'] }}
                                                        </th>
                                                    @endif
                                                    @if (!empty($custom_labels['shipping']['custom_field_3']))
                                                        <th>
                                                            {{ $custom_labels['shipping']['custom_field_3'] }}
                                                        </th>
                                                    @endif
                                                    @if (!empty($custom_labels['shipping']['custom_field_4']))
                                                        <th>
                                                            {{ $custom_labels['shipping']['custom_field_4'] }}
                                                        </th>
                                                    @endif
                                                    @if (!empty($custom_labels['shipping']['custom_field_5']))
                                                        <th>
                                                            {{ $custom_labels['shipping']['custom_field_5'] }}
                                                        </th>
                                                    @endif
                                                    <th>@lang('sale.payment_status')</th>
                                                    <th>@lang('restaurant.service_staff')</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                @if (auth()->user()->can('account.access') && config('constants.show_payments_recovered_today') == true)
                    <div
                        class="tw-transition-all lg:tw-col-span-2 tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-xl tw-ring-1 hover:tw-shadow-md hover:tw--translate-y-0.5 tw-ring-gray-200">
                        <div class="tw-p-4 sm:tw-p-5">
                            <div class="tw-flex tw-items-center">
                                <div
                                    class="tw-bg-yellow-100 tw-flex tw-items-center tw-justify-center tw-rounded-full tw-p-2">
                                    <svg  xmlns="http://www.w3.org/2000/svg" class="tw-size-5 tw-text-yellow-500" viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-receipt-dollar"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 21v-16a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v16l-3 -2l-2 2l-2 -2l-2 2l-2 -2l-3 2" /><path d="M14.8 8a2 2 0 0 0 -1.8 -1h-2a2 2 0 1 0 0 4h2a2 2 0 1 1 0 4h-2a2 2 0 0 1 -1.8 -1" /><path d="M12 6v10" /></svg>
                                </div>
                                <div class="tw-px-2">
                                    <h3 class="tw-font-bold tw-text-base lg:tw-text-xl">
                                        @lang('lang_v1.payment_recovered_today')
                                    </h3>
                                </div>
                               
                            </div>
                            <div class="tw-flow-root tw-mt-5  tw-border-gray-200">
                                <div class="tw--mx-4 tw--my-2 tw-overflow-x-auto sm:tw--mx-5">
                                    <div class="tw-inline-block tw-min-w-full tw-py-2 tw-align-middle sm:tw-px-5">
                                        <table class="table table-striped tw-rounded-lg tw-overflow-hidden tw-border" id="cash_flow_table">
                                            <thead class="tw-text-white tw-bg-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-800">
                                                <tr>
                                                    <th>@lang('messages.date')</th>
                                                    <th>@lang('account.account')</th>
                                                    <th>@lang('lang_v1.description')</th>
                                                    <th>@lang('lang_v1.payment_method')</th>
                                                    <th>@lang('lang_v1.payment_details')</th>
                                                    <th>@lang('account.credit')</th>
                                                    <th>@lang('lang_v1.account_balance')
                                                        @show_tooltip(__('lang_v1.account_balance_tooltip'))</th>
                                                    <th>@lang('lang_v1.total_balance')
                                                        @show_tooltip(__('lang_v1.total_balance_tooltip'))</th>
                                                </tr>
                                            </thead>
                                        <tfoot style="background-color: #e9ecef;">
                                                <tr class=" footer-total text-center" style="background-color:oklch(97% 0.014 254.604);">
                                                    <td colspan="5"><strong>@lang('sale.total'):</strong></td>
                                                    <td class="footer_total_credit"></td>
                                                    <td colspan="2"></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                {{-- @if (!empty($widgets['after_dashboard_reports']))
                    @foreach ($widgets['after_dashboard_reports'] as $widget)
                        {!! $widget !!}
                    @endforeach
                @endif --}}
            </div>
        </div>
    @endif

@endsection


<div class="modal fade payment_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
</div>
<div class="modal fade edit_pso_status_modal" tabindex="-1" role="dialog"></div>
<div class="modal fade edit_payment_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
</div>

@section('css')
    <style>
       .white-tooltip i {
            color: white !important;
       }
       .white-tooltip .smallText{
            color : #ddd;
       }
       .newBg{
        background-color : {{ $bgColor }};
       }
    </style>
@endsection

@section('javascript')
    <script src="{{ asset('js/home.js?v=' . $asset_v) }}"></script>
    <script src="{{ asset('js/payment.js?v=' . $asset_v) }}"></script>
    @includeIf('sales_order.common_js')
    @includeIf('purchase_order.common_js')
    @if (!empty($all_locations))
        {!! $sells_chart_1->script() !!}
        {!! $sells_chart_2->script() !!}
    @endif
    <script type="text/javascript">
        $(document).ready(function() {

            // custom chart section start
            const colors = [
            'rgba(255, 99, 132, 1)',    // red
            'rgba(54, 162, 235, 1)',    // blue
            'rgba(255, 206, 86, 1)',    // yellow
            'rgba(75, 192, 192, 1)',    // green
            'rgba(153, 102, 255, 1)',   // purple
            'rgba(255, 159, 64, 1)'     // orange
            ];

            const pointStyles = [
            'triangle',
            'circle',
            'rect',
            'rectRounded',
            'rectRot',
            'star',
            'crossRot'
            ];
            // Pass PHP variables to JS safely as JSON
            const sellsChartLabels1 = {!! json_encode($sells_chart_1_labels) !!};
            const sellsChartDatasetsRaw1 = {!! json_encode($sells_chart_1_datasets) !!};
            const sellsChartOptionsRaw1 = {!! json_encode($sells_chart_1_options) !!};
            const sellsChartLabels2 = {!! json_encode($sells_chart_2_labels) !!};
            const sellsChartDatasetsRaw2 = {!! json_encode($sells_chart_2_datasets) !!};
            const sellsChartOptionsRaw2 = {!! json_encode($sells_chart_2_options) !!};

            // Transform the datasets to Chart.js format
            const sellsChartDatasets1 = sellsChartDatasetsRaw1.map((ds, index) => ({
                    label: ds.name,
                    data: ds.data,
                    borderColor: colors[index % colors.length],
                    backgroundColor:  colors[index % colors.length], // lighter transparent fill
                    fill: false,
                    tension: 0.3,
                    pointStyle: pointStyles[index % pointStyles.length],
                    borderWidth : 2,
                    hoverBorderWidth: 3,
                    pointHoverBorderWidth: 1,
                    pointRadius: 4,
                    pointHoverRadius: 5,
                }));
            const sellsChartDatasets2 = sellsChartDatasetsRaw2.map((ds, index) => ({
                    label: ds.name,
                    data: ds.data,
                    borderColor: colors[index % colors.length],
                    backgroundColor:  colors[index % colors.length], // lighter transparent fill
                    fill: false,
                    tension: 0.3,
                    pointStyle: pointStyles[index % pointStyles.length],
                    borderWidth : 2,
                    hoverBorderWidth: 3,
                    pointHoverBorderWidth: 1,
                    pointRadius: 4,
                    pointHoverRadius: 5,
                }));

            // Merge your PHP options with Chart.js options or fallback defaults
            const sellsChartOptions1 = {
                responsive: true,
                plugins: {
                    legend: {
                        display: sellsChartOptionsRaw1.legend.floating,
                        position: sellsChartOptionsRaw1.legend.verticalAlign, 
                        align: sellsChartOptionsRaw1.legend.align,
                        labels: {
                            usePointStyle: true,  // use the pointStyle icons in legend
                            padding: 20,          // space between legend items
                            color: '#333',        // legend text color
                            font: {
                                size: 12,
                                weight: 'normal',
                                family : 'sans-serif',
                            }
                        }
                    },
                    tooltip:{
                        usePointStyle: true, // use the pointStyle icons in tooltip
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        title : {
                            display: true,
                            padding: 20, 
                            text: sellsChartOptionsRaw1.yAxis.title.text,
                            font: {
                                size: 12,
                                weight: 'normal',
                                family : 'sans-serif',
                            }
                        },
                        // grid: {
                        //     display: false,  // removes vertical grid lines
                        //     drawBorder: false // removes bottom border line
                        // }
                    },
                    // x: {
                    //     grid: {
                    //         display: false,  // removes horizontal grid lines
                    //         drawBorder: false // removes left border line
                    //     }
                    // }
                }
            };
            const sellsChartOptions2 = {
                responsive: true,
                plugins: {
                    legend: {
                        display: sellsChartOptionsRaw2.legend.floating,
                        position: sellsChartOptionsRaw2.legend.verticalAlign, 
                        align: sellsChartOptionsRaw2.legend.align,
                        labels: {
                            usePointStyle: true,  // use the pointStyle icons in legend
                            padding: 20,          // space between legend items
                            color: '#333',        // legend text color
                            font: {
                                size: 12,
                                weight: 'normal',
                                family : 'sans-serif',
                            }
                        }
                    },
                    tooltip:{
                        usePointStyle: true, // use the pointStyle icons in tooltip
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        title : {
                            display: true,
                            padding: 20, 
                            text: sellsChartOptionsRaw2.yAxis.title.text,
                            font: {
                                size: 12,
                                weight: 'normal',
                                family : 'sans-serif',
                            }
                        }
                    }
                }
            };
            
            // Get canvas context by ID (make sure your canvas has id="sellsChart2")
            const ctx1 = document.getElementById('sellsChart1').getContext('2d');
            ctx1.canvas.width = 600;
            ctx1.canvas.height = 300;
            const ctx2 = document.getElementById('sellsChart2').getContext('2d');
            ctx2.canvas.width = 600;
            ctx2.canvas.height = 300;

            

            // Initialize the Chart.js chart
            const sellsChart1 = new Chart(ctx1, {
                type: 'line',
                data: {
                    labels: sellsChartLabels1,
                    datasets: sellsChartDatasets1
                },
                options: sellsChartOptions1,
            });
            const sellsChart2 = new Chart(ctx2, {
                type: 'line',
                data: {
                    labels: sellsChartLabels2,
                    datasets: sellsChartDatasets2
                },
                options: sellsChartOptions2,
            });

            let resizeTimeout;
            const container = document.getElementById('chartContainer');
            const resizeObserver = new ResizeObserver(() => {
                clearTimeout(resizeTimeout);
                resizeTimeout = setTimeout(() => {
                    sellsChart1.resize();
                    sellsChart2.resize();
                }, 100); // Delay in ms – tweak as needed
            });

            resizeObserver.observe(container);
            // custom charts section end

            sales_order_table = $('#sales_order_table').DataTable({
                processing: true,
                serverSide: true,
                fixedHeader:false,
                scrollY: "75vh",
                scrollX: true,
                scrollCollapse: true,
                aaSorting: [
                    [1, 'desc']
                ],
                // dom: '<"tw-mb-4 tw-flex tw-flex-wrap tw-justify-between tw-items-center tw-gap-2"lBf>rt<"tw-mt-4 tw-flex tw-justify-between tw-items-center"ip>',
                dom: `
                    <"dt-section-toolbar tw-mb-4 tw-flex tw-flex-wrap tw-justify-between tw-items-center tw-gap-2"
                        <"drps-section tw-flex tw-gap-2 tw-items-center"
                            <"so tw-flex tw-items-center tw-gap-2"B>
                            l
                        >
                        f
                    >
                    rt
                    <"tw-mt-4 tw-flex tw-justify-between tw-items-center"ip>
                `,
                initComplete: function () {
                    // first inject the class into toolbar div then make it show
                    const $filter = $('#sales_order_lf');
                    const $container = $('.so');

                    $container.append($filter);
                    // If you want to ensure it becomes visible + flex
                    $filter.removeClass('tw-hidden').addClass('tw-flex');
                },
                "ajax": {
                    "url": '{{ action([\App\Http\Controllers\SellController::class, 'index']) }}?sale_type=sales_order',
                    "data": function(d) {
                        d.for_dashboard_sales_order = true;

                        if ($('#so_location').length > 0) {
                            d.location_id = $('#so_location').val();
                        }
                    }
                },
                columnDefs: [{
                    "targets": 7,
                    "orderable": false,
                    "searchable": false
                }],
                columns: [{
                        data: 'action',
                        name: 'action'
                    },
                    {
                        data: 'transaction_date',
                        name: 'transaction_date'
                    },
                    {
                        data: 'invoice_no',
                        name: 'invoice_no'
                    },
                    {
                        data: 'conatct_name',
                        name: 'conatct_name'
                    },
                    {
                        data: 'mobile',
                        name: 'contacts.mobile'
                    },
                    {
                        data: 'business_location',
                        name: 'bl.name'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'shipping_status',
                        name: 'shipping_status'
                    },
                    {
                        data: 'so_qty_remaining',
                        name: 'so_qty_remaining',
                        "searchable": false
                    },
                    {
                        data: 'added_by',
                        name: 'u.first_name'
                    },
                ]
            });

            

            @if (auth()->user()->can('account.access') && config('constants.show_payments_recovered_today') == true)

                // Cash Flow Table
                cash_flow_table = $('#cash_flow_table').DataTable({
                    processing: true,
                    serverSide: true,
                    fixedHeader:false,
                    dom: '<"tw-mb-4 tw-flex tw-items-center tw-gap-2"Bl>rt<"tw-mt-4 tw-flex tw-justify-between tw-items-center"ip>',
                    "ajax": {
                        "url": "{{ action([\App\Http\Controllers\AccountController::class, 'cashFlow']) }}",
                        "data": function(d) {
                            d.type = 'credit';
                            d.only_payment_recovered = true;
                        }
                    },
                    "ordering": false,
                    "searching": false,
                    columns: [{
                            data: 'operation_date',
                            name: 'operation_date'
                        },
                        {
                            data: 'account_name',
                            name: 'account_name'
                        },
                        {
                            data: 'sub_type',
                            name: 'sub_type'
                        },
                        {
                            data: 'method',
                            name: 'TP.method'
                        },
                        {
                            data: 'payment_details',
                            name: 'payment_details',
                            searchable: false
                        },
                        {
                            data: 'credit',
                            name: 'amount'
                        },
                        {
                            data: 'balance',
                            name: 'balance'
                        },
                        {
                            data: 'total_balance',
                            name: 'total_balance'
                        },
                    ],
                    "fnDrawCallback": function(oSettings) {
                        __currency_convert_recursively($('#cash_flow_table'));
                    },
                    "footerCallback": function(row, data, start, end, display) {
                        var footer_total_credit = 0;

                        for (var r in data) {
                            footer_total_credit += $(data[r].credit).data('orig-value') ? parseFloat($(
                                data[r].credit).data('orig-value')) : 0;
                        }
                        $('.footer_total_credit').html(__currency_trans_from_en(footer_total_credit));
                    }
                });
            @endif

            $('#so_location').change(function() {
                sales_order_table.ajax.reload();
            });
            @if (!empty($common_settings['enable_purchase_order']))
                //Purchase table
                purchase_order_table = $('#purchase_order_table').DataTable({
                    processing: true,
                    serverSide: true,
                    fixedHeader:false,
                    aaSorting: [
                        [1, 'desc']
                    ],
                    scrollY: "75vh",
                    scrollX: true,
                    scrollCollapse: true,
                    dom: `
                        <"dt-section-toolbar tw-mb-4 tw-flex tw-flex-wrap tw-justify-between tw-items-center tw-gap-2"
                            <"drps-section tw-flex tw-gap-2 tw-items-center"
                                <"pot tw-flex tw-items-center tw-gap-2"B>
                                l
                            >
                            f
                        >
                        rt
                        <"tw-mt-4 tw-flex tw-justify-between tw-items-center"ip>
                    `,
                    initComplete: function () {
                        // first inject the class into toolbar div then make it show
                        const $filter = $('#purchase_order_lf');
                        const $container = $('.pot');

                        $container.append($filter);
                        // If you want to ensure it becomes visible + flex
                        $filter.removeClass('tw-hidden').addClass('tw-flex');
                    },
                    ajax: {
                        url: '{{ action([\App\Http\Controllers\PurchaseOrderController::class, 'index']) }}',
                        data: function(d) {
                            d.from_dashboard = true;

                            if ($('#po_location').length > 0) {
                                d.location_id = $('#po_location').val();
                            }
                        },
                    },
                    columns: [{
                            data: 'action',
                            name: 'action',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'transaction_date',
                            name: 'transaction_date'
                        },
                        {
                            data: 'ref_no',
                            name: 'ref_no'
                        },
                        {
                            data: 'location_name',
                            name: 'BS.name'
                        },
                        {
                            data: 'name',
                            name: 'contacts.name'
                        },
                        {
                            data: 'status',
                            name: 'transactions.status'
                        },
                        {
                            data: 'po_qty_remaining',
                            name: 'po_qty_remaining',
                            "searchable": false
                        },
                        {
                            data: 'added_by',
                            name: 'u.first_name'
                        }
                    ]
                })

                $('#po_location').change(function() {
                    purchase_order_table.ajax.reload();
                });
            @endif

            @if (!empty($common_settings['enable_purchase_requisition']))
                //Purchase table
                purchase_requisition_table = $('#purchase_requisition_table').DataTable({
                    processing: true,
                    serverSide: true,
                    fixedHeader:false,
                    aaSorting: [
                        [1, 'desc']
                    ],
                    scrollY: "75vh",
                    scrollX: true,
                    scrollCollapse: true,
                    dom: `
                        <"dt-section-toolbar tw-mb-4 tw-flex tw-flex-wrap tw-justify-between tw-items-center tw-gap-2"
                            <"drps-section tw-flex tw-gap-2 tw-items-center"
                                <"prt tw-flex tw-items-center tw-gap-2"B>
                                l
                            >
                            f
                        >
                        rt
                        <"tw-mt-4 tw-flex tw-justify-between tw-items-center"ip>
                    `,
                    initComplete: function () {
                        // first inject the class into toolbar div then make it show
                        const $filter = $('#purchase_requisition_lf');
                        const $container = $('.prt');

                        $container.append($filter);
                        // If you want to ensure it becomes visible + flex
                        $filter.removeClass('tw-hidden').addClass('tw-flex');
                    },
                    ajax: {
                        url: '{{ action([\App\Http\Controllers\PurchaseRequisitionController::class, 'index']) }}',
                        data: function(d) {
                            d.from_dashboard = true;

                            if ($('#pr_location').length > 0) {
                                d.location_id = $('#pr_location').val();
                            }
                        },
                    },
                    columns: [{
                            data: 'action',
                            name: 'action',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'transaction_date',
                            name: 'transaction_date'
                        },
                        {
                            data: 'ref_no',
                            name: 'ref_no'
                        },
                        {
                            data: 'location_name',
                            name: 'BS.name'
                        },
                        {
                            data: 'status',
                            name: 'status'
                        },
                        {
                            data: 'delivery_date',
                            name: 'delivery_date'
                        },
                        {
                            data: 'added_by',
                            name: 'u.first_name'
                        },
                    ]
                })

                $('#pr_location').change(function() {
                    purchase_requisition_table.ajax.reload();
                });

                $(document).on('click', 'a.delete-purchase-requisition', function(e) {
                    e.preventDefault();
                    swal({
                        title: LANG.sure,
                        icon: 'warning',
                        buttons: true,
                        dangerMode: true,
                    }).then(willDelete => {
                        if (willDelete) {
                            var href = $(this).attr('href');
                            $.ajax({
                                method: 'DELETE',
                                url: href,
                                dataType: 'json',
                                success: function(result) {
                                    if (result.success == true) {
                                        toastr.success(result.msg);
                                        purchase_requisition_table.ajax.reload();
                                    } else {
                                        toastr.error(result.msg);
                                    }
                                },
                            });
                        }
                    });
                });
            @endif

            sell_table = $('#shipments_table').DataTable({
                processing: true,
                serverSide: true,
                fixedHeader:false,
                aaSorting: [
                    [1, 'desc']
                ],
                scrollY: "75vh",
                scrollX: true,
                scrollCollapse: true,
                // dom: '<"tw-mb-4 tw-flex tw-flex-wrap tw-justify-between tw-items-center tw-gap-2"lBf>rt<"tw-mt-4 tw-flex tw-justify-between tw-items-center"ip>',
                dom: `
                    <"dt-section-toolbar tw-mb-6 tw-flex tw-flex-wrap tw-justify-between tw-items-center tw-gap-2"
                        <"drps-section tw-flex tw-gap-2 tw-items-center"
                            <"pst tw-flex tw-items-center tw-gap-2"B>
                            l
                        >
                        f
                    >
                    rt
                    <"tw-mt-4 tw-flex tw-justify-between tw-items-center"ip>
                `,
                initComplete: function () {
                    // first inject the class into toolbar div then make it show
                    const $filter = $('#pending_shipments_lf');
                    const $container = $('.pst');

                    $container.append($filter);
                    // If you want to ensure it becomes visible + flex
                    $filter.removeClass('tw-hidden').addClass('tw-flex');
                },
                "ajax": {
                    "url": '{{ action([\App\Http\Controllers\SellController::class, 'index']) }}',
                    "data": function(d) {
                        d.only_pending_shipments = true;
                        if ($('#pending_shipments_location').length > 0) {
                            d.location_id = $('#pending_shipments_location').val();
                        }
                    }
                },
                columns: [{
                        data: 'action',
                        name: 'action',
                        searchable: false,
                        orderable: false
                    },
                    {
                        data: 'transaction_date',
                        name: 'transaction_date'
                    },
                    {
                        data: 'invoice_no',
                        name: 'invoice_no'
                    },
                    {
                        data: 'conatct_name',
                        name: 'conatct_name'
                    },
                    {
                        data: 'mobile',
                        name: 'contacts.mobile'
                    },
                    {
                        data: 'business_location',
                        name: 'bl.name'
                    },
                    {
                        data: 'shipping_status',
                        name: 'shipping_status'
                    },
                    @if (!empty($custom_labels['shipping']['custom_field_1']))
                        {
                            data: 'shipping_custom_field_1',
                            name: 'shipping_custom_field_1'
                        },
                    @endif
                    @if (!empty($custom_labels['shipping']['custom_field_2']))
                        {
                            data: 'shipping_custom_field_2',
                            name: 'shipping_custom_field_2'
                        },
                    @endif
                    @if (!empty($custom_labels['shipping']['custom_field_3']))
                        {
                            data: 'shipping_custom_field_3',
                            name: 'shipping_custom_field_3'
                        },
                    @endif
                    @if (!empty($custom_labels['shipping']['custom_field_4']))
                        {
                            data: 'shipping_custom_field_4',
                            name: 'shipping_custom_field_4'
                        },
                    @endif
                    @if (!empty($custom_labels['shipping']['custom_field_5']))
                        {
                            data: 'shipping_custom_field_5',
                            name: 'shipping_custom_field_5'
                        },
                    @endif {
                        data: 'payment_status',
                        name: 'payment_status'
                    },
                    {
                        data: 'waiter',
                        name: 'ss.first_name',
                        @if (empty($is_service_staff_enabled))
                            visible: false
                        @endif
                    }
                ],
                "fnDrawCallback": function(oSettings) {
                    __currency_convert_recursively($('#sell_table'));
                },
                createdRow: function(row, data, dataIndex) {
                    $(row).find('td:eq(4)').attr('class', 'clickable_td');
                }
            });

            $('#pending_shipments_location').change(function() {
                sell_table.ajax.reload();
            });
        });
    </script>
    
@endsection
