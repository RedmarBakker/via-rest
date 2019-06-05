<?php
/**
 * Created by PhpStorm.
 * User: redmarbakker
 * Date: 23/05/2019
 * Time: 13:36
 */

namespace ViaRest\Http\Router;

use ViaRest\Http\Controllers\Api\DynamicRestController;
use ViaRest\Http\Controllers\Api\DynamicRestRelationController;
use ViaRest\Http\Requests\Api\CreateRequest;
use ViaRest\Http\Requests\Api\DestroyRequest;
use ViaRest\Http\Requests\Api\FetchAllRequest;
use ViaRest\Http\Requests\Api\FetchRequest;
use ViaRest\Http\Requests\Api\UpdateRequest;
use Illuminate\Http\Request;
use Route;

class ViaRest
{

    public static function handle(string $version, array $config)
    {
        Route::group(['prefix' => $version], function () use ($config) {

            foreach ($config as $url => $via) {
                /** @var $via self */

                if ($via instanceof ModelRoute) {

                    Route::get($url, function (FetchAllRequest $request) use ($via) {
                        $modelName  = $via->getTarget();
                        $controller = new DynamicRestController(new $modelName());

                        return $controller->fetchAll($request);
                    });

                    Route::get($url . '/{id}', function (FetchRequest $request, $id) use ($via) {
                        $modelName  = $via->getTarget();
                        $controller = new DynamicRestController(new $modelName());

                        return $controller->fetch($request, $id);
                    })->where('id', '[0-9]+');

                    Route::post($url, function (CreateRequest $request) use ($via) {
                        $modelName  = $via->getTarget();
                        $controller = new DynamicRestController(new $modelName());

                        return $controller->create($request);
                    });

                    Route::put($url . '/{id}', function (UpdateRequest $request, $id) use ($via) {
                        $modelName  = $via->getTarget();
                        $controller = new DynamicRestController(new $modelName());

                        return $controller->update($request, $id);
                    })->where('id', '[0-9]+');

                    Route::delete($url . '/{id}', function (DestroyRequest $request, $id) use ($via) {
                        $modelName  = $via->getTarget();
                        $controller = new DynamicRestController(new $modelName());

                        return $controller->destroy($request, $id);
                    })->where('id', '[0-9]+');

                } elseif ($via instanceof ControllerRoute) {

                    $controllerName = '\\' . $via->getTarget();

                    Route::get($url, $controllerName . '@fetchAll');

                    Route::get($url . '/{id}', $controllerName . '@fetch')->where('id', '[0-9]+');

                    Route::post($url, $controllerName . '@create');

                    Route::put($url . '/{id}', $controllerName . '@update')->where('id', '[0-9]+');

                    Route::delete($url . '/{id}', $controllerName . '@destroy')->where('id', '[0-9]+');

                    foreach ($via->getCustoms() as $endpoint => $conf) {
                        list($method, $action) = $conf;

                        switch (strtoupper($method)) {
                            case Request::METHOD_GET:
                                Route::get($url . '/' . $endpoint, $controllerName . '@' . $action);
                            case Request::METHOD_PUT:
                                Route::put($url . '/' . $endpoint, $controllerName . '@' . $action);
                            case Request::METHOD_POST:
                                Route::post($url . '/' . $endpoint, $controllerName . '@' . $action);
                            case Request::METHOD_DELETE:
                                Route::delete($url . '/' . $endpoint, $controllerName . '@' . $action);
                        }
                    }

                } else {
                    continue;
                }

                foreach ($via->getRelations() as $route => $relation) {

                    Route::get($url . '/{join_id}/' . $route, function (FetchAllRequest $request, $joinId) use ($via, $relation) {
                        $refl = new \ReflectionClass($via->getTarget());
                        $identifier = str_replace('controller', '', strtolower($refl->getShortName()));
                        $controller = new DynamicRestRelationController(new $relation(), $identifier . '_id', $joinId);

                        return $controller->fetchAll($request);
                    })->where('join_id', '[0-9]+');

                    Route::post($url . '/{join_id}/' . $route, function (CreateRequest $request, $joinId) use ($via, $relation) {
                        $refl = new \ReflectionClass($via->getTarget());
                        $identifier = str_replace('controller', '', strtolower($refl->getShortName()));
                        $controller = new DynamicRestRelationController(new $relation(), $identifier . '_id', $joinId);

                        return $controller->create($request);
                    })->where('join_id', '[0-9]+');

                }

            }

            Route::get('{any}', function ($any) {
                return not_found('Invalid Api Route');
            })->where('any', '.*');

        });
    }

    /**
     * Model config
     *
     * @param $model string
     * @param $relations array
     * @return ModelRoute
     * */
    public static function model(string $model, array $relations = []): ModelRoute
    {
        return new ModelRoute($model, $relations);
    }

    /**
     * Controller config
     *
     * @param $controller string
     * @param $relations array
     * @param $customs array
     * @return ControllerRoute
     * */
    public static function controller($controller, $relations = [], $customs = []): ControllerRoute
    {
        return new ControllerRoute($controller, $relations, $customs);
    }

}