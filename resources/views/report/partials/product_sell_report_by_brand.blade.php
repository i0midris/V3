<div class="tab-pane" id="psr_by_brand_tab">
    <div class="table-responsive">
        <table class="table table-bordered table-striped" id="product_sell_report_by_brand" style="width: 100%;">
            <thead class="tw-text-white tw-bg-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-800">
                <tr>
                    <th>@lang('product.brand')</th>
                    <th>@lang('report.current_stock')</th>
                    <th>@lang('report.total_unit_sold')</th>
                    <th>@lang('sale.total')</th>
                </tr>
            </thead>
            <tfoot style="background-color: #e9ecef;">
                <tr class="footer-total text-center">
                    <td><strong>@lang('sale.total'):</strong></td>
                    <td id="footer_psr_by_brand_total_stock"></td>
                    <td id="footer_psr_by_brand_total_sold"></td>
                    <td><span class="display_currency" id="footer_psr_by_brand_total_sell" data-currency_symbol ="true"></span></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>