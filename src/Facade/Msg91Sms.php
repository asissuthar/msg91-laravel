<?php

namespace Msg91\Laravel\Facade;

use Msg91\SmsClient;
use Illuminate\Support\Facades\Facade;

class Msg91Sms extends Facade
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
        return SmsClient::class;
    }
}