@extends('layouts.app')
@section('title', __('unit.units'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang('unit.units')
            <small class="tw-text-sm md:tw-text-base tw-text-gray-700 tw-font-semibold">@lang('unit.manage_your_units')</small>
        </h1>
        <!-- <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
            <li class="active">Here</li>
        </ol> -->
    </section>

    <!-- Main content -->
    <section class="content">
        @component('components.widget', ['class' => 'box-primary', 'title' => __('unit.all_your_units')])
            @can('unit.create')
                @slot('tool')
                    <div class="box-tools">
                        {{-- <button type="button" class="btn btn-block btn-primary btn-modal" 
                        data-href="{{action([\App\Http\Controllers\UnitController::class, 'create'])}}" 
                        data-container=".unit_modal">
                        <i class="fa fa-plus"></i> @lang( 'messages.add' )</button> --}}
                        <a 
                            class="btn-modal pull-right add-btn"
                            data-href="{{action([\App\Http\Controllers\UnitController::class, 'create'])}}" 
                            data-container=".unit_modal">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="icon icon-tabler icons-tabler-outline icon-tabler-plus">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M12 5l0 14" />
                                <path d="M5 12l14 0" />
                            </svg> @lang('messages.add')
                        </a>
                    </div>
                @endslot
            @endcan
            @can('unit.view')
                <div class="table-responsive">
                    <table class="table tw-border table-striped" id="unit_table">
                        <thead class="tw-text-white tw-bg-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-800">
                            <tr>
                                <th>@lang('unit.name')</th>
                                <th>@lang('unit.short_name')</th>
                                <th>@lang('unit.allow_decimal') @show_tooltip(__('tooltip.unit_allow_decimal'))</th>
                                <th>@lang('messages.action')</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            @endcan
        @endcomponent

        <div class="modal fade unit_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        </div>

    </section>
    <!-- /.content -->

@endsection
