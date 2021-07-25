<?php

namespace ViaRest\Models;

use ViaRest\Http\Exceptions\Api\ConfigurationException;
use ViaRest\Http\Requests\Api\CrudRequestInterface;
use ViaRest\Http\Requests\Api\DefaultRequest;
use Illuminate\Support\Str;
use ViaRest\Http\Requests\Api\FetchAllRequest;

trait DynamicModelTrait
{

    private static $base = '%s\Http\Requests\Api\%s\%s';


    /**
     * @param null $version
     * @return CrudRequestInterface
     * @throws ConfigurationException
     */
    public static function instanceCreateRequest($version = null): CrudRequestInterface
    {
        return self::instanceRequest('CreateRequest', $version);
    }

    /**
     * @param null $version
     * @return CrudRequestInterface
     * @throws ConfigurationException
     * */
    public static function instanceUpdateRequest($version = null): CrudRequestInterface
    {
        return self::instanceRequest('UpdateRequest', $version);
    }

    /**
     * @param null $version
     * @return CrudRequestInterface
     * @throws ConfigurationException
     * */
    public static function instanceFetchRequest($version = null): CrudRequestInterface
    {
        return self::instanceRequest('FetchRequest', $version);
    }

    /**
     * @param null $version
     * @return FetchAllRequest
     * @throws ConfigurationException
     * */
    public static function instanceFetchAllRequest($version = null): FetchAllRequest
    {
        $request = self::instanceRequest('FetchAllRequest', $version);

        return $request instanceof FetchAllRequest ? $request : new FetchAllRequest();
    }

    /**
     * @param null $version
     * @return CrudRequestInterface
     * @throws ConfigurationException
     * */
    public static function instanceDestroyRequest($version = null): CrudRequestInterface
    {
        return self::instanceRequest('DestroyRequest', $version);
    }

    /**
     * @param $version
     * @param $endpoint string
     * @return CrudRequestInterface
     * @throws ConfigurationException
     * */
    private static function instanceRequest(string $endpoint, $version): CrudRequestInterface
    {
        try {
            $refl = new \ReflectionClass(get_called_class());
            $module = explode('\\', $refl->getNamespaceName())[0];
            $modelName = $refl->getShortName();

            if ($version != null) {
                $module = $module . '\\' . $version;
            }

            $className = sprintf(
                self::$base,
                $module,
                Str::plural($modelName),
                $endpoint
            );

            if (!class_exists($className)) {
                return new DefaultRequest();
            }
        } catch (\ReflectionException $e) {
            throw new ConfigurationException(sprintf(
                'Something went wrong while trying to predict the ' .
                'namespace of a model.'
            ));
        }

        $request = new $className();

        if (! $request instanceof CrudRequestInterface) {
            throw new ConfigurationException(sprintf(
                'Configured ViaRest model needs to be an instance of %s',
                CrudRequestInterface::class
            ));
        }

        return $request;
    }

}
