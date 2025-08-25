<?php
$tree_accs =  \Modules\Accounting\Utils\AccountingUtil::checkTreeOfAccountsIsHere();
?>
@if($tree_accs)
    <div class="form-group">
        {!! Form::label('from_account',  __( 'lang_v1.payment_method' ) .":*") !!}
        {!! Form::select('from_account', [], null, ['class' => 'form-control accounts-dropdown', 'required',
            'same_ids' => '1101,1102',
            'placeholder' => __('messages.please_select') ]); !!}
    </div>
@endif