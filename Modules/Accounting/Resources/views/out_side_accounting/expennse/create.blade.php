<?php
$tree_accs =  \Modules\Accounting\Utils\AccountingUtil::checkTreeOfAccountsIsHere();
?>
@if($tree_accs)
    <script>
    $('#amount_0').parent().parent().parent().hide();
    $('#final_total').keyup(function (event) {
    $('#amount_0').val(this.value);
    });
    </script>
@endif