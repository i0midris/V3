@extends('layouts.app')
@section('title', __('inventorymanagement::inventory.inventory'))
@section('css')
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/dt-1.13.1/datatables.min.css"/>
    <style>
        .table-btn{
            padding: 0.4rem !important;
            border-radius: 0.25rem;
            font-weight: bolder;
        }
        .custom-badge{
            font-size: 10px !important;
            border-radius: 999px;
            padding: 0.5rem 0.8rem !important;
        }
    </style>

@endsection
@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('inventorymanagement::inventory.stock_inventory')</h1>
    </section>

    <!-- Main content -->
    <section class="content">

        <div class="box box-solid">
            <div class="box-header" >
                <h3 class="box-title">@lang("inventorymanagement::inventory.show_stock_inventory")</h3>
            </div>
            <div class="box-body">
                <table id="example" class="table tw-border table-striped nowrap" style="width:100%;border-bottom-color:#ddd !important;">
                    <thead class="tw-text-white !tw-text-sm tw-bg-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-800">
                        <tr>
                            <th  style="text-align: right">@lang("inventorymanagement::inventory.operation_number")</th>
                            <th  style="text-align: right">@lang("inventorymanagement::inventory.inventory_start_date")</th>
                            <th  style="text-align: right">@lang("inventorymanagement::inventory.inventory_end_date")</th>
                            <th  style="text-align: right">@lang("inventorymanagement::inventory.status")</th>
                            <th  style="text-align: right">@lang("inventorymanagement::inventory.branch")</th>
                            <th  style="text-align: right">@lang("inventorymanagement::inventory.options")</th>
                        </tr>
                    </thead>
                    <tbody style="font-size: 0.7rem !important;">
        
                       @foreach ($inventories as $inventory)
                          <tr>
                             <td>{{$inventory->id}}</td>
                             <td>{{$inventory->created_at}}</td>
                             <td>{{$inventory->end_date}}</td>
                             <td>
                                 @if($inventory->status == 1)
                                     <span class="badge bg-green custom-badge"><span class="mx-2"><i class="fa fa-lock-open"></i></span>  @lang("inventorymanagement::inventory.opened")</span>
                                 @else
                                     <span class="badge bg-red custom-badge"><i class="fa fa-lock"></i>  @lang("inventorymanagement::inventory.closed")</span>
                                 @endif
                             </td>
                             <td>{{$inventory->branch->name}} ( {{$inventory->branch->location_id}} )</td>
                             <td>
                                 @if($inventory->status == 1)
                                    <a href="{{ url('inventorymanagement/makeInventory/' . $inventory->id) }}">
                                        <button class="table-btn btn-primary" >
                                            @lang("inventorymanagement::inventory.inve")
                                        </button>
                                    </a>
                                 @endif
                                 <a href="{{ url('inventorymanagement/showInventoryReports/' . $inventory->id . '/' . $inventory->branch_id) }}" >
                                     <button class="table-btn btn-primary">@lang('inventorymanagement::inventory.reports')</button>
                                 </a>
                                 <a href="{{ url('inventorymanagement/inventoryIncreaseReports/' . $inventory->id . '/' . $inventory->branch_id) }}">
                                    <button class="table-btn btn-primary">@lang("inventorymanagement::inventory.products_reports_increase")</button>
                                 </a>
                                 <!-- <a href="{{url("inventorymanagement/inventoryDisabilityReports")."/".$inventory->id."/".$inventory->branch_id}}" > -->
                                 <a href="{{ url('inventorymanagement/inventoryDisabilityReports/' . $inventory->id . '/' . $inventory->branch_id) }}" >
                                    <button class="table-btn btn-primary">@lang("inventorymanagement::inventory.products_reports_decrease")</button>
                                 </a>
                                 @if($inventory->status == 1)
                                    <button class="table-btn btn-danger inventory_change_status_btn" data-status="0" data-inventory-id="{{$inventory->id}}">@lang("inventorymanagement::inventory.inv_btn_close")</button>
                                 @else
                                    <button class="table-btn btn-success inventory_change_status_btn" data-status="1" data-inventory-id="{{$inventory->id}}">@lang("inventorymanagement::inventory.inv_btn_open")</button>
                                 @endif
                              </td>
                          </tr>
                       @endforeach
                       </tbody>
                </table>
            </div>
        </div>

        <!-- <table cellspacing="5" cellpadding="5" border="0">
                <tbody>

               </tbody></table> -->
    </section>
    <!-- /.content -->

@endsection

@section('javascript')
    <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.2/moment.min.js"></script>
    <script src="https://cdn.datatables.net/datetime/1.2.0/js/dataTables.dateTime.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            $('#example').DataTable({
                dom: `
                    <"tw-mt-4 dt-section-toolbar tw-mb-4 tw-flex tw-flex-wrap tw-justify-between tw-items-center tw-gap-2"
                        <"drps-section tw-flex tw-gap-2 tw-items-center"
                            l
                        >
                        f
                    >
                    rt
                    <"tw-mt-4 tw-flex tw-justify-between tw-items-center"ip>
                `,
                columnDefs: [
                    {
                        targets: [0],
                        orderData: [0, 1],
                    },
                    {
                        targets: [1],
                        orderData: [1, 0],
                    },
                    {
                        targets: [4],
                        orderData: [4, 0],
                    },
                ],
                order: [[0, 'desc']]
            });
            
            $('.inventory_change_status_btn').click(function (e){
                e.preventDefault();
                let status = $(this).data('status');
                let invenetory_id = $(this).data('inventory-id');
                console.log(status,invenetory_id);
                $.ajax({
                    url:`{{ url('/inventorymanagement/update/status') }}/${invenetory_id}`,
                    data:{'new_status':status},
                    method:'PUT',
                    success:function(res){
                        if(res.status){
                            location.reload()
                        }
                    },
                    error:function (errs){
                        console.error(errs);
                    }
                });
            })
        });
        
        
    </script>

@endsection
