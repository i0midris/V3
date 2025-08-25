<?php

namespace Modules\Accounting\Entities;

use App\Contact;
use Illuminate\Database\Eloquent\Model;

class AccountingAccTransMapping extends Model
{
    protected $fillable = [];

    public function childs()
    {
        return $this->hasMany(\Modules\Accounting\Entities\AccountingAccountsTransaction::class, 'acc_trans_mapping_id')->orderBy('accounting_accounts_transactions.type', 'desc')->get();
    }

    public function createdBy()
    {
        return $this->belongsTo(\App\User::class, 'created_by');
    }

    public function getTypeTransAttribute()
    {
        $trans = __('accounting::lang.'.$this->attributes['type']);

        if (str_starts_with($trans, 'accounting::lang.')) {
            $trans = $this->attributes['type'];
        }

        return $trans;
    }

    public function getIsContactLinkedAttribute()
    {
        $link_table = $this->attributes['link_table'];
        $link_id = $this->attributes['link_id'];

        if ($link_table == 'contacts') {
            $contact = Contact::where('id', $link_id)->first();

            if (isset($contact->id)) {
                return true;
            }
        }

        return false;
    }
}
