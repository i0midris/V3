@extends('layouts.app')
@section('title', __('inventorymanagement::inventory.inventory'))

@section('content')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            @lang('inventorymanagement::inventory.inventory')
            <small>@lang('inventorymanagement::inventory.create_new_inventory')</small>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="box box-solid">
            <div class="box-body">
                <form method="post" action="{{url('inventorymanagement/createNewInventory')}}">
                    @csrf
                        <div class="form-group col-md-4">
                            <label>@lang("inventorymanagement::inventory.inventory_start_date")</label></br>
                            <input class="form-control tw-mt-1"  required="" name="inventory_start_date" type="date" >
                        </div>
                        <div class="form-group col-md-4">
                            <label>@lang("inventorymanagement::inventory.inventory_end_date")</label></br>
                            <input class="form-control tw-mt-1"  required="" name="inventory_end_date" type="date" >
                        </div>
                        <div class="form-group col-md-4">
                            <label>@lang("inventorymanagement::inventory.inventory_branch")</label></br>
                            <select class="form-control tw-mt-1" name="branch">
                                @foreach($branches as $branch)
                                    <option id="1" value="{{ $branch->id }}">{{$branch->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </br>
                    </br>
                    <div class="text-center row" style="margin-top: 4rem;">
                        <button type="submit" class="add-btn tw-w-24">@lang('inventorymanagement::inventory.save')</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
    <!-- /.content -->
@include('inventorymanagement::partials.mainscript')
@endsection
