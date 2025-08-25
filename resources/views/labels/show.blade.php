@extends('layouts.app')
@section('title', __('barcode.print_labels'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
<br>
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang('barcode.print_labels') @show_tooltip(__('tooltip.print_label'))</h1>
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
</section>

<!-- Main content -->
<section class="content no-print">
	{!! Form::open(['url' => '#', 'method' => 'post', 'id' => 'preview_setting_form', 'onsubmit' => 'return false']) !!}
	@component('components.widget', ['class' => 'box-primary', 'title' => __('product.add_product_for_labels')])
		<div class="row">
			<div class="col-sm-8 col-sm-offset-2">
				<div class="form-group">
					<div class="input-group">
						<span class="input-group-addon">
							<i class="fa fa-search"></i>
						</span>
						{!! Form::text('search_product', null, ['class' => 'form-control', 'id' => 'search_product_for_label', 'placeholder' => __('lang_v1.enter_product_name_to_print_labels'), 'autofocus']); !!}
					</div>
				</div>
			</div>
		</div>

		<div class="row tw-mt-4">
			<div class="col-sm-12">
				<table class="table table-striped table-condensed" id="product_table">
					<thead class="tw-text-white tw-bg-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-800">
						<tr>
							<th>@lang( 'barcode.products' )</th>
							<th>@lang( 'barcode.no_of_labels' )</th>
							@if(request()->session()->get('business.enable_lot_number') == 1)
								<th>@lang( 'lang_v1.lot_number' )</th>
							@endif
							@if(request()->session()->get('business.enable_product_expiry') == 1)
								<th>@lang( 'product.exp_date' )</th>
							@endif
							<th>@lang('lang_v1.packing_date')</th>
							<th>@lang('lang_v1.selling_price_group')</th>
						</tr>
					</thead>
					<tbody>
						@include('labels.partials.show_table_rows', ['index' => 0])
					</tbody>
				</table>
			</div>
		</div>
	@endcomponent

	@component('components.widget', ['class' => 'box-primary', 'title' => __( 'barcode.info_in_labels' )])
		<div class="row">
			<div class="col-md-12">
				<div class="row tw-gap-4">
					<div class="col-sm-6 col-md-4 col-lg-3">
						<div class="checkbox">
							<label>
								<input type="checkbox" checked name="print[name]" value="1"> <b>@lang( 'barcode.print_name' )</b>
							</label>
						</div>

						<div class="input-group">
							<div class="input-group-addon"><b>@lang( 'lang_v1.size' )</b></div>
							<input type="text" class="form-control" 
								name="print[name_size]" 
								value="15">
						</div>
					</div>

					<div class="col-sm-6 col-md-4 col-lg-3">
						<div class="checkbox">
							<label>
								<input type="checkbox" checked name="print[variations]" value="1"> <b>@lang( 'barcode.print_variations' )</b>
							</label>
						</div>

						<div class="input-group">
							<div class="input-group-addon"><b>@lang( 'lang_v1.size' )</b></div>
							<input type="text" class="form-control" 
								name="print[variations_size]" 
								value="17">
						</div>
					</div>

					<div class="col-sm-6 col-md-4 col-lg-3">
						<div class="checkbox">
							<label>
								<input type="checkbox" checked name="print[price]" value="1" id="is_show_price"> <b>@lang( 'barcode.print_price' )</b>
							</label>
						</div>

						<div class="input-group">
							<div class="input-group-addon"><b>@lang( 'lang_v1.size' )</b></div>
							<input type="text" class="form-control" 
								name="print[price_size]" 
								value="17">
						</div>

					</div>

					<div class="col-sm-6 col-md-4 col-lg-3">
						
						<div class="" id="price_type_div">
							<div class="form-group">
								{!! Form::label('print[price_type]', @trans( 'barcode.show_price' ) . ':') !!}
								<div class="input-group">
									<span class="input-group-addon">
										<i class="fa fa-info"></i>
									</span>
									{!! Form::select('print[price_type]', ['inclusive' => __('product.inc_of_tax'), 'exclusive' => __('product.exc_of_tax')], 'inclusive', ['class' => 'form-control']); !!}
								</div>
							</div>
						</div>

					</div>

					<div class="col-sm-6 col-md-4 col-lg-3">
						<div class="checkbox">
							<label>
								<input type="checkbox" checked name="print[business_name]" value="1"> <b>@lang( 'barcode.print_business_name' )</b>
							</label>
						</div>

						<div class="input-group">
							<div class="input-group-addon"><b>@lang( 'lang_v1.size' )</b></div>
							<input type="text" class="form-control" 
								name="print[business_name_size]" 
								value="20">
						</div>
					</div>

					<div class="col-sm-6 col-md-4 col-lg-3">
						<div class="checkbox">
							<label>
								<input type="checkbox" checked name="print[packing_date]" value="1"> <b>@lang( 'lang_v1.print_packing_date' )</b>
							</label>
						</div>

						<div class="input-group">
							<div class="input-group-addon"><b>@lang( 'lang_v1.size' )</b></div>
							<input type="text" class="form-control" 
								name="print[packing_date_size]" 
								value="12">
						</div>
					</div>

					<div class="col-sm-6 col-md-4 col-lg-3">
						@if(request()->session()->get('business.enable_lot_number') == 1)
						
							<div class="checkbox">
								<label>
									<input type="checkbox" checked name="print[lot_number]" value="1"> <b>@lang( 'lang_v1.print_lot_number' )</b>
								</label>
							</div>

							<div class="input-group">
							<div class="input-group-addon"><b>@lang( 'lang_v1.size' )</b></div>
								<input type="text" class="form-control" 
									name="print[lot_number_size]" 
									value="12">
							</div>
						@endif
					</div>

					<div class="col-sm-6 col-md-4 col-lg-3">
						@if(request()->session()->get('business.enable_product_expiry') == 1)
							<div class="checkbox">
								<label>
									<input type="checkbox" checked name="print[exp_date]" value="1"> <b>@lang( 'lang_v1.print_exp_date' )</b>
								</label>
							</div>

							<div class="input-group">
							<div class="input-group-addon"><b>@lang( 'lang_v1.size' )</b></div>
								<input type="text" class="form-control" 
									name="print[exp_date_size]" 
									value="12">
							</div>
						@endif
					</div>						
					
						
					@php
						$c = 0;
						$custom_labels = json_decode(session('business.custom_labels'), true);
						$product_custom_fields = !empty($custom_labels['product']) ? $custom_labels['product'] : [];
							$product_cf_details = !empty($custom_labels['product_cf_details']) ? $custom_labels['product_cf_details'] : [];
					@endphp
					@foreach($product_custom_fields as $index => $cf)
						@if(!empty($cf))
							@php
								$field_name = 'product_custom_field' . $loop->iteration;
								$cf_type = !empty($product_cf_details[$loop->iteration]['type']) ? $product_cf_details[$loop->iteration]['type'] : 'text';
								$dropdown = !empty($product_cf_details[$loop->iteration]['dropdown_options']) ? explode(PHP_EOL, $product_cf_details[$loop->iteration]['dropdown_options']) : [];
								$c++;
							@endphp
							<div class="col-sm-6 col-md-4 col-lg-3">
								<div class="checkbox">
									<label>
										<input type="checkbox" name="print[{{ $field_name }}]" value="1"> <b>{{ $cf }}</b>
									</label>
								</div>

								<div class="input-group">
								<div class="input-group-addon"><b>@lang( 'lang_v1.size' )</b></div>
									<input type="text" class="form-control" 
										name="print[{{ $field_name }}_size]" 
										value="12">
								</div>
							</div>
							@if ($c % 4 == 0)
								</tr>
							@endif
						@endif
					@endforeach
					</tr>
				</div>
			</div>

			

			

			<div class="col-sm-12">
				<hr/>
			</div>

			<div class="col-md-8">
				<div class="form-group">
					{!! Form::label('price_type', @trans( 'barcode.barcode_setting' ) . ':') !!}
					<div class="input-group">
						<span class="input-group-addon">
							<i class="fa fa-cog"></i>
						</span>
						{!! Form::select('barcode_setting', $barcode_settings, !empty($default) ? $default->id : null, ['class' => 'form-control']); !!}
					</div>
				</div>
			</div>

			<div class="clearfix"></div>
			
			<div class="col-sm-12 text-center">
				<button 
					type="button" 
					id="labels_preview" 
					class="pull-right tw-flex tw-items-center tw-gap-2 tw-justify-center tw-text-md tw-font-medium tw-text-white tw-transition-all tw-duration-50 tw-bg-sky-800 hover:tw-bg-sky-700 tw-p-2 tw-rounded-full"
					style="width:7rem"
				>@lang( 'barcode.preview' )</button>
			</div>
		</div>
	@endcomponent
	{!! Form::close() !!}

	<div class="col-sm-8 hide display_label_div">
		<h3 class="box-title">@lang( 'barcode.preview' )</h3>
		<button type="button" class="col-sm-offset-2 btn btn-success btn-block" id="print_label">Print</button>
	</div>
	<div class="clearfix"></div>
</section>

<!-- Preview section-->
<div id="preview_box">
</div>

@stop
@section('javascript')
	<script src="{{ asset('js/labels.js?v=' . $asset_v) }}"></script>
@endsection
