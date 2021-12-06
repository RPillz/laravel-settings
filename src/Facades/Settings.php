<?php

namespace RPillz\Settings\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \RPillz\Settings\Settings
 */
class Settings extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \RPillz\Settings\Settings::class;
    }
}
