<?php

namespace ViaRest\Http\Controllers\Api;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;
use ViaRest\Exceptions\Api\ConfigurationException;
use ViaRest\Models\DynamicModelInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Validation\Rule;

class DynamicRestRelationController extends AbstractRestController
{

    /**
     * @var string
     * */
    protected $rootClass;

    /**
     * @var int
     * */
    protected $rootId;

    /**
     * @var string
     * */
    protected $relation;

    /**
     * @var string
     * */
    protected $relationClass;


    /**
     * Constructor
     *
     * @throws ConfigurationException
     * @param string $rootElement
     * @param $rootId
     * @param string $relation
     * @param string $relationClass
     * */
    public function __construct(string $rootElement, $rootId, string $relation, string $relationClass)
    {
        try {
            $reflection = new \ReflectionClass($rootElement);
            if ($reflection->implementsInterface(RestControllerInterface::class)) {
                $this->rootClass = call_user_func([$rootElement, 'getModelClass']);
            } else if($reflection->implementsInterface(DynamicModelInterface::class)) {
                $this->rootClass = $rootElement;
            } else {
                throw new ConfigurationException(sprintf(
                    'Model class could not be fetched. Are you sure you followed the docs? See the docs: ' .
                    'https://github.com/RedmarBakker/via-rest'
                ));
            }
        } catch (\ReflectionException $e) {
            throw new ConfigurationException(sprintf(
                'Model class could not be fetched. Check your route target. See the docs: ' .
                'https://github.com/RedmarBakker/via-rest'
            ));
        }

        $this->rootId        = $rootId;
        $this->relation      = $relation;
        $this->relationClass = $relationClass;

        $this->checkRelations();
    }

    /**
     * Check if this configuration is correct.
     *
     * @throws ConfigurationException
     * @return boolean
     */
    public function checkRelations()
    {
        try {
            $rootReflection = new \ReflectionClass($this->rootClass);

            if (! $rootReflection->hasMethod($this->relation)) {
                throw new ConfigurationException(sprintf(
                    'Model %s could not be configured as a relation route, because relation %s was not found. See the docs: ' .
                    'https://github.com/RedmarBakker/via-rest#configuring-api-routes-with-a-relation',
                    $this->rootClass,
                    $this->relation
                ));
            }

            $relationMethod = $rootReflection->getMethod($this->relation);
            $return = $relationMethod->invoke(new $this->rootClass);

            if (! $return instanceof Relation) {
                throw new ConfigurationException(sprintf(
                    'Model %s could not be configured as a relation route, because relation %s was of type %s. See the docs: ' .
                    'https://github.com/RedmarBakker/via-rest#configuring-api-routes-with-a-relation',
                    $this->rootClass,
                    $this->relation,
                    Relation::class
                ));
            }
        } catch (\ReflectionException $e) {
            throw new ConfigurationException(sprintf(
                'Model %s could not be configured as a relation route. See the docs: ' .
                'https://github.com/RedmarBakker/via-rest#configuring-api-routes-with-a-relation',
                $this->rootClass
            ));
        }

        return true;
    }

    /**
     * Create new model
     *
     * @param $request Request
     * @return JsonResponse
     */
    public function create(Request $request): JsonResponse
    {
        try {
            $createRequest = call_user_func([$this->rootClass, 'instanceCreateRequest']);

            if (!$createRequest->authorize()) {
                return $this->forbidden();
            }

            $validator = Validator::make($request->all(), $createRequest->rules());

            try {
                $input = $validator->validate();
            } catch (\Exception $e) {
                return $this->invalidInput($validator->errors());
            }

            $target = new $this->relationClass($input);
            $root = call_user_func([$this->rootClass, 'find'], $this->rootId);
            $root->{$this->relation}()->save($target);

            return ok([
                Str::singular($this->relation) => $target
            ]);
        } catch (\Exception $e) {
            return error_json_response($e->getMessage(), $e->getTrace(), 500);
        }
    }

    /**
     * Create new model
     *
     * @param $request Request
     * @param $id
     * @return JsonResponse
     */
    public function attach(Request $request, $id): JsonResponse
    {
        try {
            if ($this->rootClass == $this->relationClass && $this->rootId == $id) {
                return json_response([
                    'message' => 'Can not attach item to it\'s self.'
                ], 400);
            }

            $root = call_user_func([$this->rootClass, 'find'], $this->rootId);

            if (! $root->{$this->relation}->contains($id)) {
                $root->{$this->relation}()->attach($id);
            } else {
                return json_response([
                    'message' => 'Item already attached.'
                ], 400);
            }

            return ok([
                Str::singular($this->relation) => call_user_func([$this->relationClass, 'find'], $id)
            ]);
        } catch (\Exception $e) {
            return error_json_response($e->getMessage(), $e->getTrace(), 500);
        }
    }

    /**
     * Fetch a model
     *
     * @param $id int
     * @param $request Request
     * @return JsonResponse
     */
    public function fetch(Request $request, $id): JsonResponse
    {
        return $this->notAllowed();
    }

    /**
     * Fetch all models
     *
     * @param $request Request
     * @return JsonResponse
     */
    public function fetchAll(Request $request): JsonResponse
    {
        $fetchAllRequest = call_user_func([$this->rootClass, 'instanceFetchAllRequest']);

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
            $root = call_user_func([$this->rootClass, 'find'], $this->rootId);

            $result = $root->{$this->relation}();
            $result->get()->load($input['relations'] ?? []);

            $orderDirection = $input['order_direction'] ?? self::ORDER_DIRECTION;
            if ($orderDirection == 'random') {
                return ok([
                    'data' => $result->inRandomOrder()->get()
                ]);
            } else {
                $result->orderBy($input['order_identifier'] ?? self::ORDER_IDENTIFIER, $orderDirection);
            }

            return ok(
                $result->paginate($input['limit'] ?? self::LIMIT)
            );

        } catch (\Exception $e) {
            return error_json_response('Something went wrong. Relation not fully configured.', [$e->getMessage()], 500);
        }
    }

    /**
     * Update a model
     *
     * @param $id int
     * @param $request Request
     * @return JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        return $this->notAllowed();
    }

    /**
     * Destroy a model
     *
     * @param $id int
     * @param $request Request
     * @return JsonResponse
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        try {
            $root = call_user_func([$this->rootClass, 'find'], $this->rootId);

            if (! $root->{$this->relation}->contains($id)) {
                return $this->notFound();
            }

            $root->{$this->relation}()->detach($id);

            return ok();
        } catch (\Exception $e) {
            return error_json_response($e->getMessage(), $e->getTrace(), 500);
        }
    }

}
