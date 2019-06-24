<?php

namespace ViaRest\Models;

use ViaRest\Http\Requests\Api\CrudRequestInterface;
use ViaRest\Http\Requests\Api\FetchAllRequest;

interface DynamicModelInterface
{

    public function instanceCreateRequest(): CrudRequestInterface;

    public function instanceUpdateRequest(): CrudRequestInterface;

    public function instanceFetchRequest(): CrudRequestInterface;

    public function instanceFetchAllRequest(): FetchAllRequest;

    public function instanceDestroyRequest(): CrudRequestInterface;

}
