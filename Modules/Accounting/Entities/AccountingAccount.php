<?php

namespace Modules\Accounting\Entities;

use App\Account;
use App\Contact;
use App\ExpenseCategory;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountingAccount extends Model
{
    use SoftDeletes;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    public function child_accounts()
    {
        return $this->hasMany(\Modules\Accounting\Entities\AccountingAccount::class, 'parent_account_id')->orderByRaw('CONVERT(gl_code, SIGNED) asc');
    }

    // public function account_type()
    // {
    //     return $this->belongsTo(\Modules\Accounting\Entities\AccountingAccountType::class, 'account_type_id');
    // }

    public function account_sub_type()
    {
        return $this->belongsTo(\Modules\Accounting\Entities\AccountingAccountType::class, 'account_sub_type_id');
    }

    public function detail_type()
    {
        return $this->belongsTo(\Modules\Accounting\Entities\AccountingAccountType::class, 'detail_type_id');
    }

    public function user()
    {
        return $this->belongsTo(\App\User::class, 'created_by');

    }

    /**
     * Accounts Dropdown
     *
     * @param  int  $business_id
     * @return array
     */
    public static function forDropdown($business_id, $with_data = false, $q = '', array $parent_ids = [], array $same_ids = [], array $without_parent_ids = [])
    {
        $query = AccountingAccount::where('accounting_accounts.business_id', $business_id)
            ->where('status', 'active');

        if (count($without_parent_ids) > 0) {
            $query->whereNotIn('parent_account_id', $without_parent_ids);
        }

        if (count($parent_ids) > 0) {
            $query->whereIn('parent_account_id', $parent_ids);
        }

        if (count($same_ids) > 0) {
            $query->whereIn('gl_code', $same_ids);
        } else {
            $query->where('gl_code', '!=', 1101);
            $query->where('gl_code', '!=', 1102);
            $query->where('gl_code', '!=', 1103);
            $query->where('gl_code', '!=', 2101);
        }

        if ($with_data) {
            $account_types = AccountingAccountType::accounting_primary_type();

            //            if (! empty($q)) {
            //                $query->where('accounting_accounts.name', 'like', "%{$q}%");
            //            }
            $accounts = $query->leftJoin('accounting_account_types as at', 'at.id', '=', 'accounting_accounts.account_sub_type_id')
                ->select('accounting_accounts.name', 'accounting_accounts.id', 'at.name as sub_type',
                    'accounting_accounts.account_primary_type', 'at.business_id as sub_type_business_id',
                    'accounting_accounts.gl_code')
                ->get();

            foreach ($accounts as $k => $v) {
                $accounts[$k]->account_primary_type = ! empty($account_types[$v->account_primary_type]) ?
                $account_types[$v->account_primary_type]['label'] : $v->account_primary_type;

                $accounts[$k]->sub_type = ! empty($v->sub_type_business_id) ? $v->sub_type : __('accounting::lang.'.$v->sub_type);
            }

            foreach ($accounts as $one) {
                $one->name = $one->gl_code.' - '.$one->name;
            }

            if (! empty($q)) {
                $accounts = $accounts->filter(function ($item) use ($q) {
                    return stristr($item->name, $q) !== false;
                });
            }

            return $accounts;
        } else {

            $data = $query->get();

            $arr = [];

            foreach ($data as $key => $value) {
                $arr[$key] = $value->gl_code.' - '.$value->name;
            }

            return $arr;
        }
    }

    public function parent()
    {
        return $this->belongsTo(\Modules\Accounting\Entities\AccountingAccount::class, 'parent_account_id');
    }

    public function getNameAttribute()
    {
        $trans = __('accounting::lang.'.$this->attributes['name']);

        if (str_starts_with($trans, 'accounting::lang.')) {
            $trans = $this->attributes['name'];
        }

        return $trans;
    }

    public function getTextAttribute()
    {
        $trans = __('accounting::lang.'.$this->attributes['text']);

        if (str_starts_with($trans, 'accounting::lang.')) {
            $trans = $this->attributes['text'];
        }

        return $trans;
    }

    public function account_linked($table_name)
    {
        $link_table = $this->link_table;
        $link_id = $this->link_id;

        if ($link_table == $table_name && $table_name == 'contacts') {
            $contact = Contact::where('id', $link_id)->first();

            if (isset($contact->id)) {
                return $contact;
            }
        } elseif ($link_table == $table_name && $table_name == 'users') {
            $user = User::where('id', $link_id)->first();

            if (isset($user->id)) {
                return $user;
            }
        } elseif ($link_table == $table_name && $table_name == 'accounts') {
            $account = Account::where('id', $link_id)->first();

            if (isset($account->id)) {
                return $account;
            }
        } elseif ($link_table == $table_name && $table_name == 'expense_categories') {
            $expense_category = ExpenseCategory::where('id', $link_id)->first();

            if (isset($expense_category->id)) {
                return $expense_category;
            }
        }

        return null;
    }

    public function is_account_linked($table_name)
    {
        $link_table = $this->link_table;
        $link_id = $this->link_id;

        if ($link_table == $table_name && $table_name == 'contacts') {
            $contact = Contact::where('id', $link_id)->first();

            if (isset($contact->id)) {
                return true;
            }
        } elseif ($link_table == $table_name && $table_name == 'users') {
            $user = User::where('id', $link_id)->first();

            if (isset($user->id)) {
                return true;
            }
        } elseif ($link_table == $table_name && $table_name == 'accounts') {
            $account = Account::where('id', $link_id)->first();

            if (isset($account->id)) {
                return true;
            }
        } elseif ($link_table == $table_name && $table_name == 'expense_categories') {
            $expense_category = ExpenseCategory::where('id', $link_id)->first();

            if (isset($expense_category->id)) {
                return true;
            }
        }

        return false;
    }
}
