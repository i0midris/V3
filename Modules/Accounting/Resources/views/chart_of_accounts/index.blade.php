@extends('layouts.app')

@section('title', __('accounting::lang.chart_of_accounts'))

@section('content')
@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.2.1/themes/default/style.min.css" />
@endsection

@include('accounting::layouts.nav')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang( 'accounting::lang.chart_of_accounts' )</h1>
</section>
<section class="content">
    <div class="row mb-12">
        <div class="col-md-12">
            <div class="btn-group btn-group-toggle" data-toggle="buttons">
                <label class="btn btn-info active">
                    <input type="radio" name="view_type" value="tree" class="view_type">
                    <i class="fas fa-list-ul"></i> @lang('accounting::lang.tree_view')
                </label>
                <label class="btn btn-info">
                    <input type="radio" name="view_type" value="table" class="view_type">
                    <i class="fas fa-table"></i> @lang('accounting::lang.tabular_view')
                </label>
                
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            @component('components.widget', ['class' => 'box-solid'])
            @slot('tool')
                <div class="box-tools">
                    @if(\Modules\Accounting\Utils\AccountingUtil::checkTreeOfAccountsIsHere())
                    <a class="btn btn-primary pull-right m-5 btn-modal"
                       onclick="on_create = true;"
                    href="{{action([\Modules\Accounting\Http\Controllers\CoaController::class, 'create'])}}" 
                    data-href="{{action([\Modules\Accounting\Http\Controllers\CoaController::class, 'create'])}}" 
                    data-container="#create_account_modal">
                    <i class="fas fa-plus"></i> @lang( 'messages.add' )</a>
                    @endif
                </div>
            @endslot
                <div id="accounts_tree"></div>
                <div id="tabular_view" class="hide">
                    <div class="row">
                        <div class="col-md-12">
                            @component('components.filters', ['title' => __('report.filters')])
                                <div class="col-md-4">
                                    <div class="form-group">
                                        {!! Form::label('account_type_filter', __( 'accounting::lang.account_type' ) . ':') !!}
                                        {!! Form::select('account_type_filter', $account_types, null,
                                            ['class' => 'form-control select2', 'style' => 'width:100%', 
                                            'id' => 'account_type_filter', 'placeholder' => __('lang_v1.all')]); !!}
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        {!! Form::label('status_filter', __( 'sale.status' ) . ':') !!}
                                        {!! Form::select('status_filter', ['active' => __( 'accounting::lang.active' ),
                                            'inactive' => __('accounting::lang.inactive')], null,
                                            ['class' => 'form-control select2', 'style' => 'width:100%', 
                                            'id' => 'status_filter', 'placeholder' => __('lang_v1.all')]); !!}
                                    </div>
                                </div>
                            @endcomponent
                        </div>
                    </div>
                    <div class="table-responsive" id="accounts_table">
                    </div>
                </div>
            @endcomponent
        </div>
    </div>
