<?php

namespace insign\LaravelDecaptcha;

use Illuminate\Support\ServiceProvider;

class LaravelDecaptchaServiceProvider extends ServiceProvider
{
   /**
    * Indicates if loading of the provider is deferred.
    *
    * @var bool
    */
   protected $defer = TRUE;

   /**
    * Bootstrap the application events.
    */
   public function boot()
   {
      $config_file = __DIR__ . '/../config/decaptcha.php';
      if ($this->isLumen()) {
         $this->app->configure('Decaptcha');
      } else {
         $this->publishes([ $config_file => config_path('decaptcha.php') ]);
      }
      $this->mergeConfigFrom($config_file, 'decaptcha');
   }

   /**
    * Register the service provider.
    */
   public function register()
   {
      $this->app->singleton('Decaptcha', LaravelDecaptcha::class);
   }

   /**
    * Get the services provided by the provider.
    *
    * @return array
    */
   public function provides()
   {
      return [ 'Decaptcha' ];
   }

   /**
    * @return bool
    */
   private function isLumen()
   {
      return TRUE === str_contains($this->app->version(), 'Lumen');
   }
}
