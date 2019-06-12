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


class DynamicRestRelationController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    use RestResponseControllerTrait;

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

        $input = array_merge($request->all(), [$this->identifier => $this->joinId]);

        $validator = Validator::make($input, $createRequest->rules());

        try {
            $input = $validator->validate();
        } catch (\Exception $e) {
            return $this->invalidInput($validator->errors());
        }

        try {

            return ok([
                'data' => call_user_func([$this->getModel(), 'create'], $input)->refresh()
            ]);

        } catch (\Exception $e) {
            return error_json_response($e->getMessage(), $e->getTrace(), 500);
        }
    }

    /**
     * Fetch a model
     *
     * @param $request Request
     * @return JsonResponse
     */
    public function fetch(Request $request): JsonResponse
    {
        return method_not_allowed('Method Not Allowed');
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

            return ok([
                'data' => call_user_func_array([$this->getModel(), 'where'], [$this->identifier, '=', $this->joinId])->get()
            ]);

        } catch (\Exception $e) {
            return error_json_response('Something went wrong. Relation not fully configured.', [], 500);
        }
    }

    /**
     * Update a model
     *
     * @param $request Request
     * @return JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        return method_not_allowed('Method Not Allowed');
    }

    /**
     * Destroy a model
     *
     * @param $request Request
     * @return JsonResponse
     */
    public function destroy(Request $request): JsonResponse
    {
        return method_not_allowed('Method Not Allowed');
    }

    /**
     * @return DynamicModelInterface
     */
    public function getModel(): DynamicModelInterface
    {
        return $this->model;
    }

}
