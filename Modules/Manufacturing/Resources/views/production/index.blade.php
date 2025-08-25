@extends('layouts.app')
@section('title', __('manufacturing::lang.production'))

@section('content')
@include('manufacturing::layouts.nav')
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang('manufacturing::lang.production')</h1>
</section>

<!-- Main content -->
<section class="content">
    @component('components.filters', ['title' => __('report.filters')])
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('productstion_list_filter_location_id',  __('purchase.business_location') . ':') !!}

                {!! Form::select('productstion_list_filter_location_id', $business_locations, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all') ]); !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('production_list_filter_date_range', __('report.date_range') . ':') !!}
                {!! Form::text('production_list_filter_date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'readonly']); !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <div class="checkbox">
                    <br>
                    <label>
                      {!! Form::checkbox('production_list_is_final', 1, false, 
                      [ 'class' => 'input-icheck', 'id' => 'production_list_is_final']); !!} {{ __('manufacturing::lang.finalize') }}
                    </label>
                </div>
            </div>
        </div>
    @endcomponent
    @component('components.widget', ['class' => 'box-solid'])
        @slot('tool')
        <div class="box-tools tw-flex tw-gap-2 tw-justify-end">
    
            {{-- Blue "Add" Button --}}
            <a class="tw-dw-btn tw-bg-gradient-to-r tw-from-indigo-600 tw-to-blue-500 tw-font-bold tw-text-white tw-border-none tw-rounded-full"
                href="{{ action([\Modules\Manufacturing\Http\Controllers\ProductionController::class, 'create']) }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="icon icon-tabler icons-tabler-outline icon-tabler-plus">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path d="M12 5l0 14" />
                    <path d="M5 12l14 0" />
                </svg>
                @lang('messages.add')
            </a>
            {{-- Green "Add Multiple" Button --}}
            <a class="tw-dw-btn tw-bg-gradient-to-r tw-from-green-600 tw-to-emerald-500 tw-font-bold tw-text-white tw-border-none tw-rounded-full"
                href="{{ action([\Modules\Manufacturing\Http\Controllers\ProductionController::class, 'createMultiple']) }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-plus" width="24" height="24"
                    viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round"
                    stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path d="M12 5v14" />
                    <path d="M5 12h14" />
                </svg>
                @lang('manufacturing::lang.add_multiple')
            </a>
        </div>

        @endslot
        <div class="table-responsive">
        <table class="table table-bordered table-striped" id="productions_table">
    <thead>
        <tr>
            <th>@lang('messages.date')</th>
            <th>@lang('purchase.ref_no')</th>
            <th>@lang('purchase.location')</th>
            <th>@lang('sale.product')</th>
            <th>@lang('lang_v1.quantity')</th>
            <th>@lang('manufacturing::lang.total_cost')</th>
            <th>@lang('messages.action')</th>
        </tr>
    </thead>
    <tfoot>
        <tr>
            <th class="text-left">@lang('report.total')</th>
            <th></th>
            <th></th>
            <th></th>
            <th id="footer_total_quantity"></th>
            <th id="footer_total_cost"></th>
            <th></th>
        </tr>
    </tfoot>
</table>

        </div>
    @endcomponent
</section>
<!-- /.content -->
<div class="modal fade" id="recipe_modal" tabindex="-1" role="dialog" 
    aria-labelledby="gridSystemModalLabel">
</div>
@stop
@section('javascript')
    @include('manufacturing::layouts.partials.common_script')
    <script>
    $(document).ready(function () {
        if ($.fn.DataTable.isDataTable('#productions_table')) {
            $('#productions_table').DataTable().clear().destroy();
        }

        productions_table = $('#productions_table').DataTable({
            processing: true,
            serverSide: true,
            aaSorting: [[0, 'desc']],
            ajax: {
                url: '{{ action([\Modules\Manufacturing\Http\Controllers\ProductionController::class, 'index']) }}',
                data: function (d) {
                    const range = $('#production_list_filter_date_range').data('daterangepicker');
                    if (range) {
                        d.start_date = range.startDate.format('YYYY-MM-DD');
                        d.end_date = range.endDate.format('YYYY-MM-DD');
                    }
                    d.location_id = $('#productstion_list_filter_location_id').val();
                    if ($('#production_list_is_final').is(':checked')) {
                        d.is_final = 1;
                    }
                }
            },
            columns: [
                { data: 'transaction_date', name: 'transaction_date' },
                { data: 'ref_no', name: 'ref_no' },
                { data: 'location_name', name: 'bl.name' },
                { data: 'product_name', name: 'product_name' },
                { data: 'quantity', searchable: false },
                { data: 'final_total', name: 'final_total' },
                { data: 'action', name: 'action' },

                // 🔒 Hidden raw columns
                { data: 'quantity_raw', visible: false, searchable: false },
                { data: 'final_total_raw', visible: false, searchable: false }
            ],
            
            footerCallback: function (row, data, start, end, display) {
                const api = this.api();

                const parse = val => parseFloat(val) || 0;

                const totalQty = api
                    .column(7, { page: 'current' })
                    .data()
                    .reduce((a, b) => parse(a) + parse(b), 0);

                const totalCost = api
                    .column(8, { page: 'current' })
                    .data()
                    .reduce((a, b) => parse(a) + parse(b), 0);

                $('#footer_total_quantity').html(
                    typeof __number_f === 'function' ? __number_f(totalQty, false) : totalQty.toFixed(2)
                );

                $('#footer_total_cost').html(
                    typeof __currency_trans_from_en === 'function'
                        ? __currency_trans_from_en(totalCost, true)
                        : totalCost.toFixed(2)
                );
            },
            fnDrawCallback: function () {
                __currency_convert_recursively($('#productions_table'));
            }
        });

        $('#production_list_filter_date_range, #productstion_list_filter_location_id').on('change', function () {
            productions_table.ajax.reload();
        });

        $('#production_list_is_final').on('ifChanged', function () {
            productions_table.ajax.reload();
        });
        
    });
    
</script>

@endsection
