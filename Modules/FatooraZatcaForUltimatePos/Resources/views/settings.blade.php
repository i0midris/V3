@extends('layouts.app')
@section('title', __('fatoorazatcaforultimatepos::lang.zatca_settings'))

@section('content')

{{-- Zatca Settings --}}
<section class="content-header">
    <h1>{{ __('fatoorazatcaforultimatepos::lang.zatca_settings')}}</h1>
</section>

<section class="content">
    {!! Form::open(['url' => action([\Modules\FatooraZatcaForUltimatePos\Http\Controllers\SettingsController::class, 'store']), 'method' => 'post' ]) !!}

    <div class="row">
        <div class="col-md-12">
            @component('components.widget', ['class' => 'box-primary'])
                @include('fatoorazatcaforultimatepos::settings_fields')
            @endcomponent
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 text-center">
            <button type="submit" class="btn btn-danger btn-big">@lang('messages.save')</button>
        </div>
    </div>
    
    {!! Form::close() !!}

</section>

{{-- Zatca Verify --}}
<section class="content-header">
    <h1>
        {{ __('fatoorazatcaforultimatepos::lang.zatca_verify')}} 
        @if ($zatca_verified)
            <span class="badge alert-success">{{ __('fatoorazatcaforultimatepos::lang.verified')}} </span>
        @else
            <span class="badge alert-danger">{{ __('fatoorazatcaforultimatepos::lang.not_verified')}} </span>
        @endif
    </h1>
</section>

<section class="content">
    {!! Form::open(['url' => action([\Modules\FatooraZatcaForUltimatePos\Http\Controllers\VerifyController::class, 'store']), 'method' => 'post' ]) !!}

    <div class="row">
        <div class="col-md-12">
            @component('components.widget', ['class' => 'box-primary'])
            <div class="col-sm-6">
                <div class="form-group">
                    {!! Form::label('otp', __('fatoorazatcaforultimatepos::lang.otp_name') . ':') !!}
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-info"></i>
                        </span>
                        {!! Form::text('otp', '', ['class' => 'form-control','placeholder' => 'xxxxxx', 'disabled' => $business->is_zatca_verified]); !!}
                    </div>
                </div>
            </div>
            @endcomponent
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 text-center">
            <button type="submit" class="btn btn-danger btn-big">@lang('fatoorazatcaforultimatepos::lang.verify')</button>
        </div>
    </div>
    
    {!! Form::close() !!}

</section>

@stop