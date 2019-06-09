<?php

namespace ViaRest\Models;

use App\Exceptions\Api\ConfigurationException;
use ViaRest\Http\Requests\Api\CrudRequestInterface;
use ViaRest\Http\Requests\Api\DefaultRequest;
use Illuminate\Support\Str;

trait DynamicModelTrait
{

    private $base = '%s\Http\Requests\Api\%s\%s';


    /**
     * @return CrudRequestInterface
     * @throws ConfigurationException
     * */
    public function instanceCreateRequest(): CrudRequestInterface
    {
        return $this->instanceRequest('CreateRequest');
    }

    /**
     * @return CrudRequestInterface
     * @throws ConfigurationException
     * */
    public function instanceUpdateRequest(): CrudRequestInterface
    {
        return $this->instanceRequest('UpdateRequest');
    }

    /**
     * @return CrudRequestInterface
     * @throws ConfigurationException
     * */
    public function instanceFetchRequest(): CrudRequestInterface
    {
        return $this->instanceRequest('FetchRequest');
    }

    /**
     * @return CrudRequestInterface
     * @throws ConfigurationException
     * */
    public function instanceFetchAllRequest(): CrudRequestInterface
    {
        return $this->instanceRequest('FetchAllRequest');
    }

    /**
     * @return CrudRequestInterface
     * @throws ConfigurationException
     * */
    public function instanceDestroyRequest(): CrudRequestInterface
    {
        return $this->instanceRequest('DestroyRequest');
    }

    /**
     * @param $endpoint string
     * @return CrudRequestInterface
     * @throws ConfigurationException
     * */
    private function instanceRequest(string $endpoint): CrudRequestInterface
    {
        try {
            $refl = new \ReflectionClass($this);
            $module = explode('\\', $refl->getNamespaceName())[0];
            $modelName = $refl->getShortName();

            $className = printf($this->base, $module, Str::plural($modelName), $endpoint);

            if (!class_exists($className)) {
                return new DefaultRequest();
            }
        } catch (\ReflectionException $e) {
            throw new ConfigurationException(printf(
                'Something went wrong while trying to predict the namespace of a model.'
            ));
        }

        $model = new $className();

        if (! $model instanceof CrudRequestInterface) {
            throw new ConfigurationException(printf(
                'Configured ViaRest model needs to be an instance of %s',
                CrudRequestInterface::class
            ));
        }

        return $model;
    }

}
