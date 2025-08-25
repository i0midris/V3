@extends('layouts.app')
@section('title', __('expense.expense_categories'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang( 'expense.expense_categories' )
        <small  class="tw-text-sm md:tw-text-base tw-text-gray-700 tw-font-semibold">@lang( 'expense.manage_your_expense_categories' )</small>
    </h1>
</section>

<!-- Main content -->
<section class="content">
    @component('components.widget', ['class' => 'box-primary', 'title' => __( 'expense.all_your_expense_categories' )])
        @slot('tool')
            <div class="box-tools">
                
                <a 
                    class="btn-modal pull-right add-btn tw-gap-1"
                    data-href="{{action([\App\Http\Controllers\ExpenseCategoryController::class, 'create'])}}" 
                    data-container=".expense_category_modal"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="3" stroke-linecap="round" stroke-linejoin="round"
                        class="icon icon-tabler icons-tabler-outline icon-tabler-plus">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M12 5l0 14" />
                        <path d="M5 12l14 0" />
                    </svg> @lang('messages.add')
                </a>
            </div>
        @endslot
        <div class="table-responsive">
            <table class="table tw-border table-striped" id="expense_category_table">
                <thead class="tw-text-white tw-bg-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-800">
                    <tr>
                        <th>@lang( 'expense.category_name' )</th>
                        <th>@lang( 'expense.category_code' )</th>
                        <th>@lang( 'messages.action' )</th>
                    </tr>
                </thead>
            </table>
        </div>
    @endcomponent

    <div class="modal fade expense_category_modal" tabindex="-1" role="dialog" 
    	aria-labelledby="gridSystemModalLabel">
    </div>

</section>
<!-- /.content -->

@endsection
