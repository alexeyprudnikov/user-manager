<?php

namespace App\Helper;

interface ValidatorInterface
{
    public function notEmpty($arg, $message);

    public function same($arg1, $arg2, $message);
}