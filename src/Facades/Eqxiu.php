<?php 
namespace Githen\LaravelEqxiu\Facades;

use Illuminate\Support\Facades\Facade;

class Eqxiu extends Facade {

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'jiaoyu.eqxiu';
    }

}