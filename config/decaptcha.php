<?php
return [

   /*
   | Any request to the API must be passed the API key 
   */
   'key'    => env('DECAPTCHA_KEY', 'a0aa0aa0a0a0aa0000a00a00aaaa00aa'),

   /*
   | Service which will load the captcha
   */
   'domain' => env('DECAPTCHA_DOMAIN', '2captcha.com'),

   /*
   | The folder inside storage folder that the script should save images got by reference
   */
   'tmp'    => env('DECAPTCHA_TMP', 'captcha'),
];