<div class="row">
    <div class="col-sm-6">
        <div class="form-group">
            {!! Form::label('environment', __('fatoorazatcaforultimatepos::lang.environment') . ':') !!}
            @error('environment')
                <span class="error">{{ $message }}</span>
            @enderror
            <div class="input-group">
                <span class="input-group-addon">
                    <i class="fa fa-info"></i>
                </span>
                {!! Form::select('environment', $environments, $zatca_fields['environment'] ?? null, ['class' => 'form-control']) !!}
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="form-group">
            {!! Form::label('common_name', __('fatoorazatcaforultimatepos::lang.common_name_name') . ':') !!}
            @error('common_name')
                <span class="error">{{ $message }}</span>
            @enderror
            <div class="input-group">
                <span class="input-group-addon">
                    <i class="fa fa-info"></i>
                </span>
                {!! Form::text('common_name', $zatca_fields['common_name'] ?? null, ['class' => 'form-control','placeholder' => __('fatoorazatcaforultimatepos::lang.common_name_placeholder')]); !!}
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="form-group">
            {!! Form::label('organization_name', __('fatoorazatcaforultimatepos::lang.organization_name_name') . ':') !!}
            @error('organization_name')
                <span class="error">{{ $message }}</span>
            @enderror
            <div class="input-group">
                <span class="input-group-addon">
                    <i class="fa fa-info"></i>
                </span>
                {!! Form::text('organization_name', $zatca_fields['organization_name'] ?? null, ['class' => 'form-control','placeholder' => __('fatoorazatcaforultimatepos::lang.organization_name_placeholder')]); !!}
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="form-group">
            {!! Form::label('organizational_unit_name', __('fatoorazatcaforultimatepos::lang.organizational_unit_name_name') . ':') !!}
            @error('organizational_unit_name')
                <span class="error">{{ $message }}</span>
            @enderror
            <div class="input-group">
                <span class="input-group-addon">
                    <i class="fa fa-info"></i>
                </span>
                {!! Form::text('organizational_unit_name', $zatca_fields['organizational_unit_name'] ?? null, ['class' => 'form-control','placeholder' => __('fatoorazatcaforultimatepos::lang.organizational_unit_name_placeholder')]); !!}
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="form-group">
            {!! Form::label('tax_number', __('fatoorazatcaforultimatepos::lang.tax_number_name') . ':') !!}
            @error('tax_number')
                <span class="error">{{ $message }}</span>
            @enderror
            <div class="input-group">
                <span class="input-group-addon">
                    <i class="fa fa-info"></i>
                </span>
                {!! Form::text('tax_number', $zatca_fields['tax_number'] ?? null, ['class' => 'form-control','placeholder' => __('fatoorazatcaforultimatepos::lang.tax_number_placeholder')]); !!}
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="form-group">
            {!! Form::label('business_category', __('fatoorazatcaforultimatepos::lang.business_category_name') . ':') !!}
            @error('business_category')
                <span class="error">{{ $message }}</span>
            @enderror
            <div class="input-group">
                <span class="input-group-addon">
                    <i class="fa fa-info"></i>
                </span>
                {!! Form::text('business_category', $zatca_fields['business_category'] ?? null, ['class' => 'form-control','placeholder' => __('fatoorazatcaforultimatepos::lang.business_category_placeholder')]); !!}
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="form-group">
            {!! Form::label('egs_serial_number', __('fatoorazatcaforultimatepos::lang.egs_serial_number_name') . ':') !!}
            @error('egs_serial_number')
                <span class="error">{{ $message }}</span>
            @enderror
            <div class="input-group">
                <span class="input-group-addon">
                    <i class="fa fa-info"></i>
                </span>
                {!! Form::text('egs_serial_number', $zatca_fields['egs_serial_number'] ?? null, ['class' => 'form-control','placeholder' => __('fatoorazatcaforultimatepos::lang.egs_serial_number_placeholder')]); !!}
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="form-group">
            {!! Form::label('registration_number', __('fatoorazatcaforultimatepos::lang.registration_number_name') . ':') !!}
            @error('registration_number')
                <span class="error">{{ $message }}</span>
            @enderror
            <div class="input-group">
                <span class="input-group-addon">
                    <i class="fa fa-info"></i>
                </span>
                {!! Form::text('registration_number', $zatca_fields['registration_number'] ?? null, ['class' => 'form-control','placeholder' => __('fatoorazatcaforultimatepos::lang.registration_number_placeholder')]); !!}
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="form-group">
            {!! Form::label('registered_address', __('fatoorazatcaforultimatepos::lang.registered_address_name') . ':') !!}
            @error('registered_address')
                <span class="error">{{ $message }}</span>
            @enderror
            <div class="input-group">
                <span class="input-group-addon">
                    <i class="fa fa-info"></i>
                </span>
                {!! Form::text('registered_address', $zatca_fields['registered_address'] ?? null, ['class' => 'form-control','placeholder' => __('fatoorazatcaforultimatepos::lang.registered_address_placeholder')]); !!}
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="form-group">
            {!! Form::label('street_name', __('fatoorazatcaforultimatepos::lang.street_name_name') . ':') !!}
            @error('street_name')
                <span class="error">{{ $message }}</span>
            @enderror
            <div class="input-group">
                <span class="input-group-addon">
                    <i class="fa fa-info"></i>
                </span>
                {!! Form::text('street_name', $zatca_fields['street_name'] ?? null, ['class' => 'form-control','placeholder' => __('fatoorazatcaforultimatepos::lang.street_name_placeholder')]); !!}
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="form-group">
            {!! Form::label('building_number', __('fatoorazatcaforultimatepos::lang.building_number_name') . ':') !!}
            @error('building_number')
                <span class="error">{{ $message }}</span>
            @enderror
            <div class="input-group">
                <span class="input-group-addon">
                    <i class="fa fa-info"></i>
                </span>
                {!! Form::text('building_number', $zatca_fields['building_number'] ?? null, ['class' => 'form-control','placeholder' => __('fatoorazatcaforultimatepos::lang.building_number_placeholder')]); !!}
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="form-group">
            {!! Form::label('plot_identification', __('fatoorazatcaforultimatepos::lang.plot_identification_name') . ':') !!}
            @error('plot_identification')
                <span class="error">{{ $message }}</span>
            @enderror
            <div class="input-group">
                <span class="input-group-addon">
                    <i class="fa fa-info"></i>
                </span>
                {!! Form::text('plot_identification', $zatca_fields['plot_identification'] ?? null, ['class' => 'form-control','placeholder' => __('fatoorazatcaforultimatepos::lang.plot_identification_placeholder')]); !!}
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="form-group">
            {!! Form::label('city_sub_division', __('fatoorazatcaforultimatepos::lang.city_sub_division_name') . ':') !!}
            @error('city_sub_division')
                <span class="error">{{ $message }}</span>
            @enderror
            <div class="input-group">
                <span class="input-group-addon">
                    <i class="fa fa-info"></i>
                </span>
                {!! Form::text('city_sub_division', $zatca_fields['city_sub_division'] ?? null, ['class' => 'form-control','placeholder' => __('fatoorazatcaforultimatepos::lang.city_sub_division_placeholder')]); !!}
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="form-group">
            {!! Form::label('postal_number', __('fatoorazatcaforultimatepos::lang.postal_number_name') . ':') !!}
            @error('postal_number')
                <span class="error">{{ $message }}</span>
            @enderror
            <div class="input-group">
                <span class="input-group-addon">
                    <i class="fa fa-info"></i>
                </span>
                {!! Form::text('postal_number', $zatca_fields['postal_number'] ?? null, ['class' => 'form-control','placeholder' => __('fatoorazatcaforultimatepos::lang.postal_number_placeholder')]); !!}
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="form-group">
            {!! Form::label('email', __('fatoorazatcaforultimatepos::lang.email_name') . ':') !!}
            @error('email')
                <span class="error">{{ $message }}</span>
            @enderror
            <div class="input-group">
                <span class="input-group-addon">
                    <i class="fa fa-info"></i>
                </span>
                {!! Form::text('email', $zatca_fields['email'] ?? null, ['class' => 'form-control','placeholder' => __('fatoorazatcaforultimatepos::lang.email_placeholder')]); !!}
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="form-group">
            {!! Form::label('city', __('fatoorazatcaforultimatepos::lang.city_name') . ':') !!}
            @error('city')
                <span class="error">{{ $message }}</span>
            @enderror
            <div class="input-group">
                <span class="input-group-addon">
                    <i class="fa fa-info"></i>
                </span>
                {!! Form::text('city', $zatca_fields['city'] ?? null, ['class' => 'form-control','placeholder' => __('fatoorazatcaforultimatepos::lang.city_placeholder')]); !!}
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="form-group">
            {!! Form::label('invoice_report_type', __('fatoorazatcaforultimatepos::lang.invoice_report_type_name') . ':') !!}
            @error('invoice_report_type')
                <span class="error">{{ $message }}</span>
            @enderror
            <div class="input-group">
                <span class="input-group-addon">
                    <i class="fa fa-info"></i>
                </span>
                {!! Form::select('invoice_report_type', $invoice_types, $zatca_fields['invoice_report_type'] ?? null, ['class' => 'form-control']) !!}
            </div>
        </div>
    </div>
</div>