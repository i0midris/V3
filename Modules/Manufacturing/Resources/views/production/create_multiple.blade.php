<style>
    .x3{
        font-size: 10px;
        color: crimson !important;
        margin-top:2px;
    }
    .x1 {
        font-size: 18px;
        text-align: center;
        width: 70%;
        margin-right: 15%;
    }
</style>
@extends('layouts.app')

@section('title', __('manufacturing::lang.add_multiple_productions'))

@section('content')
@include('manufacturing::layouts.nav')

<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">
        @lang('manufacturing::lang.add_multiple_productions')
    </h1>
</section>

<section class="content">
    {!! Form::open(['url' => action([\Modules\Manufacturing\Http\Controllers\ProductionController::class, 'storeMultiple']), 'method' => 'post', 'id' => 'multiple_production_form']) !!}

    <div class="box box-solid">
        <div class="box-body">
            <div class="form-group x1">
                <label for="default_location">@lang('purchase.business_location')</label>
                {!! Form::select('default_location', $business_locations, null, ['id' => 'default_location', 'class' => 'form-control select2']) !!}
                <small class="form-text text-muted x3">
                    قم بتحديد الفرع الافتراضي لتحميل جميع الوصفات.*
                </small>
            </div>    
            <br>
            <table class="table tw-border table-striped" id="multiple_production_table">
                <thead class="tw-text-white tw-bg-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-800">
                    <tr>
                        <th>@lang('sale.product')</th>
                        <th>@lang('lang_v1.quantity')</th>
                        <th>@lang('lang_v1.price')</th>
                        <th>@lang('manufacturing::lang.unit_cost')</th>
                        <th>@lang('purchase.business_location')</th>
                        <th>@lang('messages.action')</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <select name="productions[0][variation_id]" class="form-control variation-combo-selector" required>
                                <option value="">@lang("messages.please_select")</option>
                            </select>
                        </td>
                        <td><input type="text" name="productions[0][quantity]" class="form-control input_number quantity" value="1" required></td>
                        <td><input type="text" name="productions[0][price]" class="form-control input_number price" value="0" required></td>
                        <td class="line_total text-right">0</td>
                        <td>
                            {!! Form::select('productions[0][location_id]', $business_locations, null, ['class' => 'form-control select2', 'required']) !!}
                        </td>
                        <td>
                            <button type="button" class="btn btn-danger btn-sm remove_row">
                                <i class="fa fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                </tbody>
                <tfoot style="background-color: #e9ecef;">
                    <tr>
                        <th class="text-left">@lang('manufacturing::lang.total')</th>
                        <th class="text-left" id="total_quantity">@lang('manufacturing::lang.total_quantity') : 0</th>
                        <th class="text-left" id="total_price">@lang('manufacturing::lang.total_cost') : 0</th>
                        <th colspan="3"></th>
                    </tr>
                </tfoot>
            </table>

            <div class="tw-flex tw-gap-1">
                <button type="button" class="custom-tbtn btn-success" id="add_row">
                    <i class="fa fa-plus"></i> @lang('messages.add')
                </button>
                

                <button type="button" class="custom-tbtn btn-info" id="fetch_all_recipes">
                    <i class="fa fa-download"></i> @lang('manufacturing::lang.fetch_all_recipes')
                </button>
            </div>

            <div class="tw-mt-4 tw-flex tw-justify-center tw-gap-4">
                <button type="button" class="tw-dw-btn tw-dw-btn-neutral tw-text-white" id="btn-save-print">@lang('messages.save') & @lang('messages.print')</button>
                <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-text-white" id="btn-save">@lang('messages.save')</button>
            </div>
        </div>
    </div>


    {!! Form::close() !!}
</section>
@endsection

@php
    $recipe_options = [];
    foreach($all_recipes as $id => $name) {
        $recipe_options[] = ['id' => $id, 'text' => $name];
    }
