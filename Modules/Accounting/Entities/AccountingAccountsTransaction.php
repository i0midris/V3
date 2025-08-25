<?php

namespace Modules\Accounting\Entities;

use Illuminate\Database\Eloquent\Model;

class AccountingAccountsTransaction extends Model
{
    protected $guarded = [];

    public function account()
    {
        return $this->belongsTo('Modules\Accounting\Entities\AccountingAccount', 'accounting_account_id');
    }

    /**
     * Creates new account transaction
     *
     * @return obj
     */
    public static function createTransaction($data)
    {
        $transaction = new AccountingAccountsTransaction;

        $transaction->amount = $data['amount'];
        $transaction->accounting_account_id = $data['accounting_account_id'];
        $transaction->transaction_id = ! empty($data['transaction_id']) ? $data['transaction_id'] : null;
        $transaction->type = $data['type'];
        $transaction->sub_type = ! empty($data['sub_type']) ? $data['sub_type'] : null;
        $transaction->map_type = ! empty($data['map_type']) ? $data['map_type'] : null;
        $transaction->operation_date = ! empty($data['operation_date']) ? $data['operation_date'] : \Carbon::now();
        $transaction->created_by = $data['created_by'];
        $transaction->note = ! empty($data['note']) ? $data['note'] : null;
        $transaction->currency_code = $data['currency_code'] ?? null;
    $transaction->exchange_rate = $data['exchange_rate'] ?? 1;
    $transaction->base_amount = $data['base_amount'] ?? ($data['amount'] * ($data['exchange_rate'] ?? 1));


        return $transaction->save();
    }

    /**
     * Creates/updates account transaction
     *
     * @return obj
     */
    public static function updateOrCreateMapTransaction($data)
    {
        $transaction = AccountingAccountsTransaction::updateOrCreate(
            ['transaction_id' => $data['transaction_id'],
                'map_type' => $data['map_type'],
                'transaction_payment_id' => $data['transaction_payment_id'],
            ],
            ['accounting_account_id' => $data['accounting_account_id'], 'amount' => $data['amount'],
                'type' => $data['type'], 'sub_type' => $data['sub_type'], 'created_by' => $data['created_by'], 'operation_date' => $data['operation_date'],
            ]
        );
    }

    public function location()
    {
        return $this->belongsTo(\App\BusinessLocation::class, 'location_id');
    }

    public function accounting_acc_trans_mapping()
    {
        return $this->belongsTo('Modules\Accounting\Entities\AccountingAccTransMapping', 'acc_trans_mapping_id');
    }
}
