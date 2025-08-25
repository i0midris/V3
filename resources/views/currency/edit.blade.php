@extends('layouts.app')
@section('title', __('lang_v1.edit_currency'))

@section('content')
<section class="content-header">
    <h1>@lang('lang_v1.edit_currency')</h1>
</section>

<section class="content">
    {!! Form::open(['url' => route('currency.update', $currency_rate->id), 'method' => 'post']) !!}
    <div class="box box-solid">
        <div class="box-body">
            @csrf
            <div class="form-group col-md-6">
                <label>@lang('lang_v1.currency_name')</label>
                <input type="text" name="currency_name" class="form-control" value="{{ $currency_rate->currency_name }}" required>
            </div>

            <div class="form-group col-md-6">
                <label>@lang('lang_v1.currency_code')</label>
                <input type="text" name="currency_code" class="form-control text-uppercase" value="{{ $currency_rate->currency_code }}" required>
            </div>

            <div class="form-group col-md-6">
                <label>@lang('lang_v1.exchange_rate')</label>
                <input type="number" name="exchange_rate" step="0.000001" class="form-control" value="{{ $currency_rate->exchange_rate }}" required>
            </div>

            <div class="form-group col-md-6" style="margin-top:2rem">
                <label>
                    <input type="checkbox" name="status" value="1" {{ $currency_rate->status ? 'checked' : '' }}>
                    @lang('lang_v1.active')
                </label>
            </div>

        </div>

        <div class="box-footer text-center">
            <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-text-white tw-w-24">@lang('messages.update')</button>
            <a href="{{ route('currency.index') }}" class="tw-dw-btn tw-dw-btn-neutral tw-text-white tw-w-24">@lang('messages.cancel')</a>
        </div>
    </div>
    {!! Form::close() !!}
</section>
@endsection
