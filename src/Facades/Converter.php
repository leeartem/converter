<?php

namespace LeeArtem\Converter\Facades;

use Illuminate\Support\Facades\Facade;

class Converter extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'converter';
    }
}
