<?php

namespace insign\LaravelDecaptcha;

use Illuminate\Support\ServiceProvider;

class LaravelDecaptchaServiceProvider extends ServiceProvider
{

   /**
    * Bootstrap the application services.
    *
    * @return void
    */
   public function boot()
   {
      //
   }

   /**
    * Register the application services.
    *
    * @return void
    */
   public function register()
   {
      $this->registerLaravelDecaptcha();
   }

   /**
    * Registers Facade
    *
    * @return LaravelDecaptcha
    */
   private function registerLaravelDecaptcha()
   {
      $this->app->bind('LD', function ($app) {
         return new LaravelDecaptcha($app);
      });
   }
}
