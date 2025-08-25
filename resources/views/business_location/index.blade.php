@extends('layouts.app')
@section('title', __('business.business_locations'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang( 'business.business_locations' )
        <small class="tw-text-sm md:tw-text-base tw-text-gray-700 tw-font-semibold">@lang( 'business.manage_your_business_locations' )</small>
    </h1>
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
</section>

<!-- Main content -->
<section class="content">
    @component('components.widget', ['class' => 'box-primary', 'title' => __( 'business.all_your_business_locations' )])
        @slot('tool')
            <div class="box-tools">
                <button class="add-btn tw-gap-2 pull-right btn-modal"
                    data-href="{{action([\App\Http\Controllers\BusinessLocationController::class, 'create'])}}" 
                    data-container=".location_add_modal">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="3" stroke-linecap="round" stroke-linejoin="round"
                        class="icon icon-tabler icons-tabler-outline icon-tabler-plus">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M12 5l0 14" />
                        <path d="M5 12l14 0" />
                    </svg> @lang('messages.add')
                </button>
            </div>
        @endslot
        <div class="table-responsive">
            <table class="table tw-border table-striped" id="business_location_table">
                <thead class="tw-text-white tw-bg-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-800">
                    <tr>
                        <th>@lang( 'invoice.name' )</th>
                        <th>@lang( 'lang_v1.location_id' )</th>
                        <th>@lang( 'business.landmark' )</th>
                        <th>@lang( 'business.city' )</th>
                        <th>@lang( 'business.zip_code' )</th>
                        <th>@lang( 'business.state' )</th>
                        <th>@lang( 'business.country' )</th>
                        <th>@lang( 'lang_v1.price_group' )</th>
                        <th>@lang( 'invoice.invoice_scheme' )</th>
                        <th>@lang('lang_v1.invoice_layout_for_pos')</th>
                        <th>@lang('lang_v1.invoice_layout_for_sale')</th>
                        <th>@lang( 'messages.action' )</th>
                    </tr>
                </thead>
            </table>
        </div>
    @endcomponent

    <div class="modal fade location_add_modal" tabindex="-1" role="dialog" 
    	aria-labelledby="gridSystemModalLabel">
    </div>
    <div class="modal fade location_edit_modal" tabindex="-1" role="dialog" 
        aria-labelledby="gridSystemModalLabel">
    </div>

</section>
<!-- /.content -->

@endsection
