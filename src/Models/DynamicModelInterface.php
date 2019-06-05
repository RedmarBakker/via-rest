<?php

namespace ViaRest\Models;

use ViaRest\Http\Requests\Api\CrudRequestInterface;

interface DynamicModelInterface
{

    public function instanceCreateRequest(): CrudRequestInterface;

    public function instanceUpdateRequest(): CrudRequestInterface;

    public function instanceFetchRequest(): CrudRequestInterface;

    public function instanceFetchAllRequest(): CrudRequestInterface;

    public function instanceDestroyRequest(): CrudRequestInterface;

}
