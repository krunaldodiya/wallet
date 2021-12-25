<?php

namespace KD\Wallet\Models;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $table = 'wallets';

    protected $fillable = [
        'user_id', 'balance'
    ];

    /**
     * Retrieve all transactions
     */
    public function transactions()
    {
        return $this->hasMany(config('wallet.transaction_model', WalletTransaction::class));
    }

    /**
     * Retrieve owner
     */
    public function user()
    {
        return $this->belongsTo(config('wallet.user_model', \App\User::class));
    }
}