</section>
<div class="modal fade" id="create_account_modal" tabindex="-1" role="dialog">
</div>
<div class="modal fade" id="edit_link_modal" tabindex="-1" role="dialog">
</div>
@stop
@section('javascript')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.2.1/jstree.min.js"></script>
<script type="text/javascript">
	$(document).ready( function(){
        load_accounts_table();
        load_accounts_table('tree');
	});

    $(document).on('change', '#account_type_filter, #status_filter', function(){
        load_accounts_table();
    });

    $('input[type=radio][name=view_type]').change(function() {
        if (this.value == 'tree') {
            $('#accounts_tree').removeClass('hide');
            $('#tabular_view').addClass('hide');
        }
        else if (this.value == 'table') {
            $('#accounts_tree').addClass('hide');
            $('#tabular_view').removeClass('hide');
        }
    });

    function load_accounts_table(view_type='table'){
        var data = {view_type: view_type};

        if($('#account_type_filter').val()!== ''){
            data.account_type = $('#account_type_filter').val();
        }
        if($('#status_filter').val()!== ''){
            data.status = $('#status_filter').val();
        }
        $.ajax({
            url: '/accounting/chart-of-accounts',
            data: data,
            dataType: 'html',
            success: function(html) {
                if(view_type=='table') {
                    $('#accounts_table').html(html);
                } else {
                    $('#accounts_tree').html(html);

                    $.jstree.defaults.core.themes.variant = "large";
                    $('#accounts_tree_container').jstree({
                        "core" : {
                            "themes" : {
                                "responsive": true
                            }
                        },
                        "types" : {
                            "default" : {
                                "icon" : "fa fa-folder"
                            },
                            "file" : {
                                "icon" : "fa fa-file"
                            },
                        },
                        "plugins": ["types", "search"]
                    });

                    var to = false;
                    $('#accounts_tree_search').keyup(function () {
                        if(to) { clearTimeout(to); }
                        to = setTimeout(function () {
                        var v = $('#accounts_tree_search').val();
                        $('#accounts_tree_container').jstree(true).search(v);
                        }, 250);
                    });
                }
            },
        });
    };
    $(document).on('click', '#expand_all', function(e){
        $('#accounts_tree_container').jstree("open_all");
        on_create = false
    })
    $(document).on('click', '#collapse_all', function(e){
        $('#accounts_tree_container').jstree("close_all");
        on_create = false
    })

    $(document).on('shown.bs.modal', '#create_account_modal', function(){
        $(this).find('#account_sub_type').select2({
            dropdownParent: $('#create_account_modal')
        });
        $(this).find('#detail_type').select2({
            dropdownParent: $('#create_account_modal')
        });
        $(this).find('#parent_account').select2({
            dropdownParent: $('#create_account_modal')
        });
        $('#as_of').datepicker({
            autoclose: true,
            endDate: 'today',
        });
        init_tinymce('description');
    });

    $(document).on('hidden.bs.modal', '#create_account_modal', function(){
        tinymce.remove("#description");
    });
    $(document).on('change', '#account_primary_type', function(){
        if($(this).val() !== '') {
            $.ajax({
                url: '/accounting/get-account-sub-types?account_primary_type=' + $(this).val(),
                dataType: 'json',
                success: function(result) {
                    $('#account_sub_type').select2('destroy')
                        .empty()
                        .select2({
                            data: result.sub_types,
                            dropdownParent: $('#create_account_modal'),
                        }).on('change', function() {
                            if($(this).select2('data')[0].show_balance==1) {
                                $('#bal_div').removeClass('hide');
                            } else {
                                $('#bal_div').addClass('hide');
                            }
                        });
                        $('#account_sub_type').change();
                },
            });
        }
    });
    $(document).on('change', '#account_sub_type', function(){
        if($(this).val() !== '') {
            $.ajax({
                url: '/accounting/get-account-details-types?account_type_id=' + $(this).val(),
                dataType: 'json',
                success: function(result) {
                    $('#detail_type').select2('destroy')
                            .empty()
                            .select2({
                                data: result.detail_types,
                                dropdownParent: $('#create_account_modal'),
                            }).on('change', function() {
                                if($(this).val() !== '') {
                                    var desc = $(this).select2('data')[0].description;
                                    $('#detail_type_desc').html(desc);
                                }
                            });
                        $('#parent_account').select2('destroy')
                        .empty()
                        .select2({
                            data: result.parent_accounts,
                            dropdownParent: $('#create_account_modal'),
                        });
                },
            });
        }
    })

    $(document).on('click', 'a.activate-deactivate-btn', function(e) {
        e.preventDefault();
        $.ajax({
            url: $(this).attr('href'),
            dataType: 'json',
            success: function(response) {
                toastr.success(response.msg);
                load_accounts_table();
                load_accounts_table('tree');
            },
        });
    })
    $(document).on('click', 'a.ledger-link', function(e) {
        window.location.href = $(this).attr('href');
    });

       function doNow() {

           // if ($("#detail_type option:selected").val() == "null") {
           //
           //     return false;
           // }

           // if ($("#parent_account option:selected").val() == "null") {
           //
           //     return false;
           // }

           if ($("#gl_code").val() == "") {

               return false;
           }

           return true;

       }
</script>
<script>

    var on_create = false;

    setInterval(function () {

        if(on_create)
        {
            $('#accounts_tree_container').jstree("open_all");

            var parent_account = $('#parent_account option:selected').val();

            var account_sub_type = $('#account_sub_type option:selected').val();

            if(typeof parent_account !== "undefined" && parent_account != "" && parent_account != "null")
            {

                var $tree = $("#accounts_tree_container");

                $($tree.jstree().get_json($tree, {
                    flat: true
                }))
                    .each(function(index, value) {

                        if(typeof $("#"+this.id).attr("real_id") !== "undefined")
                        {

                            if($("#"+this.id).attr("real_id") == parent_account)
                            {

                                var nodee = $("#accounts_tree_container").jstree().get_node(this.id);

                                if(nodee.children.length == 0)
                                {


                                    $("#gl_code").val(
                                        $("#"+nodee.id).attr("gl_code") + Number.padLeft(1)
                                    );



                                }
                                else
                                {

                                    $("#gl_code").val(
                                        $("#" + nodee.id).attr("gl_code") + Number.padLeft(nodee.children.length+1)
                                    );

                                }
                            }
                        }

                    });


            }
            else
            if(typeof account_sub_type !== "undefined")
            {

                var $tree = $("#accounts_tree_container");

                $($tree.jstree().get_json($tree, {
                    flat: true
                }))
                    .each(function(index, value) {

                        if(typeof $("#"+this.id).attr("gl_code") !== "undefined")
                        {
                            if($("#"+this.id).attr("gl_code") == account_sub_type)
                            {
                                var nodee = $("#accounts_tree_container").jstree().get_node(this.id);

                                if(nodee.children.length == 0)
                                {


                                        $("#gl_code").val(
                                            $("#"+nodee.id).attr("gl_code") + Number.padLeft(1)
                                        );



                                }
                                else
                                {

                                        $("#gl_code").val(
                                            $("#" + nodee.id).attr("gl_code") + Number.padLeft(nodee.children.length+1)
                                        );

                                }
                            }
                        }

                    });


            }

        }

    }, 5000);

    $("#accounts_tree_container").on(
        "select_node.jstree", function(evt, data){

        }
    );

    Number.padLeft = (nr, len = 2, padChr = `0`) =>
        `${nr < 0 ? `-` : ``}${`${Math.abs(nr)}`.padStart(len, padChr)}`;
</script>
@endsection