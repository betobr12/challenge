<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CardTransaction extends Model
{
    protected $table = 'card_transactions';
    public $timestamps = true;
    protected $fillable = [
        'account_id',
        'user_id',
        'card_id',
        'value',
        'balance',
        'store',
        'date',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function getBalanceCard()
    {
        if ($this->start_date == '') {
            return array('balance' => '0');
        }
        return DB::table('card_transactions as card_transctns')
        ->selectRaw("
            round(COALESCE(sum(COALESCE(card_transctns.value,0)),0),2) as balance
        ")
        ->whereNull('card_transctns.deleted_at')
        ->when($this->account_id, function ($query, $account_id) {
            return $query->where('card_transctns.account_id', '=', $this->account_id);
        })
        ->when($this->start_date, function ($query, $start_date) {
            return $query->where('card_transctns.date', '<',  $start_date);
        })
        ->first();
    }

    public function getCardTransactions()
    {
        return DB::table('card_transactions  as card_transctns')
        ->selectRaw('
            card_transctns.id,
            card_transctns.account_id,
            card_transctns.user_id,
            card_transctns.card_id,
            card_transctns.value,
            card_transctns.balance,
            card_transctns.store,
            card_transctns.date,
            card_transctns.created_at,
            card_transctns.updated_at,
            card_transctns.deleted_at
        ')
        ->when($this->account_id, function ($query, $account_id) {
            return $query->where('card_transctns.account_id', '=',  $account_id);
        })
        ->get();
    }
}
