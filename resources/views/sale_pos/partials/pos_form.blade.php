<div class="row">
	<div class="col-md-4 col-sm-6">
		<div class="form-group">
			{!! Form::label('contact_id', __('lang_v1.contact_id') . ':') !!}

			<div class="input-group">
				<!-- <span class="input-group-addon">
					<i class="fa fa-user"></i>
				</span> -->
				<input type="hidden" id="default_customer_id" 
				value="{{ $walk_in_customer['id'] ?? ''}}" >
				<input type="hidden" id="default_customer_name" 
				value="{{ $walk_in_customer['name'] ?? ''}}" >
				<input type="hidden" id="default_customer_balance" 
				value="{{ $walk_in_customer['balance'] ?? ''}}" >
				<input type="hidden" id="default_customer_address" 
				value="{{ $walk_in_customer['shipping_address'] ?? ''}}" >
				@if(!empty($walk_in_customer['price_calculation_type']) && $walk_in_customer['price_calculation_type'] == 'selling_price_group')
					<input type="hidden" id="default_selling_price_group" 
				value="{{ $walk_in_customer['selling_price_group_id'] ?? ''}}" >
				@endif
				{!! Form::select('contact_id', [], null, ['class' => 'form-control mousetrap', 'id' => 'customer_id', 'placeholder' => 'Enter Customer name / phone', 'required']); !!}
				<span class="input-group-addon">
					<button type="button" class="btn-flat add_new_customer" data-name=""  @if(!auth()->user()->can('customer.create')) disabled @endif><i class="fa fa-plus-circle text-white fa-lg"></i></button>
				</span>
			</div>
			<small class="text-danger hide contact_due_text"><strong>@lang('account.customer_due'):</strong> <span></span></small>
			
		</div>
	</div>
	<div class="col-md-4 col-sm-6">
		<div class="form-group">
			{!! Form::label('search_product', __('lang_v1.search_product') . ':') !!}
			<div class="input-group">
				<!-- <div class="input-group-btn">
					<button type="button" class="btn btn-default bg-white btn-flat" data-toggle="modal" data-target="#configure_search_modal" title="{{__('lang_v1.configure_product_search')}}"><i class="fas fa-search-plus"></i></button>
				</div> -->
				{{-- Removed mousetrap class as it was causing issue with barcode scanning --}}
				{!! Form::text('search_product', null, ['class' => 'form-control', 'id' => 'search_product', 'placeholder' => __('lang_v1.search_product_placeholder'),
				'disabled' => is_null($default_location)? true : false,
				'autofocus' => is_null($default_location)? false : true,
				]); !!}
				<span class="input-group-addon">

					<!-- Show button for weighing scale modal -->
					@if(isset($pos_settings['enable_weighing_scale']) && $pos_settings['enable_weighing_scale'] == 1)
						<button type="button" class="btn-flat" id="weighing_scale_btn" data-toggle="modal" data-target="#weighing_scale_modal" 
						title="@lang('lang_v1.weighing_scale')"><i class="fa fa-digital-tachograph text-white fa-lg"></i></button>
					@endif
					

					<button type="button" class="btn-flat pos_add_quick_product" data-href="{{action([\App\Http\Controllers\ProductController::class, 'quickAdd'])}}" data-container=".quick_add_product_modal"><i class="fa fa-plus-circle text-white fa-lg"></i></button>
				</span>
			</div>
		</div>
	</div>
	<div class="col-md-4 col-sm-6">
		<div class="form-group">
			{!! Form::label('temp_customer_name', __('lang_v1.temp_customer_name') . ':') !!}
			{!! Form::text('temp_customer_name', null, ['class' => 'form-control', 'placeholder' => __('lang_v1.enter_temp_customer_name_if_needed')]) !!}
		</div>
	</div>
</div>
<div class="row">
	@if(!empty($pos_settings['show_invoice_layout']))
	<div class="col-md-4 col-sm-6">
		<div class="form-group">
		{!! Form::select('invoice_layout_id', 
					$invoice_layouts, $default_location->invoice_layout_id, ['class' => 'form-control select2', 'placeholder' => __('lang_v1.select_invoice_layout'), 'id' => 'invoice_layout_id']); !!}
		</div>
	</div>
	@endif
	<input type="hidden" name="pay_term_number" id="pay_term_number" value="{{$walk_in_customer['pay_term_number'] ?? ''}}">
	<input type="hidden" name="pay_term_type" id="pay_term_type" value="{{$walk_in_customer['pay_term_type'] ?? ''}}">
	
