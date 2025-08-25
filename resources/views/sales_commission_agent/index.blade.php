@extends('layouts.app')
@section('title', __('lang_v1.sales_commission_agents'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang( 'lang_v1.sales_commission_agents' )
    </h1>
</section>

<!-- Main content -->
<section class="content">
    @component('components.widget', ['class' => 'box-primary'])
        @can('user.create')
            @slot('tool')
                <div class="box-tools" style="margin: 0 1rem;">    
                        <!-- dont delete btn-model it destroy the function -->
                        <a
                            class="btn-modal tw-inline-flex tw-transition-all hover:tw-text-white tw-cursor-pointer tw-duration-50 tw-bg-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-800 hover:tw-bg-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-700 tw-py-2 tw-px-4 tw-rounded-full tw-items-center tw-justify-center tw-text-sm tw-font-medium tw-text-white tw-gap-1"
                            data-href="{{action([\App\Http\Controllers\SalesCommissionAgentController::class, 'create'])}}" 
                            data-container=".commission_agent_modal"
                        >
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
        @can('user.view')
            <div class="table-responsive">
                <table class="table table-striped tw-rounded-lg tw-border tw-overflow-hidden" id="sales_commission_agent_table">
                    <thead class="tw-text-white tw-bg-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-800">
                        <tr>
                            <th>@lang( 'user.name' )</th>
                            <th>@lang( 'business.email' )</th>
                            <th>@lang( 'lang_v1.contact_no' )</th>
                            <th>@lang( 'business.address' )</th>
                            <th>@lang( 'lang_v1.cmmsn_percent' )</th>
                            <th>@lang( 'messages.action' )</th>
                        </tr>
                    </thead>
                </table>
            </div>
        @endcan
    @endcomponent

    <div class="modal fade commission_agent_modal" tabindex="-1" role="dialog" 
    	aria-labelledby="gridSystemModalLabel">
    </div>

</section>
<!-- /.content -->

@endsection
