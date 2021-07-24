<?php
/**
 * Created by PhpStorm.
 * User: redmarbakker
 * Date: 23/05/2019
 * Time: 13:36
 */

namespace ViaRest\Http\Router;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use ViaRest\Http\Exceptions\Api\ConfigurationException;
use ViaRest\Http\Controllers\Api\DynamicRestController;
use ViaRest\Http\Controllers\Api\DynamicRestMeController;
use ViaRest\Http\Controllers\Api\DynamicRestRelationController;
use Illuminate\Http\Request;
use Route;
use ViaRest\Http\Controllers\Api\RestControllerInterface;
use ViaRest\Http\Requests\Api\DefaultRequest;
use ViaRest\Models\DynamicModelInterface;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class ViaRest
{

    /**
     * Id Validation
     *
     * @var string
     * */
    protected static $idValidation = '[0-9]+';

    /**
     * Route Group Middleware
     *
     * @var string
     * */
    protected static $middleware = '';


    /**
     * Set Id Validation
     *
     * @param $validation string
     * */
    public static function setIdValidation(string $validation)
    {
        self::$idValidation = $validation;
    }

    /**
     * Configure a middleware for the root route
     *
     * @param $middleware string
     * */
    public static function setMiddleware(string $middleware)
    {
        self::$middleware = $middleware;
    }

    /**
     * Building routes
     *
     * @param $version string
     * @param $config array
     * @throws ConfigurationException
     * @throws \Exception
     */
    public static function handle(string $version, array $config)
    {
        Route::group(['prefix' => $version, 'middleware' => self::$middleware], function () use ($config) {

            foreach ($config as $url => $route) {
                switch (true) {
                    case $route instanceof ModelRoute:
                        self::configureModelRoute($url, $route);
                        self::configureRelationRoutes($url, $route);
                        break;
                    case $route instanceof ControllerRoute:
                        self::configureControllerRoute($url, $route);
                        self::configureRelationRoutes($url, $route);
                        break;
                    case $route instanceof MeRoute:
                        self::configureMeRoute($url, $route);
                        break;
                }
            }

            Route::get('{any}', function ($any) {
                return not_found('Invalid Api Route');
            })->where('any', '.*');

        });
    }

    /**
     * Configure Model Route
     *
     * @param string $url
     * @param ModelRoute $route
     * @return void
     * @throws \Exception
     */
    protected static function configureModelRoute(string $url, ModelRoute $route, $idIntegration = true): void
    {
        $urlPostfix = '';
        if ($idIntegration == true) {
            $urlPostfix = $url .= '/{id}';
        }

        Route::get($url, function (DefaultRequest $request) use ($route) {
            $controller = new DynamicRestController($route->getTarget());

            return $controller->fetchAll($request);
        });

        Route::get($url . $urlPostfix, function (DefaultRequest $request, $id) use ($route) {
            $controller = new DynamicRestController($route->getTarget());

            return $controller->fetch($request, $id);
        })->where('id', self::$idValidation);

        Route::post($url, function (DefaultRequest $request) use ($route) {
            $controller = new DynamicRestController($route->getTarget());

            return $controller->create($request);
        });

        Route::put($url . $urlPostfix, function (DefaultRequest $request, $id) use ($route) {
            $controller = new DynamicRestController($route->getTarget());

            return $controller->update($request, $id);
        })->where('id', self::$idValidation);

        Route::delete($url . $urlPostfix, function (DefaultRequest $request, $id) use ($route) {
            $controller = new DynamicRestController($route->getTarget());

            return $controller->destroy($request, $id);
        })->where('id', self::$idValidation);
    }

    /**
     * Configure Controller Route
     *
     * @param string $url
     * @param ControllerRoute $route
     * @return void
     * @throws ConfigurationException
     */
    protected static function configureControllerRoute(string $url, ControllerRoute $route, $idIntegration = true): void
    {
        $controllerName = '\\' . $route->getTarget();

        $urlPostfix = '';
        if ($idIntegration == true) {
            $urlPostfix = '/{id}';
        }

        Route::get($url, $controllerName . '@fetchAll');

        Route::get($url . $urlPostfix, $controllerName . '@fetch')->where('id', self::$idValidation);

        Route::post($url, $controllerName . '@create');

        Route::put($url . $urlPostfix, $controllerName . '@update')->where('id', self::$idValidation);

        Route::delete($url . $urlPostfix, $controllerName . '@destroy')->where('id', self::$idValidation);

        foreach ($route->getCustoms() as $endpoint => $config) {
            $method = Request::METHOD_GET;
            $target = null;

            if (! is_array($config)) {
                throw new ConfigurationException(sprintf(
                    'Custom method with name %s does not have the right configuration, expects an array. '.
                    'See the docs: https://github.com/RedmarBakker/via-rest#configuring-your-routes',
                    $endpoint
                ));
            }

            $validator = Validator::make($config, [
                'method' => [Rule::in([
                    Request::METHOD_GET,
                    Request::METHOD_POST,
                    Request::METHOD_PUT,
                    Request::METHOD_PATCH,
                    Request::METHOD_DELETE,
                ])],
                'target' => ['required'],
                'id_integration' => ['bool'],
                'endpoint' => ['string'],
                'endpoint_where' => ['array']
            ]);

            try {
                $input = $validator->validate();
            } catch (\Exception $e) {
                throw new ConfigurationException(sprintf(
                    $validator->errors() .
                    'See the docs: https://github.com/RedmarBakker/via-rest#configuring-your-routes',
                ));
            }

            $method         = isset($input['method']) ? $input['method'] : $method;
            $target         = $input['target'] ?: $target;
            $idIntegration  = isset($input['id_integration']) ? $input['id_integration'] : $idIntegration;
            $endpoint       = isset($input['endpoint']) ? $input['endpoint'] : $endpoint;
            $endpointWhere  = isset($input['endpoint_where']) ? $input['endpoint_where'] : [];

            if ($idIntegration == true) {
                switch (strtoupper($method)) {
                    case Request::METHOD_GET:
                        Route::get($url . '/{root_id}/' . $endpoint, $controllerName . '@' . $target)
                            ->where(array_merge(['root_id' => self::$idValidation], $endpointWhere));
                        break;
                    case Request::METHOD_PUT:
                        Route::put($url . '/{root_id}/' . $endpoint, $controllerName . '@' . $target)
                            ->where(array_merge(['root_id' => self::$idValidation], $endpointWhere));
                        break;
                    case Request::METHOD_POST:
                        Route::post($url . '/{root_id}/' . $endpoint, $controllerName . '@' . $target)
                            ->where(array_merge(['root_id' => self::$idValidation], $endpointWhere));
                        break;
                    case Request::METHOD_DELETE:
                        Route::delete($url . '/{root_id}/' . $endpoint, $controllerName . '@' . $target)
                            ->where(array_merge(['root_id' => self::$idValidation], $endpointWhere));
                        break;
                }
            } else {
                switch (strtoupper($method)) {
                    case Request::METHOD_GET:
                        Route::get($url . '/' . $endpoint, $controllerName . '@' . $target);
                        break;
                    case Request::METHOD_PUT:
                        Route::put($url . '/' . $endpoint, $controllerName . '@' . $target);
                        break;
                    case Request::METHOD_POST:
                        Route::post($url . '/' . $endpoint, $controllerName . '@' . $target);
                        break;
                    case Request::METHOD_DELETE:
                        Route::delete($url . '/' . $endpoint, $controllerName . '@' . $target);
                        break;
                }
            }
        }
    }

    /**
     * Configure Me Route
     *
     * @param string $url
     * @param MeRoute $route
     * @return void
     * */
    protected static function configureMeRoute(string $url, MeRoute $route): void
    {
        Route::group(['middleware' => 'me-injection'], function () use ($url, $route) {
            switch (true) {
                case $route->getLinkedRoute() instanceof ModelRoute:

                    self::configureModelRoute($url, $route->getLinkedRoute(), false);

                    break;
                case $route->getLinkedRoute() instanceof ControllerRoute:

                    self::configureControllerRoute($url, $route->getLinkedRoute(), false);

                    break;
            }

            self::configureRelationRoutes($url, $route->getLinkedRoute(), false);
        });
    }

    /**
     * Configure Me Route
     *
     * @param string $url
     * @param RouteInterface $route
     * @return void
     * @throws ConfigurationException
     */
    protected static function configureRelationRoutes($url, RouteInterface $route, $idIntegration = true): void
    {
        foreach ($route->getRelations() as $relation => $relationOptions) {
            $create = false;
            $attach = false;
            $relationClass = '';

            if (is_array($relationOptions)) {
                $validator = Validator::make($relationOptions, [
                    'relation_class' => ['required'],
                    'create' => ['bool'],
                    'attach' => ['bool'],
                ]);

                try {
                    $input = $validator->validate();
                } catch (\Exception $e) {
                    throw new ConfigurationException(sprintf(
                        $validator->errors() .
                        'See the docs: https://github.com/RedmarBakker/via-rest#configuring-your-routes',
                    ));
                }

                $create         = isset($input['create']) ? $input['create'] : $create;
                $attach         = isset($input['attach']) ? $input['attach'] : $attach;
                $relationClass  = $input['relation_class'] ?: $relationClass;
            } else {
                $relationClass = $relationOptions;
            }

            if ($idIntegration == true) {
                $url = $url .= '/{root_id}';
            }

            Route::get($url . '/' . $relation, function (...$args) use ($route, $relation, $relationClass) {
                if (count($args) == 1) {
                    list($rootId) = $args;
                } else {
                    $rootId = Auth::user()->id;
                }

                $relation = lcfirst(str_replace(' ', '', ucwords(str_replace('-', ' ', $relation))));
                $controller = new DynamicRestRelationController($route->getTarget(), $rootId, $relation, $relationClass);

                return $controller->fetchAll(app(Request::class));
            })->where('root_id', self::$idValidation);

            if ($create == true) {
                Route::post($url . '/' . $relation, function (...$args) use ($route, $relation, $relationClass) {
                    if (count($args) == 1) {
                        list($rootId) = $args;
                    } else {
                        $rootId = Auth::user()->id;
                    }

                    $relation = lcfirst(str_replace(' ', '', ucwords(str_replace('-', ' ', $relation))));
                    $controller = new DynamicRestRelationController($route->getTarget(), $rootId, $relation, $relationClass);

                    return $controller->create(app(Request::class));
                })->where('root_id', self::$idValidation);
            }

            if ($attach == true) {
                Route::post($url . '/' . $relation . '/{target_id}', function (...$args) use ($route, $relation, $relationClass) {
                    if (count($args) == 2) {
                        list($targetId, $rootId) = $args;
                    } else {
                        list($targetId) = $args;
                        $rootId = Auth::user()->id;
                    }

                    $relation = lcfirst(str_replace(' ', '', ucwords(str_replace('-', ' ', $relation))));
                    $controller = new DynamicRestRelationController($route->getTarget(), $rootId, $relation, $relationClass);

                    return $controller->attach(app(Request::class), $targetId);
                })->where(['root_id' => self::$idValidation, 'target_id' => self::$idValidation]);
            }

            Route::delete($url . '/' . $relation . '/{target_id}', function (...$args) use ($route, $relation, $relationClass) {
                if (count($args) == 2) {
                    list($targetId, $rootId) = $args;
                } else {
                    list($targetId) = $args;
                    $rootId = Auth::user()->id;
                }

                $relation = lcfirst(str_replace(' ', '', ucwords(str_replace('-', ' ', $relation))));
                $controller = new DynamicRestRelationController($route->getTarget(), $rootId, $relation, $relationClass);

                return $controller->destroy(app(Request::class), $targetId);
            })->where(['root_id' => self::$idValidation, 'target_id' => self::$idValidation]);
        }
    }

    /**
     * Model config
     *
     * @param $model string
     * @param $relations array
     * @return ModelRoute
     * @throws ConfigurationException
     * */
    public static function model(string $model, array $relations = []): ModelRoute
    {
        if (! class_exists($model)) {
            throw new ConfigurationException(sprintf(
                'Model class "%s" could not be found and initialized. Please configure the full ' .
                'model name. See the docs: https://github.com/RedmarBakker/via-rest#configuring-your-routes',
                $model
            ));
        }

        try {
            $ref = new \ReflectionClass($model);
        } catch (\ReflectionException $e) {
            throw new ConfigurationException(sprintf(
                'Reflection: %s',
                $e->getMessage()
            ));
        }

        if (! $ref->implementsInterface(DynamicModelInterface::class)) {
            throw new ConfigurationException(sprintf(
                'Model %s needs to implement %s to work correctly. See the docs: ' .
                'https://github.com/RedmarBakker/via-rest#setting-up-a-model',
                $model,
                DynamicModelInterface::class
            ));
        }

        return new ModelRoute($model, $relations);
    }

    /**
     * Controller config
     *
     * @param $controller string
     * @param $relations array
     * @param $customs array
     * @return ControllerRoute
     * @throws ConfigurationException
     * */
    public static function controller($controller, $relations = [], $customs = []): ControllerRoute
    {
        if (! class_exists($controller)) {
            throw new ConfigurationException(sprintf(
                'Controller class "%s" could not be found and initialized. Please configure the full ' .
                'controller name. See the docs: https://github.com/RedmarBakker/via-rest#configuring-your-routes',
                $controller
            ));
        }

        try {
            $ref = new \ReflectionClass($controller);
        } catch (\ReflectionException $e) {
            throw new ConfigurationException(sprintf(
                'Reflection: %s',
                $e->getMessage()
            ));
        }

        if (! $ref->implementsInterface(RestControllerInterface::class)) {
            throw new ConfigurationException(sprintf(
                'Controller %s needs to implement %s to work correctly. See the docs: ' .
                'https://github.com/RedmarBakker/via-rest#setting-up-a-controller',
                $controller,
                RestControllerInterface::class
            ));
        }

        return new ControllerRoute($controller, $relations, $customs);
    }

    /**
     * Me config
     *
     * @param $route RouteInterface
     * @return MeRoute
     * */
    public static function me(RouteInterface $route)
    {
        return new MeRoute($route);
    }
}