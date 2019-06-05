<?php

namespace ViaRest\Http\Controllers\Api;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;
use ViaRest\Models\DynamicModelInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

abstract class AbstractRestController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    use RestResponseControllerTrait;

    /**
     * Fetch All
     *
     * @param $request FormRequest
     * @return Model|string
     * */
    public function fetchAll(Request $request)
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
        $fetchRequest = $this->getModel()->instanceFetchRequest();

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
        $createRequest = $this->getModel()->instanceCreateRequest();

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
        $updateRequest = $this->getModel()->instanceUpdateRequest();

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
        $destroyRequest = $this->getModel()->instanceDestroyRequest();

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
            'data' => call_user_func([$this->getModel(), 'create'], $input)
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
        $item = call_user_func([$this->getModel(), 'find'], $id);

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
        return ok([
            'data' => call_user_func([$this->getModel(), 'all'])
        ]);
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
        $item = call_user_func([$this->getModel(), 'find'], $id);

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
        $item = call_user_func([$this->getModel(), 'find'], $id);

        if ($item == null) {
            return $this->notFound();
        }

        $item->delete();

        return ok([
            'data' => []
        ]);
    }

    /**
     * @return DynamicModelInterface
     */
    abstract function getModel(): DynamicModelInterface;

}