@if(!empty($commission_agent))
    @php
        $is_commission_agent_required = !empty($pos_settings['is_commission_agent_required']);
    @endphp
    <div class="col-md-4">
        <div class="form-group">
            <select id="commission_agent"
                    name="commission_agent"
                    class="form-control select2"
                    data-placeholder="{{ __('lang_v1.commission_agent') }}"
                    {{ $is_commission_agent_required ? '' : '' }}>
                {{-- Visible "None" item uses a non-empty value (0) so it shows in the dropdown --}}
                <option value="0" data-cmmsn="0">{{ __('lang_v1.none') }}</option>

                @foreach($commission_agent as $id => $name)
                    @php
                        $pct = isset($agent_percents) ? ($agent_percents[$id] ?? 0)
                              : (optional(\App\User::find($id))->cmmsn_percent ?? 0);
                    @endphp
                    <option value="{{ $id }}" data-cmmsn="{{ $pct }}">{{ $name }}</option>
                @endforeach
            </select>
        </div>
    </div>
@endif


	@if(!empty($pos_settings['enable_transaction_date']))
		<div class="col-md-4 col-sm-6">
			<div class="form-group">
				<div class="input-group">
					<span class="input-group-addon">
						<i class="fa fa-calendar"></i>
					</span>
					{!! Form::text('transaction_date', $default_datetime, ['class' => 'form-control', 'readonly', 'required', 'id' => 'transaction_date']); !!}
				</div>
			</div>
		</div>
	@endif
	@if(!empty($currency_settings_enabled))
    <div class="col-md-4 col-sm-6">
        <div class="form-group">
            {!! Form::label('currency_code', __('lang_v1.select_currency')) !!}
            <div class="input-group">
                <span class="input-group-addon"><i class="fas fa-coins"></i></span>
                <select name="currency_code" id="currency_code" class="form-control select2">
                    <option value="">@lang('lang_v1.select_currency')</option>
                    @foreach($currency_rates as $rate)
                        <option value="{{ $rate->currency_code }}"
                                data-rate="{{ $rate->exchange_rate }}">
                            {{ $rate->currency_code }} - {{ $rate->currency_name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- Exchange rate --}}
	<div class="col-md-4 col-sm-6">
		<div class="form-group">
			{!! Form::label('exchange_rate', __('lang_v1.currency_exchange_rate')) !!}
			<div class="input-group">
				<span class="input-group-addon"><i class="fas fa-exchange-alt"></i></span>
				<input type="text" name="exchange_rate" id="exchange_rate" value="1"
					class="form-control input_number"
					placeholder="@lang('lang_v1.currency_exchange_rate')" readonly>
			</div>
		</div>
	</div>

@endif



	@if(!empty($price_groups) && count($price_groups) > 1)
		<div class="col-md-4 col-sm-6">
			<div class="form-group">
				<div class="input-group">
					<span class="input-group-addon">
						<i class="fas fa-money-bill-alt"></i>
					</span>
					@php
						reset($price_groups);
						$selected_price_group = !empty($default_price_group_id) && array_key_exists($default_price_group_id, $price_groups) ? $default_price_group_id : null;
					@endphp
					{!! Form::hidden('hidden_price_group', key($price_groups), ['id' => 'hidden_price_group']) !!}
					{!! Form::select('price_group', $price_groups, $selected_price_group, ['class' => 'form-control select2', 'id' => 'price_group']); !!}
					<span class="input-group-addon">
						@show_tooltip(__('lang_v1.price_group_help_text'))
					</span> 
				</div>
			</div>
		</div>
	@else
		@php
			reset($price_groups);
		@endphp
		{!! Form::hidden('price_group', key($price_groups), ['id' => 'price_group']) !!}
	@endif
	@if(!empty($default_price_group_id))
		{!! Form::hidden('default_price_group', $default_price_group_id, ['id' => 'default_price_group']) !!}
	@endif

	@if(in_array('types_of_service', $enabled_modules) && !empty($types_of_service))
		<div class="col-md-4 col-sm-6">
			<div class="form-group">
				{!! Form::label('types_of_service', __('lang_v1.types_of_service') . ':') !!}
				<span>
					@show_tooltip(__('lang_v1.types_of_service_help'))
				</span> 
				<div class="input-group">
					<span class="input-group-addon">
						<i class="fa fa-external-link-square-alt text-wwhite service_modal_btn"></i>
					</span>
					{!! Form::select('types_of_service_id', $types_of_service, null, ['class' => 'form-control', 'id' => 'types_of_service_id', 'style' => 'width: 100%;', 'placeholder' => __('lang_v1.select_types_of_service')]); !!}

					{!! Form::hidden('types_of_service_price_group', null, ['id' => 'types_of_service_price_group']) !!}

					
				</div>
				<small><p class="help-block hide" id="price_group_text">@lang('lang_v1.price_group'): <span></span></p></small>
			</div>
		</div>
		<div class="modal fade types_of_service_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>
	@endif

	@if(!empty($pos_settings['show_invoice_scheme']))
		@php
			$invoice_scheme_id = $default_invoice_schemes->id;
			if(!empty($default_location->invoice_scheme_id)) {
				$invoice_scheme_id = $default_location->invoice_scheme_id;
			}
		@endphp
		<div class="col-md-4 col-sm-6">
			<div class="form-group">
				{!! Form::select('invoice_scheme_id', $invoice_schemes, $invoice_scheme_id, 
					['class' => 'form-control', 'placeholder' => __('lang_v1.select_invoice_scheme'), 
					'id' => 'invoice_scheme_id']); !!}
			</div>
		</div>
	@endif
	@if(in_array('subscription', $enabled_modules))
		<div class="col-md-4 col-sm-6">
			<div class="input-group">
				<span class="input-group-addon">
					<button type="button" data-toggle="modal" data-target="#recurringInvoiceModal" class="btn btn-link"><i class="fa fa-external-link-square-alt text-white"></i></button>
				</span>
				<label>
				  {!! Form::checkbox('is_recurring', 1, false, ['class' => 'input-icheck', 'id' => 'is_recurring']); !!} @lang('lang_v1.subscribe')?
				</label>
				<span>@show_tooltip(__('lang_v1.recurring_invoice_help'))</span>
			</div>
		</div>
	@endif
	
	
	<!-- Call restaurant module if defined -->
    @if(in_array('tables' ,$enabled_modules) || in_array('service_staff' ,$enabled_modules))
    	<div class="clearfix"></div>
    	<span id="restaurant_module_span">
      		<div class="col-md-4 col-sm-6"></div>
    	</span>
    @endif
	@if(in_array('kitchen' ,$enabled_modules))
		<div class="col-md-4 col-sm-6">
			<div class="form-group">
				<div class="checkbox">
				<label>
						{!! Form::checkbox('is_kitchen_order', 1, false, ['class' => 'input-icheck status', 'id' => 'is_kitchen_order']); !!} {{ __('lang_v1.kitchen_order') }}
				</label>
				@show_tooltip(__('lang_v1.kitchen_order_tooltip'))
				</div>
			</div>
		</div>
    @endif
	
    
