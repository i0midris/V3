{{-- Edit Commission Modal --}}
<div class="modal fade" id="posEditCommissionModal" tabindex="-1" role="dialog" aria-labelledby="posEditCommissionModalTitle" aria-modal="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">

      {{-- Header --}}
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="@lang('messages.close')">
          <span aria-hidden="true">&times;</span>
        </button>
        <h4 class="modal-title" id="posEditCommissionModalTitle">@lang('lang_v1.commission')</h4>
      </div>

      {{-- Body --}}
      <div class="modal-body">
        <div class="row">
          <div class="col-md-12">
            <h5 class="m-t-0">@lang('lang_v1.edit_commission'):</h5>
          </div>

          {{-- Commission Type --}}
          <div class="col-md-6">
            <div class="form-group">
              {!! Form::label('commission_type_modal', __('lang_v1.commission_type') . ':*') !!}
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-info" aria-hidden="true"></i></span>
                {!! Form::select(
                  'commission_type_modal',
                  ['fixed' => __('lang_v1.fixed'), 'percentage' => __('lang_v1.percentage')],
                  isset($commission_type) ? $commission_type : 'percentage',
                  ['class' => 'form-control', 'id' => 'commission_type_modal', 'required' => true]
                ) !!}
              </div>
            </div>
          </div>

          {{-- Commission Amount --}}
          <div class="col-md-6">
            <div class="form-group">
              {!! Form::label('commission_amount_modal', __('sale.amount') . ':*') !!}
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-calculator" aria-hidden="true"></i></span>
                {!! Form::text(
                  'commission_amount_modal',
                  isset($commission_amount) ? @num_format($commission_amount) : '',
                  [
                    'class' => 'form-control input_number',
                    'id' => 'commission_amount_modal',
                    'placeholder' => __('messages.please_enter'),
                    'autocomplete' => 'off'
                  ]
                ) !!}
              </div>
              <p class="help-block" id="commission_modal_hint"></p>
            </div>
          </div>

          {{-- Default percent used for fallback (kept in sync via JS when agent changes) --}}
          @php
            $me = auth()->user();
            $default_percent = isset($default_percent) ? $default_percent : ($me->cmmsn_percent ?? 0);
          @endphp
          <input type="hidden" id="commission_default_percent" value="{{ $default_percent }}">
        </div>
      </div>

      {{-- Footer --}}
{{-- Footer --}}
<div class="modal-footer">
  <button type="button" class="tw-dw-btn tw-dw-btn-danger" id="posEditCommissionModalClear">
    @lang('lang_v1.clear_commission')
  </button>
  <button type="button" class="tw-dw-btn tw-dw-btn-primary tw-text-white" id="posEditCommissionModalUpdate">
    @lang('messages.update')
  </button>
  <button type="button" class="tw-dw-btn tw-dw-btn-neutral tw-text-white" data-dismiss="modal">
    @lang('messages.cancel')
  </button>
</div>


    </div>
  </div>
</div>
