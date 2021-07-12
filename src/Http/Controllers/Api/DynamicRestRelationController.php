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

class DynamicRestRelationController extends AbstractRestController implements RestControllerInterface
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
     * @var bool
     * */
    protected $create;


    /**
     * Constructor
     *
     * @throws ConfigurationException
     * @param string $rootElement
     * @param $rootId
     * @param string $relation
     * @param string $relationClass
     * @param bool $create
     * */
    public function __construct(string $rootElement, $rootId, string $relation, string $relationClass, bool $create)
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
        $this->create        = $create;

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
            $return = $relationMethod->invoke($this->rootClass);

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
            if (is_null($this->create)) {
                $this->create = $request->input('create', true);
            }

            if ($this->create) {
                $createRequest = call_user_func([$this->getModelClass(), 'instanceCreateRequest']);

                if (!$createRequest->authorize()) {
                    return $this->forbidden();
                }

                $validator = Validator::make($request->all(), $createRequest->rules());
            } else {
                $validator = Validator::make($request->all(), [
                    'id' => [
                        'required',
                        'exists:' . (new $this->relationClass())->getTable() . ',id'
                    ]
                ]);
            }

            try {
                $input = $validator->validate();
            } catch (\Exception $e) {
                return $this->invalidInput($validator->errors());
            }

            if ($this->create) {
                $target = new $this->relationClass($input);
            } else {
                $target = call_user_func([$this->relationClass, 'find'], $input['id']);
            }

            $root = call_user_func([$this->rootClass, 'find'], $this->rootId);
            $root->{$this->relation}()->save($target);

            return ok([
                $this->relation => $target
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
        $fetchAllRequest = call_user_func([$this->getModelClass(), 'instanceFetchAllRequest']);

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
        return $this->notAllowed();
    }

    /**
     * @return string
     */
    public function getModelClass(): string
    {
        return $this->rootClass;
    }

}
