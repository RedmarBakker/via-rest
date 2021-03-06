<?php

namespace ViaRest\Http\Controllers\Api;

use ViaRest\Models\DynamicModelInterface;
use Illuminate\Database\Eloquent\Model;

interface RestControllerInterface
{

    public static function getModelClass(): string;

}
