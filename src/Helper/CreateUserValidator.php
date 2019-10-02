<?php

namespace App\Helper;


class CreateUserValidator implements ValidatorInterface
{
    /**
     * @param $arg
     * @param string $message
     */
    public function notEmpty($arg, $message = ''): void
    {
        if(empty($arg)) {
            throw new \RuntimeException($message ?: 'Error: is empty');
        }
    }

    /**
     * @param $arg1
     * @param $arg2
     * @param string $message
     */
    public function same($arg1, $arg2, $message = ''): void
    {
        if($arg1 !== $arg2) {
            throw new \RuntimeException($message ?: 'Error: are not same!');
        }
    }
}