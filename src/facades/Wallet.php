<?php namespace KD\Wallet\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \KD\Wallet\Facades
 */
class Wallet extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'wallet';
    }
}
