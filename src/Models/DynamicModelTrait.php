<?php

namespace ViaRest\Models;

use App\Exceptions\Api\ConfigurationException;
use ViaRest\Http\Requests\Api\CrudRequestInterface;
use ViaRest\Http\Requests\Api\DefaultRequest;
use Illuminate\Support\Str;
use ViaRest\Http\Requests\Api\FetchAllRequest;

trait DynamicModelTrait
{

    private static $base = '%s\Http\Requests\Api\%s\%s';


    /**
     * @return CrudRequestInterface
     * @throws ConfigurationException
     * */
    public static function instanceCreateRequest(): CrudRequestInterface
    {
        return self::instanceRequest('CreateRequest');
    }

    /**
     * @return CrudRequestInterface
     * @throws ConfigurationException
     * */
    public static function instanceUpdateRequest(): CrudRequestInterface
    {
        return self::instanceRequest('UpdateRequest');
    }

    /**
     * @return CrudRequestInterface
     * @throws ConfigurationException
     * */
    public static function instanceFetchRequest(): CrudRequestInterface
    {
        return self::instanceRequest('FetchRequest');
    }

    /**
     * @return CrudRequestInterface
     * @throws ConfigurationException
     * */
    public static function instanceFetchAllRequest(): FetchAllRequest
    {
        $request = self::instanceRequest('FetchAllRequest');

        return $request instanceof FetchAllRequest ? $request : new FetchAllRequest();
    }

    /**
     * @return CrudRequestInterface
     * @throws ConfigurationException
     * */
    public static function instanceDestroyRequest(): CrudRequestInterface
    {
        return self::instanceRequest('DestroyRequest');
    }

    /**
     * @param $endpoint string
     * @return CrudRequestInterface
     * @throws ConfigurationException
     * */
    private static function instanceRequest(string $endpoint): CrudRequestInterface
    {
        try {
            $refl = new \ReflectionClass(get_called_class());
            $module = explode('\\', $refl->getNamespaceName())[0];
            $modelName = $refl->getShortName();

            $className = sprintf(self::$base, $module, Str::plural($modelName), $endpoint);

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
