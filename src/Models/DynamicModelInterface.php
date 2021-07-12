<?php

namespace ViaRest\Models;

use ViaRest\Http\Requests\Api\CrudRequestInterface;
use ViaRest\Http\Requests\Api\FetchAllRequest;

interface DynamicModelInterface
{

    public static function instanceCreateRequest(): CrudRequestInterface;

    public static function instanceUpdateRequest(): CrudRequestInterface;

    public static function instanceFetchRequest(): CrudRequestInterface;

    public static function instanceFetchAllRequest(): FetchAllRequest;

    public static function instanceDestroyRequest(): CrudRequestInterface;

}