@endphp
@section('javascript')
<script>
    let rowIndex = 1;
    const locationDropdown = @json($business_locations);
    const allRecipes = @json($recipe_options);

    function getLocationOptions(data) {
        let html = '<option value="">@lang("messages.please_select")</option>';
        for (const key in data) {
            html += `<option value="${key}">${data[key]}</option>`;
        }
        return html;
    }

    function recalculateTotals() {
        let totalQty = 0;
        let totalPrice = 0;

        $('#multiple_production_table tbody tr').each(function () {
            const qty = parseFloat($(this).find('.quantity').val()) || 0;
            const price = parseFloat($(this).find('.price').val()) || 0;
            const lineTotal = qty * price;

            $(this).find('.line_total').text(lineTotal.toFixed(2));
            totalQty += qty;
            totalPrice += lineTotal;
        });

        $('#total_quantity').text(totalQty.toFixed(2));
        $('#total_price').text(totalPrice.toFixed(2));
    }

    function initializeComboProductSelect2(context = $('body')) {
        context.find('.variation-combo-selector').select2({
            data: allRecipes,
            placeholder: '@lang("messages.please_select")',
            allowClear: true,
            width: 'resolve',
            minimumResultsForSearch: 0,
            dropdownAutoWidth: true
        }).val('').trigger('change'); // Clear default and force change
    }

    $(document).on('change', '.variation-combo-selector', function () {
        const $row = $(this).closest('tr');
        const variationId = $(this).val();

        if (variationId && $.isNumeric(variationId)) {
            $('#btn-save, #btn-save-print').prop('disabled', true);

            $.ajax({
                method: 'GET',
                url: '/manufacturing/get-recipe-price',
                data: { variation_id: variationId },
                success: function (res) {
                    $row.find('.price').val(res.unit_price);
                    recalculateTotals();
                },
                complete: function () {
                    $('#btn-save, #btn-save-print').prop('disabled', false);
                },
                error: function () {
                    alert('حدث خطأ أثناء جلب السعر');
                }
            });
        }
    });

    $('#add_row').click(function () {
        const row = $(`
            <tr>
                <td>
                    <select name="productions[${rowIndex}][variation_id]" class="form-control variation-combo-selector" required></select>
                </td>
                <td><input type="text" name="productions[${rowIndex}][quantity]" class="form-control input_number quantity" value="1" required></td>
                <td><input type="text" name="productions[${rowIndex}][price]" class="form-control input_number price" value="0" required></td>
                <td class="line_total text-right">0</td>
                <td>
                    <select name="productions[${rowIndex}][location_id]" class="form-control select2" required>
                        ${getLocationOptions(locationDropdown)}
                    </select>
                </td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm remove_row">
                        <i class="fa fa-trash"></i>
                    </button>
                </td>
            </tr>
        `);

        $('#multiple_production_table tbody').append(row);

        // Initialize select2 and product dropdown
        row.find('.select2').select2().val('').trigger('change');
        initializeComboProductSelect2(row);

        rowIndex++;
        recalculateTotals();
    });

    $(document).on('click', '.remove_row', function () {
        $(this).closest('tr').remove();
        recalculateTotals();
    });

    $(document).on('input', '.quantity, .price', recalculateTotals);

    $(document).ready(function () {
        $('.select2').select2();
        initializeComboProductSelect2();
        recalculateTotals();
    });

    let isPrintAfterSave = false;

    $('#btn-save-print').click(function () {
        isPrintAfterSave = true;
        $('#multiple_production_form').submit();
    });

    $('#btn-save').click(function () {
        isPrintAfterSave = false;
    });

    $('#multiple_production_form').submit(function (e) {
        const form = this;

        // Make sure all selects are synced
        $(form).find('select').each(function () {
            $(this).trigger('change');
        });

        if (isPrintAfterSave) {
            if (!form.checkValidity()) {
                form.reportValidity();
                return false;
            }

            e.preventDefault();

            const $form = $(form);
            const action = $form.attr('action');
            const data = $form.serialize();

            $.post(action, data, function (res) {
                if (res.success && res.print_url) {
                    window.open(res.print_url, '_blank');
                    window.location.href = "{{ action([\Modules\Manufacturing\Http\Controllers\ProductionController::class, 'index']) }}";
                } else {
                    alert(res.msg || 'حدث خطأ أثناء الحفظ');
                } 
            }).fail(function () {
                alert('فشل الاتصال بالخادم.');
            });
        }
    });

    function getLocationOptions(data, selected = '') {
    let html = '<option value="">@lang("messages.please_select")</option>';
    for (const key in data) {
        const isSelected = key == selected ? 'selected' : '';
        html += `<option value="${key}" ${isSelected}>${data[key]}</option>`;
    }
    return html;
}

$(document).on('click', '#fetch_all_recipes', function () {
    $.ajax({
        url: '{{ route("manufacturing.ajaxAllRecipes") }}',
        method: 'GET',
        success: function (recipes) {
            if (!recipes || recipes.length === 0) {
                alert('No recipes found.');
                return;
            }

            // Get selected default location
            const defaultLocationId = $('#default_location').val();

            // Clear existing rows
            $('#multiple_production_table tbody').empty();
            rowIndex = 0;

            recipes.forEach(function (item) {
                const newRow = $(`
                    <tr>
                        <td>
                            <select name="productions[${rowIndex}][variation_id]" class="form-control variation-combo-selector" required>
                                <option value="${item.id}" selected>${item.text}</option>
                            </select>
                        </td>
                        <td><input type="text" name="productions[${rowIndex}][quantity]" class="form-control input_number quantity" value="0" required></td>
                        <td><input type="text" name="productions[${rowIndex}][price]" class="form-control input_number price" value="0" required></td>
                        <td class="line_total text-right">0</td>
                        <td>
                            <select name="productions[${rowIndex}][location_id]" class="form-control select2" required>
                                ${getLocationOptions(locationDropdown, defaultLocationId)}
                            </select>
                        </td>
                        <td>
                            <button type="button" class="btn btn-danger btn-sm remove_row">
                                <i class="fa fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `);

                $('#multiple_production_table tbody').append(newRow);

                // Initialize dropdowns
                newRow.find('.select2').select2();
                newRow.find('.variation-combo-selector').select2({
                    placeholder: '@lang("messages.please_select")',
                    allowClear: true,
                    width: 'resolve',
                    minimumResultsForSearch: 0,
                    dropdownAutoWidth: true
                });

                // Trigger recipe price fetch
                newRow.find('.variation-combo-selector').trigger('change');

                rowIndex++;
            });

            recalculateTotals();
            alert(`✅ ${recipes.length} rows added from all recipes.`);
        },
        error: function () {
            alert('❌ Failed to load recipes from server.');
        }
    });
});


</script>
@endsection
