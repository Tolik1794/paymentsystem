<?php

namespace payment\PaymentSystem\Portmone\exceptions;

class PortmoneException extends \Exception
{
    const PARAMS_ERROR = 100;
    const VALIDATION_ERROR = 110;
    const REQUEST_ERROR = 120;
    const PARSE_ERROR = 130;
    const RESULT_ERROR = 140;
    const NOT_FOUND = 200;
    const CONFIGURATION_ERROR = 999;
}