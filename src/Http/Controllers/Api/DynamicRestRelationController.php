<?php

namespace ViaRest\Http\Controllers\Api;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;
use ViaRest\Models\DynamicModelInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;

class DynamicRestRelationController extends AbstractRestController implements RestControllerInterface
{

    /**
     * @var DynamicModelInterface
     * */
    protected $model;

    /**
     * @var int
     * */
    protected $identifier;

    /**
     * @var int
     * */
    protected $joinId;


    /**
     * Constructor
     *
     * @param $model DynamicModelInterface
     * @param $identifier string
     * @param $joinId int
     * */
    public function __construct(DynamicModelInterface $model, string $identifier, int $joinId)
    {
        $this->model        = $model;
        $this->identifier   = $identifier;
        $this->joinId       = $joinId;
    }

    /**
     * Create new model
     *
     * @param $request Request
     * @return JsonResponse
     */
    public function create(Request $request): JsonResponse
    {
        $createRequest = $this->getModel()->instanceCreateRequest();

        if (!$createRequest->authorize()) {
            return $this->forbidden();
        }

        $validator = Validator::make($request->all(), $createRequest->rules());

        try {
            $input = $validator->validate();
            $input = array_merge($input, [$this->identifier => $this->joinId]);
        } catch (\Exception $e) {
            return $this->invalidInput($validator->errors());
        }

        try {

            self::doCreate($input);

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
        $fetchAllRequest = $this->getModel()->instanceFetchAllRequest();

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

            $result = $this->getModel();
            $result->where($this->identifier, '=', $this->joinId);
            $result->load($input['relations'] ?? []);

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
     * @return DynamicModelInterface
     */
    public function getModel(): DynamicModelInterface
    {
        return $this->model;
    }

}
