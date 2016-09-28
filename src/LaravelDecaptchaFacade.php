<?php

namespace insign\LaravelDecaptcha;

use Illuminate\Support\Facades\Facade;

class LaravelDecaptchaFacade extends Facade
{

   /**
    * Get the registered name of the component.
    *
    * @return string
    *
    * @throws \RuntimeException
    */
   protected static function getFacadeAccessor()
   {
      return 'Decaptcha';
   }
}
