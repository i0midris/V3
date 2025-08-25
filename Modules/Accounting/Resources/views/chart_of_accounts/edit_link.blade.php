<div class="modal-dialog modal-lg" role="document">
  <div class="modal-content">

    {!! Form::open(['url' => action([\Modules\Accounting\Http\Controllers\CoaController::class, 'update_link'], $account->id),
        'method' => 'put', 'id' => 'edit_link_form' ]) !!}

    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">@lang( 'accounting::lang.edit_account' )</h4>
    </div>

    <div class="modal-body">
        <div class="row">
            <input type="hidden" name="link_table" value="{{$link_table}}" id="link_table">
            <div class="col-md-12">
                <div class="form-group">
                    {!! Form::label('link_id', __( 'accounting::lang.account_type' ) . ':*') !!}
                    {!! Form::select('link_id', $link_id, null,  ['class' => 'form-control',
                      'required', 'placeholder' => __('messages.please_select'), 'id' => 'link_id' ]); !!}
                </div>
            </div>
        </div>
    </div>

    <div class="modal-footer">
      <button type="submit" class="btn btn-primary">@lang( 'messages.save' )</button>
      <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
    </div>

    {!! Form::close() !!}

  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->