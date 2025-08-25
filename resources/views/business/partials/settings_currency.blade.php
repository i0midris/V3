<div class="pos-tab-content">
    <div class="row">
        <div class="col-sm-6">
            <h4>@lang('business.currency_settings')</h4>
            <div class="form-group">
                <div class="checkbox">
                    <label>
                        {!! Form::checkbox('currency_settings_enabled', 1, !empty($business->currency_settings_enabled), ['class' => 'input-icheck']) !!}
                        @lang('business.enable_currency_selection')
                    </label>
                    @show_tooltip(__('business.enable_currency_selection_help'))
                </div>
            </div>
        </div>
    </div>
</div>
