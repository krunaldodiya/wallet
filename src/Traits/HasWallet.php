<?php

namespace KD\Wallet\Traits;

use Error;

use KD\Wallet\Models\Wallet;
use KD\Wallet\Models\WalletTransaction;

use Illuminate\Support\Str;

trait HasWallet
{
    public function getBalanceAttribute()
    {
        return $this->wallet->balance;
    }

    public function actualBalance()
    {
        $credits = $this->wallet
            ->transactions()
            ->whereIn('transaction_type', ['deposit'])
            ->where('status', 'success')
            ->sum('amount');

        $debits = $this->wallet
            ->transactions()
            ->whereIn('transaction_type', ['withdraw'])
            ->where('status', 'success')
            ->sum('amount');

        return $credits - $debits;
    }

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

    public function getOrderById($transaction_id)
    {
        return WalletTransaction::where([
            'transaction_id' => $transaction_id,
        ])->first();
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
        $transaction->wallet->balance =
            $transaction['transaction_type'] === 'deposit'
                ? $transaction->wallet->balance + $transaction['amount']
                : $transaction->wallet->balance - $transaction['amount'];

        $transaction->wallet->save();

        $transaction->update(['status' => 'success']);

        return $transaction;
    }
}
