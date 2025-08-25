<div class="table-responsive">
    <table class="table table-bordered table-striped table-text-center" id="profit_by_locations_table">
        <thead class="tw-text-white tw-bg-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-800">
            <tr>
                <th>@lang('sale.location')</th>
                <th>@lang('lang_v1.gross_profit')</th>
            </tr>
        </thead>
        <tfoot style="background-color: #e9ecef;">
            <tr class="footer-total">
                <td><strong>@lang('sale.total'):</strong></td>
                <td class="footer_total"></td>
            </tr>
        </tfoot>
    </table>
</div>