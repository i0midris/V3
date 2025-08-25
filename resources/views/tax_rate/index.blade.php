@extends('layouts.app')
@section('title', __( 'tax_rate.tax_rates' ))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang( 'tax_rate.tax_rates' )
        <small class="tw-text-sm md:tw-text-base tw-text-gray-700 tw-font-semibold">@lang( 'tax_rate.manage_your_tax_rates' )</small>
    </h1>
</section>

<!-- Main content -->
<section class="content">
    @component('components.widget', ['class' => 'box-primary', 'title' => __( 'tax_rate.all_your_tax_rates' )])
        @can('tax_rate.create')
            @slot('tool')
                <div class="box-tools">
                    <button class="add-btn tw-gap-1 btn-modal pull-right"
                        data-href="{{action([\App\Http\Controllers\TaxRateController::class, 'create'])}}" 
                        data-container=".tax_rate_modal">
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
        @endcan
        @can('tax_rate.view')
            <div class="table-responsive">
                <table class="table tw-border table-striped" id="tax_rates_table">
                    <thead class="tw-text-white tw-bg-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-800">
                        <tr>
                            <th>@lang( 'tax_rate.name' )</th>
                            <th>@lang( 'tax_rate.rate' )</th>
                            <th>@lang( 'messages.action' )</th>
                        </tr>
                    </thead>
                </table>
            </div>
        @endcan
    @endcomponent

    @component('components.widget', ['class' => 'box-primary'])
        @slot('title')
            @lang( 'tax_rate.tax_groups' ) ( @lang('lang_v1.combination_of_taxes') ) @show_tooltip(__('tooltip.tax_groups'))
        @endslot
        @can('tax_rate.create')
            @slot('tool')
                <div class="box-tools">
                    {{-- <button type="button" class="btn btn-block btn-primary btn-modal" 
                    data-href="{{action([\App\Http\Controllers\GroupTaxController::class, 'create'])}}" 
                    data-container=".tax_group_modal">
                    <i class="fa fa-plus"></i> @lang( 'messages.add' )</button> --}}
                    <button class="add-btn tw-gap-1 btn-modal pull-right"
                        data-href="{{action([\App\Http\Controllers\GroupTaxController::class, 'create'])}}" 
                        data-container=".tax_group_modal">
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
        @endcan
        @can('tax_rate.view')
            <div class="table-responsive">
                <table class="table tw-border table-striped" id="tax_groups_table">
                    <thead class="tw-text-white tw-bg-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-800">
                        <tr>
                            <th>@lang( 'tax_rate.name' )</th>
                            <th>@lang( 'tax_rate.rate' )</th>
                            <th>@lang( 'tax_rate.sub_taxes' )</th>
                            <th>@lang( 'messages.action' )</th>
                        </tr>
                    </thead>
                </table>
            </div>
        @endcan
    @endcomponent
    
    <div class="modal fade tax_rate_modal" tabindex="-1" role="dialog" 
    	aria-labelledby="gridSystemModalLabel">
    </div>
    <div class="modal fade tax_group_modal" tabindex="-1" role="dialog" 
        aria-labelledby="gridSystemModalLabel">
    </div>

</section>
<!-- /.content -->
@endsection
