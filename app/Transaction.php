<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Transaction extends Model
{
    protected $table = 'transactions';
    public $timestamps = true;
    protected $fillable = [
        'account_id',
        'user_id',
        'user_id',
        'type_transaction_id',
        'value',
        'balance',
        'document',
        'number_card',
        'number_phone',
        'description',
        'date',
        'created_at',
        'updated_at',
        'deleted_at',
    ];


    public function getBalance()
    {
        if ($this->start_date == '') {
            return array('balance' => '0');
        }
        return DB::table('transactions as transctns')
        ->selectRaw("
            round(COALESCE(sum(COALESCE(transctns.value,0)),0),2) as balance
        ")
        ->whereNull('transctns.deleted_at')
        ->when($this->account_id, function ($query, $account_id) {
            return $query->where('transctns.account_id', '=', $this->account_id);
        })
        ->when($this->start_date, function ($query, $start_date) {
            return $query->where('transctns.date', '<',  $start_date);
        })
        ->first();
    }

    public function getTransactions()
    {
        return DB::table('transactions  as transctns')
        ->leftJoin('users               as usrs',       'usrs.id',       '=','transctns.user_id')
        ->leftJoin('type_transactions   as typtrnsctn', 'typtrnsctn.id', '=','transctns.type_transaction_id')
        ->selectRaw('
            transctns.id,
            transctns.account_id,
            transctns.user_id,
            usrs.name                  as user_name,
            transctns.type_transaction_id,
            transctns.value,
            transctns.balance,
            transctns.document,
            transctns.number_card,
            transctns.number_phone,
            typtrnsctn.description      as typtrnsctn_description,
            transctns.description,
            transctns.date,
            transctns.created_at,
            transctns.updated_at,
            transctns.deleted_at
        ')
        ->when($this->account_id, function ($query, $account_id) {
            return $query->where('transctns.account_id', '=',  $account_id);
        })
        ->get();
    }
}
