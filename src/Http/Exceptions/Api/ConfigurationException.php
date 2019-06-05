<?php

namespace App\Exceptions\Api;

use Exception;
use Throwable;

class ConfigurationException extends Exception
{

    public function __construct(string $message = "")
    {
        parent::__construct($message, 500);
    }

}
