@extends('layouts.app')
@section('title', __('user.roles'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang( 'user.roles' )
        <small  class="tw-text-sm md:tw-text-base">@lang( 'user.manage_roles' )</small>
    </h1>
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
</section>

<!-- Main content -->
<section class="content">
    @component('components.widget', ['class' => 'box-primary', 'title' => __( 'user.all_roles' )])
        @can('roles.create')
            @slot('tool')
                <div class="box-tools" style="margin: 0 1rem;">
                    <a 
                        class="tw-inline-flex tw-transition-all hover:tw-text-white tw-cursor-pointer tw-duration-50 tw-py-2 tw-px-4 tw-rounded-full tw-items-center tw-justify-center tw-text-sm tw-font-medium tw-text-white tw-gap-1 tw-bg-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-800 hover:tw-bg-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-700 " 
                        href="{{action([\App\Http\Controllers\RoleController::class, 'create'])}}"
                    >
                        <svg  xmlns="http://www.w3.org/2000/svg" class="tw-size-5" viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="3"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-plus"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>
                        @lang('messages.add')
                    </a>
                </div>
            @endslot
        @endcan
        @can('roles.view')
            <table class="table table-striped tw-rounded-lg tw-border tw-overflow-hidden" id="roles_table">
                <thead class="tw-text-white tw-bg-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-800">
                    <tr>
                        <th>@lang( 'user.roles' )</th>
                        <th>@lang( 'messages.action' )</th>
                    </tr>
                </thead>
            </table>
        @endcan
    @endcomponent

</section>
<!-- /.content -->
@stop
@section('javascript')
<script type="text/javascript">
    //Roles table
    $(document).ready( function(){
        var roles_table = $('#roles_table').DataTable({
                    processing: true,
                    serverSide: true,
                    fixedHeader:false,
                    dom: `<"tw-mb-4 tw-flex tw-flex-wrap tw-justify-between tw-items-center tw-gap-4"lf>rt<"tw-mt-4 tw-flex tw-justify-between tw-items-center"ip>`,
                    ajax: '/roles',
                    buttons:[],
                    columnDefs: [ {
                        "targets": 1,
                        "orderable": false,
                        "searchable": false
                    } ]
                });
        $(document).on('click', 'button.delete_role_button', function(){
            swal({
              title: LANG.sure,
              text: LANG.confirm_delete_role,
              icon: "warning",
              buttons: true,
              dangerMode: true,
            }).then((willDelete) => {
                if (willDelete) {
                    var href = $(this).data('href');
                    var data = $(this).serialize();

                    $.ajax({
                        method: "DELETE",
                        url: href,
                        dataType: "json",
                        data: data,
                        success: function(result){
                            if(result.success == true){
                                toastr.success(result.msg);
                                roles_table.ajax.reload();
                            } else {
                                toastr.error(result.msg);
                            }
                        }
                    });
                }
            });
        });
    });
</script>
@endsection
