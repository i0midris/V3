<?php
$tree_accs =  \Modules\Accounting\Utils\AccountingUtil::checkTreeOfAccountsIsHere();
?>
@if($tree_accs)
    @include('accounting::accounting.common_js')
@endif