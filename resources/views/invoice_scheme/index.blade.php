@extends('layouts.app')
@section('title', __('invoice.invoice_settings'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1  class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang( 'invoice.invoice_settings' )
        <small class="tw-text-sm md:tw-text-base tw-text-gray-700 tw-font-semibold">@lang( 'invoice.manage_your_invoices' )</small>
    </h1>
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <!-- Custom Tabs -->
            @component('components.widget')
             <div class="tw-border tw-rounded-xl tw-shadow-sm tw-overflow-hidden tw-mx-1 tw-bg-white">
                <div class="nav-tabs-custom">
                    <ul class="nav nav-tabs nav-justified custom-nav-tabs">
                        <li class="active"><a href="#tab_1" data-toggle="tab" aria-expanded="true">@lang('invoice.invoice_schemes')</a></li>
                        <li class=""><a href="#tab_2" data-toggle="tab" aria-expanded="false">@lang('invoice.invoice_layouts')</a></li>
                    </ul>
                </div>
                <div class="tab-content tw-px-4 tw-pb-4">
                    <div class="tab-pane active" id="tab_1">
                        <div class="row">
                            <div class="col-md-12">
                                <h4>@lang( 'invoice.all_your_invoice_schemes' )
                                        <button 
                                            class="pull-right btn-modal add-btn tw-gap-1 tw-w-24"
                                            data-href="{{action([\App\Http\Controllers\InvoiceSchemeController::class, 'create'])}}" 
                                            data-container=".invoice_modal">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="3" stroke-linecap="round" stroke-linejoin="round"
                                                class="icon icon-tabler icons-tabler-outline icon-tabler-plus">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                <path d="M12 5l0 14" />
                                                <path d="M5 12l14 0" />
                                            </svg> @lang('messages.add')
                                        </button>
                                </h4>
                            </div>
                        </div>
                        <br>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table class="table tw-border table-striped" id="invoice_table">
                                        <thead class="tw-text-white tw-bg-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-800">
                                            <tr>
                                                <th>@lang( 'invoice.name' ) @show_tooltip(__('tooltip.invoice_scheme_name'))</th>
                                                <th>@lang( 'invoice.prefix' ) @show_tooltip(__('tooltip.invoice_scheme_prefix'))</th>
                                                <th>@lang( 'invoice.number_type' ) @show_tooltip(__('invoice.number_type_tooltip'))</th>
                                                <th>@lang( 'invoice.start_number' ) @show_tooltip(__('tooltip.invoice_scheme_start_number'))</th>
                                                <th>@lang( 'invoice.invoice_count' ) @show_tooltip(__('tooltip.invoice_scheme_count'))</th>
                                                <th>@lang( 'invoice.total_digits' ) @show_tooltip(__('tooltip.invoice_scheme_total_digits'))</th>
                                                <th>@lang( 'messages.action' )</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /.tab-pane -->
                    <div class="tab-pane" id="tab_2">
                        <div class="row">
                            <div class="col-md-12">
                                <h4>@lang( 'invoice.all_your_invoice_layouts' ) 
                                    <a class="add-btn tw-gap-1 tw-w-24 pull-right" href="{{action([\App\Http\Controllers\InvoiceLayoutController::class, 'create'])}}">
                                        <svg  xmlns="http://www.w3.org/2000/svg"  width="14"  height="14"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="3"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-plus"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>
                                        @lang( 'messages.add' )
                                    </a>
                                </h4>
                            </div>
                            <div class="col-md-12">
                                @foreach( $invoice_layouts as $layout)
                                <div class="col-xs-12 tw-flex tw-justify-center tw-flex-col tw-mt-5">
                                    <div class="tw-flex tw-flex-col tw-mx-auto" style="width:10rem">
                                        <div class="tw-relative tw-flex tw-flex-col tw-items-center">
                                            <a class="file-btn" href="{{action([\App\Http\Controllers\InvoiceLayoutController::class, 'edit'], [$layout->id])}}">
                                                <svg  xmlns="http://www.w3.org/2000/svg"  width="100"  height="100"  viewBox="0 0 24 24"  fill="currentColor"  class="icon icon-tabler icons-tabler-filled icon-tabler-file-download"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 2l.117 .007a1 1 0 0 1 .876 .876l.007 .117v4l.005 .15a2 2 0 0 0 1.838 1.844l.157 .006h4l.117 .007a1 1 0 0 1 .876 .876l.007 .117v9a3 3 0 0 1 -2.824 2.995l-.176 .005h-10a3 3 0 0 1 -2.995 -2.824l-.005 -.176v-14a3 3 0 0 1 2.824 -2.995l.176 -.005zm0 8a1 1 0 0 0 -1 1v3.585l-.793 -.792a1 1 0 0 0 -1.32 -.083l-.094 .083a1 1 0 0 0 0 1.414l2.5 2.5l.044 .042l.068 .055l.11 .071l.114 .054l.105 .035l.15 .03l.116 .006l.117 -.007l.117 -.02l.108 -.033l.081 -.034l.098 -.052l.092 -.064l.094 -.083l2.5 -2.5a1 1 0 0 0 0 -1.414l-.094 -.083a1 1 0 0 0 -1.32 .083l-.793 .791v-3.584a1 1 0 0 0 -.883 -.993zm2.999 -7.001l4.001 4.001h-4z" /></svg>
                                                {{ $layout->name }}
                                            </a>
                                            @if( $layout->is_default )
                                            <span class="badge bg-green" style="position:absolute;right:10%;top:33%;">@lang("barcode.default")</span>
                                            @endif
                                        </div>
                                        @if($layout->locations->count())
                                        <span class="text-center tw-mt-4">
                                            <b>@lang('invoice.used_in_locations'): </b><br>
                                            @foreach($layout->locations as $location)
                                            {{ $location->name }}
                                            @if (!$loop->last)
                                            ,
                                            @endif
                                            &nbsp;
                                            @endforeach
                                        </span>
                                        @endif
                                    </div>
                                </div>
                                @if( $loop->iteration % 4 == 0 )
                                <div class="clearfix"></div>
                                @endif
                                @endforeach
                            </div>
                        </div>
                        <br>
                    </div>
                    <!-- /.tab-pane -->
                </div>
                <!-- /.tab-content -->
            </div>
            @endcomponent
            <!-- nav-tabs-custom -->
        </div>
    </div>

    <div class="modal fade invoice_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>
    <div class="modal fade invoice_edit_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>

</section>
<!-- /.content -->

@endsection