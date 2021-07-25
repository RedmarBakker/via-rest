<?php

namespace ViaRest\Http\Controllers\Api;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Event;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use ViaRest\Providers\CacheProvider;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Auth;

abstract class AbstractRestController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    use RestResponseControllerTrait;


    /**
     * @var string
     * */
    const ORDER_IDENTIFIER = 'id';

    /**
     * @var string
     * */
    const ORDER_DIRECTION = 'ASC';

    /**
     * @var string
     * */
    const LIMIT = 15;


    /**
     * @var CacheProvider
     * */
    private $cacheProvider;

    /**
     * Version variable, used for fitting requirements by version
     *
     * @var string
     * */
    protected $version = null;


    /**
     * Constructor
     * */
    public function __construct()
    {
        $this->cacheProvider = new CacheProvider(__CLASS__);
    }

    /**
     * Fetch All
     *
     * @param $request FormRequest
     * @return Model|string
     * */
    public function fetchAll(Request $request)
    {
        $fetchAllRequest = call_user_func([
            $this::getModelClass(),
            'instanceFetchAllRequest'
        ], $this->version);

        if (!$fetchAllRequest->authorize()) {
            return $this->forbidden();
        }

        $validator = Validator::make($request->all(), $fetchAllRequest->rules());

        try {
            $input = $validator->validate();
        } catch (\Exception $e) {
            return $this->invalidInput($validator->errors());
        }

        try {
            return static::doFetchAll($input);
        } catch (\Exception $e) {
            return error_json_response($e->getMessage(), $e->getTrace(), 500);
        }
    }

    /**
     * Fetch
     *
     * @param $request FormRequest
     * @return Model|string
     * */
    public function fetch(Request $request, $id)
    {
        if ($this instanceof DynamicRestMeController) {
            $id = Auth::user()->id;
        }

        $fetchRequest = call_user_func([
            $this::getModelClass(),
            'instanceFetchRequest'
        ], $this->version);

        if (!$fetchRequest->authorize()) {
            return $this->forbidden();
        }

        $validator = Validator::make($request->all(), $fetchRequest->rules());

        try {
            $input = $validator->validate();
        } catch (\Exception $e) {
            return $this->invalidInput($validator->errors());
        }

        try {
            return static::doFetch($id, $input);
        } catch (\Exception $e) {
            return error_json_response($e->getMessage(), $e->getTrace(), 500);
        }
    }

    /**
     * Create
     *
     * @param $request FormRequest
     * @return Model|string
     * */
    public function create(Request $request)
    {
        $createRequest = call_user_func([
            $this::getModelClass(),
            'instanceCreateRequest'
        ], $this->version);

        if (!$createRequest->authorize()) {
            return $this->forbidden();
        }

        $validator = Validator::make($request->all(), $createRequest->rules());

        try {
            $input = $validator->validate();
        } catch (\Exception $e) {
            return $this->invalidInput($validator->errors());
        }

        try {
            return static::doCreate($input);
        } catch (\Exception $e) {
            return error_json_response($e->getMessage(), $e->getTrace(), 500);
        }
    }

    /**
     * Update
     *
     * @param $request FormRequest
     * @return Model|string
     * */
    public function update(Request $request, $id)
    {
        if ($this instanceof DynamicRestMeController) {
            $id = Auth::user()->id;
        }

        $updateRequest = call_user_func([
            $this::getModelClass(),
            'instanceUpdateRequest'
        ], $this->version);

        if (!$updateRequest->authorize()) {
            return $this->forbidden();
        }

        $validator = Validator::make($request->all(), $updateRequest->rules());

        try {
            $input = $validator->validate();
        } catch (\Exception $e) {
            return $this->invalidInput($validator->errors());
        }

        try {
            return static::doUpdate($id, $input);
        } catch (\Exception $e) {
            return error_json_response($e->getMessage(), $e->getTrace(), 500);
        }
    }

    /**
     * Destroy
     *
     * @param $request FormRequest
     * @return Model|string
     * @throws \Exception
     * */
    public function destroy(Request $request, $id)
    {
        if ($this instanceof DynamicRestMeController) {
            $id = Auth::user()->id;
        }

        $destroyRequest = call_user_func([
            $this::getModelClass(),
            'instanceDestroyRequest'
        ], $this->version);

        if (!$destroyRequest->authorize()) {
            return $this->forbidden();
        }

        $validator = Validator::make($request->all(), $destroyRequest->rules());

        try {
            $input = $validator->validate();
        } catch (\Exception $e) {
            return $this->invalidInput($validator->errors());
        }

        try {
            return static::doDestroy($id, $input);
        } catch (\Exception $e) {
            return error_json_response($e->getMessage(), $e->getTrace(), 500);
        }
    }

    /**
     * Create new model
     *
     * @param $input array
     * @return JsonResponse
     */
    public function doCreate(array $input): JsonResponse
    {
        return ok([
            'data' => call_user_func([
                $this::getModelClass(),
                'create'
            ], $input)->refresh()
        ]);
    }

    /**
     * Fetch a model
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function doFetch($id, array $input): JsonResponse
    {
        $item = call_user_func(
            [$this::getModelClass(), 'with'],
            $input['relations'] ?? []
        )->find($id);

        if ($item == null) {
            return $this->notFound();
        }

        return ok([
            'data' => $item
        ]);
    }

    /**
     * Fetch all models
     *
     * @param $input array
     * @return JsonResponse
     */
    public function doFetchAll(array $input): JsonResponse
    {
        $result = call_user_func(
            [$this::getModelClass(), 'with'],
            $input['relations'] ?? []);

        $orderDirection = $input['order_direction'] ?? self::ORDER_DIRECTION;
        if ($orderDirection == 'random') {
            return ok([
                'data' => $result->inRandomOrder()->get()
            ]);
        } else {
            $result->orderBy(
                $input['order_identifier'] ?? self::ORDER_IDENTIFIER,
                $orderDirection
            );
        }

        return ok(
            $result->paginate($input['limit'] ?? self::LIMIT)
        );
    }

    /**
     * Update a model
     *
     * @param $id
     * @param $input array
     * @return JsonResponse
     */
    public function doUpdate($id, array $input): JsonResponse
    {
        /** @var $item Model */
        $item = call_user_func([$this::getModelClass(), 'find'], $id);

        if ($item == null) {
            return $this->notFound();
        }

        $item->update($input);

        return ok([
            'data' => $item
        ]);
    }

    /**
     * Destroy a model
     *
     * @param $id
     * @param $input array
     * @return JsonResponse
     * @throws \Exception
     */
    public function doDestroy($id, array $input): JsonResponse
    {
        /** @var $item Model */
        $item = call_user_func([$this::getModelClass(), 'find'], $id);

        if ($item == null) {
            return $this->notFound();
        }

        $item->delete();

        return ok([
            'data' => []
        ]);
    }

    public function callAction($method, $parameters)
    {
        $injectedParameters = last(event('me-injection-check', [$parameters]));

        if (is_array($injectedParameters) && count($injectedParameters) > 0) {
            $parameters = $injectedParameters;
        }

        return $this->{$method}(...array_values($parameters));
    }

}