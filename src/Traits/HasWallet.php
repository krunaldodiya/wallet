<?php

namespace KD\Wallet\Traits;

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
        $credits = $this->wallet->transactions()
            ->whereIn('transaction_type', ['deposit'])
            ->where('status', 'success')
            ->sum('amount');

        $debits = $this->wallet->transactions()
            ->whereIn('transaction_type', ['withdraw'])
            ->where('status', 'success')
            ->sum('amount');

        return $credits - $debits;
    }

    public function wallet()
    {
        return $this->hasOne(config('wallet.wallet_model', Wallet::class))->withDefault();
    }

    public function transactions()
    {
        return $this->hasManyThrough(config('wallet.transaction_model', WalletTransaction::class), config('wallet.wallet_model', Wallet::class))->latest();
    }

    public function getOrderById($transaction_id)
    {
        return WalletTransaction::where(['transaction_id' => $transaction_id])->first();
    }

    public function canWithdraw($amount)
    {
        return $this->balance >= $amount;
    }

    public function deposit($transactionId)
    {
        return $this->processTransaction($transactionId);
    }

    public function withdraw($transactionId)
    {
        return $this->processTransaction($transactionId);
    }

    public function forceWithdraw($transactionId)
    {
        return $this->processTransaction($transactionId, true);
    }

    public function createTransaction($amount, $type, $meta = [])
    {
        if (!$this->wallet->exists) {
            $this->wallet->save();
        }

        return $this->wallet->transactions()
            ->create([
                'user_id' => $this->id,
                'amount' => $amount,
                'transaction_id' => Str::random(32),
                'transaction_type' => $type,
                'status' => 'pending',
                'meta' => $meta
            ]);
    }

    protected function processTransaction($transactionId, $forceWithdraw = false)
    {
        $transaction = WalletTransaction::where(['transaction_id' => $transactionId])->first();

        $canWithdraw = $forceWithdraw ? true : $this->canWithdraw($transaction['amount']);

        if ($transaction['status'] === "pending") {
            if ($transaction['transaction_type'] == 'deposit') {
                $transaction->update(['status' => 'success']);

                $transaction->wallet->balance = $transaction->wallet->balance + $transaction['amount'];
                $transaction->wallet->save();
            }

            if ($transaction['transaction_type'] == 'withdraw') {
                if ($canWithdraw) {
                    $transaction->update(['status' => 'success']);

                    $transaction->wallet->balance = $transaction->wallet->balance - $transaction['amount'];
                    $transaction->wallet->save();
                } else {
                    $transaction->update(['status' => 'failed']);
                }
            }
        }

        return $transaction;
    }
}
