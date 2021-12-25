# Laravel Wallet

In a few projects I had to implement a virtual currency. The user would buy packs of credits with Stripe and then use them in the app in exchange of services or goods.
This package is a small and simple implementation of this concept with place for customization.

## Installation

Install the package with composer:

```bash
composer require krunaldodiya/wallet
```

## Run Migrations

Publish the migrations with this artisan command:

```bash
php artisan vendor:publish --provider="KD\Wallet\WalletServiceProvider" --tag=migrations
```

## Configuration

You can publish the config file with this artisan command:

```bash
php artisan vendor:publish --provider="KD\Wallet\WalletServiceProvider" --tag=config
```

This will merge the `wallet.php` config file where you can specify the Users, Wallets & Transactions classes if you have custom ones.

## Usage

Add the `HasWallet` trait to your User model.

```php

use KD\Wallet\Traits\HasWallet;

class User extends Model
{
    use HasWallet;

    ...
}
```

Then you can easily make transactions from your user model.

```php
$user = User::find(1);
$user->balance; // 0

$transaction = $user->createTransaction(100, 'deposit', ['description' => 'transaction description'])
$user->deposit($transaction->transaction_id);
$user->balance; // 100

$transaction = $user->createTransaction(50, 'withdraw', ['description' => 'transaction description'])
$user->withdraw($transaction->transaction_id);
$user->balance; // 50

$transaction = $user->createTransaction(200, 'withdraw', ['description' => 'transaction description'])
$user->forceWithdraw($transaction->transaction_id);
$user->balance; // -150
```

### Security

If you discover any security related issues, please email kunal.dodiya1@gmail.com instead of using the issue tracker.

## Credits

- [Krunal Dodiya](https://github.com/krunaldodiya)

## License

The MIT License (MIT). Please see [License File](license.md) for more information.
