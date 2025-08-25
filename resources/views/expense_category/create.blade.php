<div class="modal-dialog" role="document">
  <div class="modal-content">

    {!! Form::open(['url' => action([\App\Http\Controllers\ExpenseCategoryController::class, 'store']), 'method' => 'post', 'id' => 'expense_category_add_form' ]) !!}
    
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
      <h4 class="modal-title">@lang('expense.add_expense_category')</h4>
    </div>

    <div class="modal-body">
      
      {{-- Category Name --}}
      <div class="form-group">
        {!! Form::label('name', __('expense.category_name') . ':*') !!}
        {!! Form::text('name', null, ['class' => 'form-control', 'required', 'placeholder' => __('expense.category_name')]) !!}
      </div>

      {{-- Account Sub-Type Selection --}}
<div class="form-group"> 
  {!! Form::label('account_sub_type_id', __('expense.account_sub_type') . ':*') !!}
  {!! Form::select('account_sub_type_id', [
      51 => __('expense.Cost_of_Goods_Sold'),
      52 => __('expense.Operating_Expenses'),
      53 => __('expense.Other_Expenses')
  ], 52, ['class' => 'form-control', 'required']) !!}
</div>


      {{-- Optional Subcategory Toggle --}}
      <div class="form-group">
        <div class="checkbox">
          <label>
            {!! Form::checkbox('add_as_sub_cat', 1, false, [ 'class' => 'toggler', 'data-toggle_id' => 'parent_cat_div' ]) !!}
            @lang('lang_v1.add_as_sub_cat')
          </label>
        </div> 
      </div>

      {{-- Parent Category Dropdown (if subcategory) --}}
      <div class="form-group hide" id="parent_cat_div">
        {!! Form::label('parent_id', __('category.select_parent_category') . ':') !!}
        {!! Form::select('parent_id', $categories, null, ['class' => 'form-control', 'placeholder' => __('lang_v1.none')]) !!}
      </div>

    </div>

    <div class="modal-footer">
      <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-text-white">@lang('messages.save')</button>
      <button type="button" class="tw-dw-btn tw-dw-btn-neutral tw-text-white" data-dismiss="modal">@lang('messages.close')</button>
    </div>

    {!! Form::close() !!}

  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
