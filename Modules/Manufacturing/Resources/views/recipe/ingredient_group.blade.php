<div class="box box-solid">
	<div class="box-body">
		<div class="row">
			<div class="col-md-12">
				<button class="tw-dw-btn tw-dw-btn-error tw-dw-btn-outline tw-dw-btn-xs pull-right remove_ingredient_group">
					<svg  xmlns="http://www.w3.org/2000/svg"  width="16"  height="16"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="3"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-x"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M18 6l-12 12" /><path d="M6 6l12 12" /></svg>
				</button>
			</div>
		</div>
		<div class="row">
			<div class="col-md-4">
				<div class="form-group">
					{!! Form::label('ingredient_group' . $ig_index, __('manufacturing::lang.ingredient_group').':') !!}

					{!! Form::text('ingredient_groups[' . $ig_index . ']', !empty($ig_name) ? $ig_name : null, ['class' => 'form-control ingredient_group', 'id' => 'ingredient_group' . $ig_index, 'placeholder' => __('manufacturing::lang.ingredient_group'), 'data-ig_index' => $ig_index , 'required']); !!}
				</div>
			</div>
			<div class="col-md-4">
				<div class="form-group">
					{!! Form::label('ingredient_group_description' . $ig_index, __('lang_v1.description').':') !!}

					{!! Form::textarea('ingredient_group_description[' . $ig_index . ']', !empty($ig_description) ? $ig_description : null, ['class' => 'form-control', 'id' => 'ingredient_group_description' . $ig_index, 'placeholder' => __('lang_v1.description'), 'rows' => 2]); !!}
				</div>
			</div>
			<div class="col-md-4">
				<div class="form-group">
					{!! Form::label('search_product_' . $ig_index, __('manufacturing::lang.select_ingredient').':') !!}

					{!! Form::text('search_product', null, ['class' => 'form-control search_product', 'placeholder' => __('manufacturing::lang.select_ingredient'), 'id' => 'search_product_' . $ig_index]); !!}
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<table class="table table-striped tw-border text-center ingredients_table">
					<thead class="tw-text-white tw-bg-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-800">
						<tr>
							<th>@lang('manufacturing::lang.ingredient')</th>
							<th>@lang('manufacturing::lang.waste_percent')</th>
							<th>@lang('manufacturing::lang.final_quantity')</th>
							<th>@lang('lang_v1.price')</th>
							<th>&nbsp;</th>
						</tr>
					</thead>
					<tbody class="ingredient-row-sortable">
						@if(!empty($ingredients))
							@foreach($ingredients as $ingredient)
								@include('manufacturing::recipe.ingredient_row', ['ingredient' => (object) $ingredient, 'ig_index' => $ig_index])
								
								@php
									$row_index++;
								@endphp
							@endforeach
						@endif
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div> <!--box end-->