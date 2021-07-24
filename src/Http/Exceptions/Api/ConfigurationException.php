<?php

namespace ViaRest\Http\Exceptions\Api;

use Exception;

class ConfigurationException extends Exception
{

    public function __construct(string $message = "")
    {
        parent::__construct($message, 500);
    }

}
