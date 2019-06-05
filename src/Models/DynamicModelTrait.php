<?php

namespace ViaRest\Models;

use ViaRest\Http\Requests\Api\CreateRequest;
use ViaRest\Http\Requests\Api\CrudRequestInterface;
use ViaRest\Http\Requests\Api\DestroyRequest;
use ViaRest\Http\Requests\Api\FetchAllRequest;
use ViaRest\Http\Requests\Api\FetchRequest;
use ViaRest\Http\Requests\Api\UpdateRequest;
use Illuminate\Support\Str;

trait DynamicModelTrait
{

    private $base = '\Http\Requests\Api\\';


    public function instanceCreateRequest(): CrudRequestInterface
    {
        $refl = new \ReflectionClass($this);
        $module = explode('\\', $refl->getNamespaceName())[0];
        $modelName = $refl->getShortName();
        $endpoint = '\CreateRequest';

        $className = $module . $this->base . Str::plural($modelName) . $endpoint;

        if (! class_exists($className)) {
            return new CreateRequest();
        }

        return new $className();
    }

    public function instanceUpdateRequest(): CrudRequestInterface
    {
        $refl = new \ReflectionClass($this);
        $module = explode('\\', $refl->getNamespaceName())[0];
        $modelName = $refl->getShortName();
        $endpoint = '\UpdateRequest';

        $className = $module . $this->base . Str::plural($modelName) . $endpoint;

        if (! class_exists($className)) {
            return new UpdateRequest();
        }

        return new $className();
    }

    public function instanceFetchRequest(): CrudRequestInterface
    {
        $refl = new \ReflectionClass($this);
        $module = explode('\\', $refl->getNamespaceName())[0];$module = explode('\\', $refl->getNamespaceName())[0];
        $modelName = $refl->getShortName();
        $endpoint = '\FetchRequest';

        $className = $module . $this->base . Str::plural($modelName) . $endpoint;

        if (! class_exists($className)) {
            return new FetchRequest();
        }

        return new $className();
    }

    public function instanceFetchAllRequest(): CrudRequestInterface
    {
        $refl = new \ReflectionClass($this);
        $module = explode('\\', $refl->getNamespaceName())[0];
        $modelName = $refl->getShortName();
        $endpoint = '\FetchAllRequest';

        $className = $module . $this->base . Str::plural($modelName) . $endpoint;

        if (! class_exists($className)) {
            return new FetchAllRequest();
        }

        return new $className();
    }

    public function instanceDestroyRequest(): CrudRequestInterface
    {
        $refl = new \ReflectionClass($this);
        $module = explode('\\', $refl->getNamespaceName())[0];
        $modelName = $refl->getShortName();
        $endpoint = '\DestroyRequest';

        $className = $module . $this->base . Str::plural($modelName) . $endpoint;

        if (! class_exists($className)) {
            return new DestroyRequest();
        }

        return new $className();
    }

}
