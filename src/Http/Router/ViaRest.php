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
     * All route permissions
     *
     * @var array
     * */
    protected static $routePermissions = [];


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
     * @param $middleware string|array
     * */
    public static function setMiddleware($middleware)
    {
        self::$middleware = $middleware;
    }

    /**
     * Get all used route permissions
     *
     * @return array
     * */
    public static function getRoutePermissions(): array
    {
        return self::$routePermissions;
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

            Route::any('{any}', function ($any) {
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
    protected static function configureModelRoute(string $url, ModelRoute $route, $meIntegration = false): void
    {
        $urlPostfix = '/{id}';
        if ($meIntegration == true) {
            $urlPostfix = '';
        }

        if (in_array(AbstractRoute::READ_ALL_ACTION, $route->getActions())) {
            Route::group(['middleware' => $meIntegration ? [] : 'token-can:' . $route->getPermission() . ':update'], function() use ($url, $urlPostfix, $route) {
                Route::get($url, function (DefaultRequest $request) use ($route) {
                    $controller = new DynamicRestController($route->getTarget());

                    return $controller->fetchAll($request);
                });
            });

            self::registerRoutePermission($route->getPermission() . ':read');
        }

        if (in_array(AbstractRoute::READ_ONE_ACTION, $route->getActions())) {
            Route::group(['middleware' => $meIntegration ? [] : 'token-can:' . $route->getPermission() . ':read'], function() use ($url, $urlPostfix, $route) {
                Route::get($url . $urlPostfix, function (DefaultRequest $request, $id) use ($route) {
                    $controller = new DynamicRestController($route->getTarget());

                    return $controller->fetch($request, $id);
                })->where('id', self::$idValidation);
            });

            self::registerRoutePermission($route->getPermission() . ':read');
        }

        if (in_array(AbstractRoute::CREATE_ACTION, $route->getActions())) {
            Route::group(['middleware' => $meIntegration ? [] : 'token-can:' . $route->getPermission() . ':create'], function() use ($url, $urlPostfix, $route) {
                Route::post($url, function (DefaultRequest $request) use ($route) {
                    $controller = new DynamicRestController($route->getTarget());

                    return $controller->create($request);
                });
            });

            self::registerRoutePermission($route->getPermission() . ':create');
        }

        if (in_array(AbstractRoute::UPDATE_ACTION, $route->getActions())) {
            Route::group(['middleware' => $meIntegration ? [] : 'token-can:' . $route->getPermission() . ':update'], function() use ($url, $urlPostfix, $route) {
                Route::put($url . $urlPostfix, function (DefaultRequest $request, $id) use ($route) {
                    $controller = new DynamicRestController($route->getTarget());

                    return $controller->update($request, $id);

                })->where('id', self::$idValidation);

            });

            self::registerRoutePermission($route->getPermission() . ':update');
        }

        if (in_array(AbstractRoute::DELETE_ACTION, $route->getActions())) {
            Route::group(['middleware' => $meIntegration ? [] : 'token-can:' . $route->getPermission() . ':update'], function() use ($url, $urlPostfix, $route) {
                Route::delete($url . $urlPostfix, function (DefaultRequest $request, $id) use ($route) {
                    $controller = new DynamicRestController($route->getTarget());

                    return $controller->destroy($request, $id);

                })->where('id', self::$idValidation);
            });

            self::registerRoutePermission($route->getPermission() . ':delete');
        }
    }

    /**
     * Configure Controller Route
     *
     * @param string $url
     * @param ControllerRoute $route
     * @return void
     * @throws ConfigurationException
     */
    protected static function configureControllerRoute(string $url, ControllerRoute $route, $meIntegration = false): void
    {
        $controllerName = '\\' . $route->getTarget();

        $urlPostfix = '/{id}';
        if ($meIntegration == true) {
            $urlPostfix = '';
        }

        if (in_array(AbstractRoute::READ_ALL_ACTION, $route->getActions())) {
            Route::group(['middleware' => $meIntegration ? [] : 'token-can:' . $route->getPermission() . ':read'], function() use ($route, $url, $controllerName) {
                Route::get($url, $controllerName . '@fetchAll');
            });

            self::registerRoutePermission($route->getPermission() . ':read');
        }

        if (in_array(AbstractRoute::READ_ONE_ACTION, $route->getActions())) {
            Route::group(['middleware' => $meIntegration ? [] : 'token-can:' . $route->getPermission() . ':read'], function () use ($route, $url, $urlPostfix, $controllerName) {
                Route::get($url . $urlPostfix, $controllerName . '@fetch')->where('id', self::$idValidation);
            });

            self::registerRoutePermission($route->getPermission() . ':read');
        }

        if (in_array(AbstractRoute::CREATE_ACTION, $route->getActions())) {
            Route::group(['middleware' => $meIntegration ? [] : 'token-can:' . $route->getPermission() . ':create'], function () use ($route, $url, $controllerName) {
                Route::post($url, $controllerName . '@create');
            });

            self::registerRoutePermission($route->getPermission() . ':create');
        }

        if (in_array(AbstractRoute::UPDATE_ACTION, $route->getActions())) {
            Route::group(['middleware' => $meIntegration ? [] : 'token-can:' . $route->getPermission() . ':update'], function () use ($route, $url, $urlPostfix, $controllerName) {
                Route::put($url . $urlPostfix, $controllerName . '@update')->where('id', self::$idValidation);
            });

            self::registerRoutePermission($route->getPermission() . ':update');
        }

        if (in_array(AbstractRoute::DELETE_ACTION, $route->getActions())) {
            Route::group(['middleware' => $meIntegration ? [] : 'token-can:' . $route->getPermission() . ':delete'], function () use ($route, $url, $urlPostfix, $controllerName) {
                Route::delete($url . $urlPostfix, $controllerName . '@destroy')->where('id', self::$idValidation);
            });

            self::registerRoutePermission($route->getPermission() . ':delete');
        }

        foreach ($route->getEndpoints() as $endpointUri => $endpoint) {
            if (! $endpoint instanceof Endpoint) {
                $endpoint = new Endpoint($endpoint);
            }

            Route::group(['middleware' => 'token-can:' . $route->getPermission() . ':' . $endpoint->target], function() use ($route, $url, $endpointUri, $endpoint, $controllerName, $meIntegration) {
                if (! $meIntegration && $endpoint->idIntegration) {
                    switch (strtoupper($endpoint->method)) {
                        case Request::METHOD_GET:
                            Route::get($url . '/{root_id}/' . $endpointUri, $controllerName . '@' . $endpoint->target)
                                ->where(array_merge(['root_id' => self::$idValidation], $endpoint->where));
                            break;
                        case Request::METHOD_PUT:
                            Route::put($url . '/{root_id}/' . $endpointUri, $controllerName . '@' . $endpoint->target)
                                ->where(array_merge(['root_id' => self::$idValidation], $endpoint->where));
                            break;
                        case Request::METHOD_POST:
                            Route::post($url . '/{root_id}/' . $endpointUri, $controllerName . '@' . $endpoint->target)
                                ->where(array_merge(['root_id' => self::$idValidation], $endpoint->where));
                            break;
                        case Request::METHOD_DELETE:
                            Route::delete($url . '/{root_id}/' . $endpointUri, $controllerName . '@' . $endpoint->target)
                                ->where(array_merge(['root_id' => self::$idValidation], $endpoint->where));
                            break;
                    }
                } else {
                    switch (strtoupper($endpoint->method)) {
                        case Request::METHOD_GET:
                            Route::get($url . '/' . $endpointUri, $controllerName . '@' . $endpoint->target)
                                ->where($endpoint->where);
                            break;
                        case Request::METHOD_PUT:
                            Route::put($url . '/' . $endpointUri, $controllerName . '@' . $endpoint->target)
                                ->where($endpoint->where);
                            break;
                        case Request::METHOD_POST:
                            Route::post($url . '/' . $endpointUri, $controllerName . '@' . $endpoint->target)
                                ->where($endpoint->where);
                            break;
                        case Request::METHOD_DELETE:
                            Route::delete($url . '/' . $endpointUri, $controllerName . '@' . $endpoint->target)
                                ->where($endpoint->where);
                            break;
                    }
                }
            });

            self::registerRoutePermission($route->getPermission() . ':' . $endpoint->target);
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
        Route::group(['middleware' => ['me-injection', 'token-can:' . $route->getLinkedRoute()->getPermission()]], function () use ($url, $route) {
            switch (true) {
                case $route->getLinkedRoute() instanceof ModelRoute:

                    self::configureModelRoute($url, $route->getLinkedRoute(), true);

                    break;
                case $route->getLinkedRoute() instanceof ControllerRoute:

                    self::configureControllerRoute($url, $route->getLinkedRoute(), true);

                    break;
            }

            self::configureRelationRoutes($url, $route->getLinkedRoute(), false);
        });

        self::registerRoutePermission($route->getLinkedRoute()->getPermission());
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
        foreach ($route->getRelations() as $endpoint => $relation) {
            //$bidirectional = false;
            //$bidirectional  = isset($input['bidirectional']) ? $input['bidirectional'] : $bidirectional;

            if (! $relation instanceof Relation) {
                $relation = new Relation($relation);
            }

            $relationClass  = $relation->relation;

            if ($idIntegration == true) {
                $url .= '/{root_id}';
            }

            $relationKey = lcfirst(str_replace(' ', '', ucwords(str_replace('-', ' ', $endpoint))));

            Route::get($url . '/' . $endpoint, function (...$args) use ($route, $relationKey, $relationClass) {
                if (count($args) == 1) {
                    list($rootId) = $args;
                } else {
                    $rootId = Auth::user()->id;
                }

                $controller = new DynamicRestRelationController($route->getTarget(), $rootId, $relationKey, $relationClass);

                return $controller->fetchAll(app(Request::class));
            })->where('root_id', self::$idValidation);

            if ($relation->canCreate) {
                Route::post($url . '/' . $endpoint, function (...$args) use ($route, $relationKey, $relationClass) {
                    if (count($args) == 1) {
                        list($rootId) = $args;
                    } else {
                        $rootId = Auth::user()->id;
                    }

                    $controller = new DynamicRestRelationController($route->getTarget(), $rootId, $relationKey, $relationClass);

                    return $controller->create(app(Request::class));
                })->where('root_id', self::$idValidation);
            }

            if ($relation->canAttach) {
                Route::post($url . '/' . $endpoint . '/{target_id}', function (...$args) use ($route, $relationKey, $relationClass) {
                    if (count($args) == 2) {
                        list($targetId, $rootId) = $args;
                    } else {
                        list($targetId) = $args;
                        $rootId = Auth::user()->id;
                    }

                    $controller = new DynamicRestRelationController($route->getTarget(), $rootId, $relationKey, $relationClass);

                    return $controller->attach(app(Request::class), $targetId);
                })->where(['root_id' => self::$idValidation, 'target_id' => self::$idValidation]);
            }

            if ($relation->canDelete) {
                if ($relation->softDelete) {
                    Route::delete($url . '/' . $endpoint . '/{target_id}', function (...$args) use ($route, $relationKey, $relationClass) {
                        if (count($args) == 2) {
                            list($targetId, $rootId) = $args;
                        } else {
                            list($targetId) = $args;
                            $rootId = Auth::user()->id;
                        }

                        $controller = new DynamicRestRelationController($route->getTarget(), $rootId, $relationKey, $relationClass);

                        return $controller->detach(app(Request::class), $targetId);
                    })->where(['root_id' => self::$idValidation, 'target_id' => self::$idValidation]);
                } else {
                    Route::delete($url . '/' . $endpoint . '/{target_id}', function (...$args) use ($route, $relationKey, $relationClass) {
                        if (count($args) == 2) {
                            list($targetId, $rootId) = $args;
                        } else {
                            list($targetId) = $args;
                            $rootId = Auth::user()->id;
                        }

                        $controller = new DynamicRestRelationController($route->getTarget(), $rootId, $relationKey, $relationClass);

                        return $controller->destroy(app(Request::class), $targetId);
                    })->where(['root_id' => self::$idValidation, 'target_id' => self::$idValidation]);
                }
            }
        }
    }

    /**
     * Model config
     *
     * @param $model string
     * @return ModelRoute
     * @throws ConfigurationException
     * */
    public static function model(string $model): ModelRoute
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

        return new ModelRoute($model);
    }

    /**
     * Controller config
     *
     * @param $controller string
     * @return ControllerRoute
     * @throws ConfigurationException
     * */
    public static function controller(string $controller): ControllerRoute
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

        return new ControllerRoute($controller);
    }

    /**
     * Me config
     *
     * @param $route RouteInterface
     * @return MeRoute
     * */
    public static function me(RouteInterface $route)
    {
        $newActionList = [];
        foreach ($route->getActions() as $requestedAction) {
            if (in_array($requestedAction, MeRoute::ALLOWED_ACTIONS)) {
                $newActionList[] = $requestedAction;
            }
        }

        $route->setActions($newActionList);

        return new MeRoute($route);
    }

    /**
     * Relation config
     *
     * @param string $relation
     * @return Relation
     * */
    public static function relation(string $relation)
    {
        return new Relation($relation);
    }

    /**
     * Relation config
     *
     * @param string $target
     * @return Endpoint
     * */
    public static function endpoint(string $target)
    {
        return new Endpoint($target);
    }

    /**
     * Registration of all permissions used in the ViaRest api routes
     *
     * @param string $permission
     * */
    protected static function registerRoutePermission(string $permission): void
    {
        if (! in_array($permission, self::$routePermissions)) {
            self::$routePermissions[] = $permission;
        }
    }

}
