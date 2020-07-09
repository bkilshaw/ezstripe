<?php

namespace bkilshaw\EZStripe\Facades;

use Illuminate\Support\Facades\Facade;

class EZStripe extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'ezstripe';
    }
}
