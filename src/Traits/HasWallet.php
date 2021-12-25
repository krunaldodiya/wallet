<?php

namespace KD\Wallet\Traits;

use Error;

use KD\Wallet\Models\Wallet;
use KD\Wallet\Models\WalletTransaction;

use Illuminate\Support\Str;

trait HasWallet
{
    public function wallet()
    {
        return $this->hasOne(
            config('wallet.wallet_model', Wallet::class)
        )->withDefault();
    }

    public function transactions()
    {
        return $this->hasManyThrough(
            config('wallet.transaction_model', WalletTransaction::class),
            config('wallet.wallet_model', Wallet::class)
        )->latest();
    }

    public function canWithdraw($amount)
    {
        return $this->balance >= $amount;
    }

    public function deposit($amount, $meta = [])
    {
        $transaction = $this->createTransaction($amount, 'deposit', $meta);

        return $this->processTransaction($transaction);
    }

    public function withdraw($amount, $meta = [])
    {
        $transaction = $this->createTransaction($amount, 'withdraw', $meta);

        return $this->processTransaction($transaction);
    }

    public function forceWithdraw($amount, $meta = [])
    {
        $transaction = $this->createTransaction(
            $amount,
            'forceWithdraw',
            $meta
        );

        return $this->processTransaction($transaction, true);
    }

    public function createTransaction($amount, $type, $meta = [])
    {
        if (!$this->wallet->exists) {
            $this->wallet->save();
        }

        if ($type === 'withdraw' && !$this->canWithdraw($amount)) {
            throw new Error('Insufficient balance');
        }

        return $this->wallet->transactions()->create([
            'user_id' => $this->id,
            'amount' => $amount,
            'transaction_id' => Str::random(32),
            'transaction_type' => $type === 'deposit' ? 'deposit' : 'withdraw',
            'status' => 'pending',
            'meta' => $meta,
        ]);
    }

    protected function processTransaction($transaction)
    {
        $this->wallet->balance =
            $transaction['transaction_type'] === 'deposit'
                ? $transaction->wallet->balance + $transaction['amount']
                : $transaction->wallet->balance - $transaction['amount'];

        $this->wallet->save();

        $transaction->update(['status' => 'success']);

        return $transaction;
    }
}