</div>
<!-- include module fields -->
@if(!empty($pos_module_data))
    @foreach($pos_module_data as $key => $value)
        @if(!empty($value['view_path']))
            @includeIf($value['view_path'], ['view_data' => $value['view_data']])
        @endif
    @endforeach
@endif
<div class="row tw-mt-5">
	<div class="col-sm-12 pos_product_div">
		<input type="hidden" name="sell_price_tax" id="sell_price_tax" value="{{$business_details->sell_price_tax}}">

		<!-- Keeps count of product rows -->
		<input type="hidden" id="product_row_count" 
			value="0">
		@php
			$hide_tax = '';
			if( session()->get('business.enable_inline_tax') == 0){
				$hide_tax = 'hide';
			}
		@endphp
		<table class="table table-condensed table-bordered table-striped table-responsive" id="pos_table">
			<thead class="tw-text-white tw-bg-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-800">
				<tr>
					<th class="tex-center tw-texst-sm tw-font-bold @if(!empty($pos_settings['inline_service_staff'])) col-md-3 @else col-md-4 @endif">	
						@lang('sale.product') 
						<span class="text-white">@show_tooltip(__('lang_v1.tooltip_sell_product_column'))</span>
					</th>
					<th class="text-center tw-text-sm tw-font-bold col-md-3">
						@lang('sale.qty')
					</th>
					@if(!empty($pos_settings['inline_service_staff']))
						<th class="text-center tw-text-sm  tw-font-bold col-md-2">
							@lang('restaurant.service_staff')
						</th>
					@endif
					<th class="text-center tw-text-sm  tw-font-bold col-md-2 {{$hide_tax}}">
						@lang('sale.price_inc_tax')
					</th>
					<th class="text-center tw-text-sm  tw-font-bold col-md-2">
						@lang('sale.subtotal')
					</th>
					<th class="text-center"><i class="fas fa-times !tw-text-sm" aria-hidden="true"></i></th>
				</tr>
			</thead>
			<tbody></tbody>
		</table>
	</div>
</div>


<script>
$(function () {
  var $ca = $('#commission_agent');
  if (!$ca.length) return;

  var NONE_VALUE = '0';

  // keep allowClear if you also want the "x" button (optional)
  $ca.select2({
    allowClear: true,
    placeholder: $ca.data('placeholder') || '{{ __("lang_v1.commission_agent") }}'
  });

  function handleCleared() {
    $('#commission_amount').val('');
    $('#commission_default_percent').val('0');
    $('#commission_chosen').val('0');
    $('#total_commission').text('0');
    if (typeof calculate_billing === 'function') { calculate_billing(); }
  }

  $ca.on('change', function () {
    var v = this.value;
    if (!v || v === NONE_VALUE) {
      handleCleared();
    } else {
      // normal agent selected -> your existing logic will read data-cmmsn
    }
  });

  // Ensure form submits an empty value (not "0") when None is chosen
  $('form').on('submit', function () {
    if ($ca.val() === NONE_VALUE) $ca.val('');
  });
});
</script>
