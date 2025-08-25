<div class="modal-dialog" role="document">
  <div class="modal-content">

    {!! Form::open(['url' => action([\Modules\Accounting\Http\Controllers\TransferController::class, 'opening_balance_store']),
        'method' => 'post', 'id' => 'opening_balance_form' ]) !!}

    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        @if($swap)
            <h4 class="modal-title">@lang( 'accounting::lang.add_opening_balance1' )</h4>
        @else
            <h4 class="modal-title">@lang( 'accounting::lang.add_opening_balance2' )</h4>
        @endif
    </div>

    <div class="modal-body">
        <div class="form-group" style="display: none">
            {!! Form::label('ref_no', __('purchase.ref_no').':') !!}
            @show_tooltip(__('lang_v1.leave_empty_to_autogenerate'))
            {!! Form::text('ref_no', null, ['class' => 'form-control']); !!}
        </div>
        @if($swap)
            <div class="form-group">
                {!! Form::label('from_account', __('accounting::lang.start_opening_balance')  .":*") !!}
                {!! Form::select('from_account', [], null, ['class' => 'form-control accounts-dropdown', 'required',
                      'parent_ids' => $opening_balance_acc,
                    'placeholder' => __('accounting::lang.start_opening_balance') ]); !!}
            </div>

            <div class="form-group">
                {!! Form::label('to_account', __('accounting::lang.account') .":*") !!}
                {!! Form::select('to_account', [], null, ['class' => 'form-control accounts-dropdown', 'required',
                     'without_parent_ids' => $without_opening_balance_acc,
                    'placeholder' => __('accounting::lang.account') ]); !!}
            </div>
        @else
            <div class="form-group">
                {!! Form::label('from_account', __('accounting::lang.account') .":*") !!}
                {!! Form::select('from_account', [], null, ['class' => 'form-control accounts-dropdown', 'required',
                     'without_parent_ids' => $without_opening_balance_acc,
                    'placeholder' => __('accounting::lang.account') ]); !!}
            </div>

            <div class="form-group">
                {!! Form::label('to_account', __('accounting::lang.start_opening_balance')  .":*") !!}
                {!! Form::select('to_account', [], null, ['class' => 'form-control accounts-dropdown', 'required',
                      'parent_ids' => $opening_balance_acc,
                    'placeholder' => __('accounting::lang.start_opening_balance') ]); !!}
            </div>
        @endif

        <div class="form-group">
            {!! Form::label('amount', __( 'accounting::lang.amount_opening_balance' ) .":*") !!}
            {!! Form::text('amount', 0, ['class' => 'form-control input_number', 
                'required','placeholder' => __( 'accounting::lang.amount_opening_balance' ) ]); !!}
        </div>

        <div class="form-group">
            {!! Form::label('operation_date', __( 'messages.date' ) .":*") !!}
            <div class="input-group">
                {!! Form::text('operation_date', null, ['class' => 'form-control', 
                    'required','placeholder' => __( 'messages.date' ), 'id' => 'operation_date' ]); !!}
                <span class="input-group-addon">
                <span class="glyphicon glyphicon-calendar"></span>
                </span>
            </div>
        </div>
        <div class="form-group">
            {!! Form::label('location_id', __( 'business.location' ) .":") !!}
            {!! Form::select('location_id', [], null, ['class' => 'form-control locations-dropdown',
                'placeholder' => __('messages.please_select') ]); !!}
        </div>

        <div class="form-group">
            {!! Form::label('note', __( 'brand.note' )) !!}
            {!! Form::textarea('note', null, ['class' => 'form-control', 
                'placeholder' => __( 'brand.note' ), 'rows' => 4]); !!}
        </div>
    </div>

    <div class="modal-footer">
      <button type="submit" class="btn btn-primary">@lang( 'messages.save' )</button>
      <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
    </div>

    {!! Form::close() !!}

  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->