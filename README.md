laravel-decaptcha
================
A Laravel package for captcha recognition for popular services like rucaptcha.com, 2captcha.com, pixodrom.com, captcha24.com, socialink.ru, anti-captcha.com

Features
------------
* Suitable for all to recognize captchas services operating on common standards
* Easy setup
* Accept the file path or by reference



Installation
------------
The preferred way to install this extension through [composer] (http://getcomposer.org/download/). Start by adding the package to require your composer.json

Run in your terminal:

```shell
composer require insign/laravel-decaptcha:~1
```

Configuration
------------

Having loaded dependencies and installed on your project, we will add ServiceProvider and facade.

### ServiceProvider
You need to update your application configuration in order to register the package so it can be loaded by Framework.

####Laravel
Just update your `config/app.php` file adding the following code at the end of your `'providers'` section:

```php
'providers' => [
    // your others classes here...
    
    insign\LaravelDecaptcha\LaravelDecaptchaServiceProvider::class,
    
],
```


#### Lumen
Go to `/bootstrap/app.php` file and add this line:

```php
	$app->register(insign\LaravelDecaptcha\LaravelDecaptchaServiceProvider::class);
```

#### Facade
Adding a new item on its facade

```php
'aliases' => array(
    // your others classes here...

	'Decaptcha' => insign\LaravelDecaptcha\LaravelDecaptchaFacade::class,
),
```

#### Settings
To move the Decaptcha settings file to the Settings folder of your application, simply perform the following command:

```shell
php artisan vendor:publish --provider="insign\LaravelDecaptcha\LaravelDecaptchaServiceProvider"
```

In your`.env` file, add the following values

```
DECAPTCHA_KEY=yourkeyfortheservice
DECAPTCHA_DOMAIN=thedomainservice.com

```
Or simply edit the file `config/decaptcha.php` 

Using
------------
A simple example:

```php
$path = 'path/insideto/captcha.png';
if (Decaptcha::run($path)) {
    $solved = Decaptcha::result();
} else {
    throw new \Exception(Decaptcha::error());
}
```

You can apply if you have only a reference to a captcha, but for this method, you should set the path in the configuration to save the captchas (DECAPTCHA_TMP var):

```php
   $path = 'https://vk.com/captcha.php?sid=698254154192&s=1';
   if (Decaptcha::run($path)) {
       $solved = Decaptcha::result();
   } else {
       throw new \Exception(Decaptcha::error());
   }
```