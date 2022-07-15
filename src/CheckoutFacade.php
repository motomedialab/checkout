<?php

namespace Motomedialab\Checkout;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Motomedialab\Checkout\Skeleton\SkeletonClass
 */
class CheckoutFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'checkout';
    }
}
