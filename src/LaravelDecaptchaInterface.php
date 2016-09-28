<?php

namespace insign\LaravelDecaptcha;

interface LaravelDecaptchaInterface
{
    public function run($filename);

    public function result();

    public function error();
}