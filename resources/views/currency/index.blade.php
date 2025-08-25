<!-- resources\views\currency\index.blade.php -->
@extends('layouts.app')
@section('title', __('lang_v1.manage_currency_rates'))

@section('content')
<section class="content-header">
    <h1 class="tw-text-2xl font-bold">@lang('lang_v1.manage_currency_rates')</h1>
</section>

<section class="content">
    @if(session('status'))
        <div class="alert alert-success">
            {{ session('status')['msg'] ?? session('status') }}
        </div>
    @endif

    <div class="box box-solid">
        <div class="box-header">
            <h3 class="box-title">@lang('lang_v1.add_currency_rate')</h3>
        </div>
        <div class="box-body">
            <form method="POST" action="{{ route('currency.store') }}">
                @csrf
                <div class="row">
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label>@lang('lang_v1.currency_name')</label>
                            <input type="text" name="currency_name" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label>@lang('lang_v1.currency_code')</label>
                            <input type="text" name="currency_code" class="form-control text-uppercase" required>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label>@lang('lang_v1.exchange_rate')</label>
                            <input type="number" name="exchange_rate" step="0.000001" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-sm-2" style="margin-top:2rem">
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="status" value="1">
                                @lang('lang_v1.active')
                            </label>
                        </div>
                    </div>
                    <div class="col-sm-2" style="margin-top:1.4rem">
                        <div class="form-group mt-4">
                            <button type="submit" class="add-btn tw-w-24">
                                @lang('messages.add')
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($currency_rates->count())
        <div class="box box-solid mt-3">
            <div class="box-header">
                <h3 class="box-title">@lang('lang_v1.all_currency_rates')</h3>
            </div>
            <div class="box-body">
                <table class="table tw-border">
                    <thead  class="tw-text-white tw-bg-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-800">
                        <tr>
                            <th>@lang('lang_v1.currency_name')</th>
                            <th>@lang('lang_v1.currency_code')</th>
                            <th>@lang('lang_v1.exchange_rate')</th>
                            <th>@lang('lang_v1.status')</th>
                            <th>@lang('messages.action')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($currency_rates as $rate)
                            <tr>
                                <td>{{ $rate->currency_name }}</td>
                                <td>{{ $rate->currency_code }}</td>
                                <td>{{ $rate->exchange_rate }}</td>
                                <td>
                                    <span class="label label-{{ $rate->status ? 'success' : 'danger' }}" style="padding:0.25rem">
                                        {{ $rate->status ? __('lang_v1.active') : __('lang_v1.inactive') }}
                                    </span>
                                </td>
                                <td>
                                    {{-- Update form --}}
                                    <form method="POST" action="{{ url('/currency/update/' . $rate->id) }}" class="inline-form" style="display: inline-block;">
                                        @csrf
                                        <input type="hidden" name="currency_name" value="{{ $rate->currency_name }}">
                                        <input type="hidden" name="currency_code" value="{{ $rate->currency_code }}">
                                        <input type="hidden" name="exchange_rate" value="{{ $rate->exchange_rate }}">
                                        <input type="hidden" name="status" value="{{ $rate->status }}">
                                        <a href="{{ url('/currency/edit/' . $rate->id) }}"
                                            class="btn btn-xs btn-warning" style="padding:0.25rem !important" title="@lang('messages.edit')">
                                            <!-- <i class="fa fa-edit"></i> -->
                                             <svg  xmlns="http://www.w3.org/2000/svg"  width="16"  height="16"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-pencil"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 20h4l10.5 -10.5a2.828 2.828 0 1 0 -4 -4l-10.5 10.5v4" /><path d="M13.5 6.5l4 4" /></svg>
                                        </a>

                                    </form>

                                    {{-- Delete link --}}
                                    <a href="{{ url('/currency/delete/' . $rate->id) }}" 
                                       onclick="return confirm('{{ __('messages.are_you_sure') }}');"
                                       class="btn btn-xs btn-danger" style="padding:0.25rem !important" title="@lang('messages.delete')">
                                        <!-- <i class="fa fa-trash"></i> -->
                                         <svg  xmlns="http://www.w3.org/2000/svg"  width="16"  height="16"  viewBox="0 0 24 24"  fill="currentColor"  class="icon icon-tabler icons-tabler-filled icon-tabler-trash"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M20 6a1 1 0 0 1 .117 1.993l-.117 .007h-.081l-.919 11a3 3 0 0 1 -2.824 2.995l-.176 .005h-8c-1.598 0 -2.904 -1.249 -2.992 -2.75l-.005 -.167l-.923 -11.083h-.08a1 1 0 0 1 -.117 -1.993l.117 -.007h16z" /><path d="M14 2a2 2 0 0 1 2 2a1 1 0 0 1 -1.993 .117l-.007 -.117h-4l-.007 .117a1 1 0 0 1 -1.993 -.117a2 2 0 0 1 1.85 -1.995l.15 -.005h4z" /></svg>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</section>
@endsection
