<?php
/**
 * Created by PhpStorm.
 * User: redmarbakker
 * Date: 2019-06-05
 * Time: 15:59
 */

namespace ViaRest\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;

trait RestResponseControllerTrait
{

    /**
     * Forbidden
     *
     * @return JsonResponse
     * */
    public function forbidden(): JsonResponse
    {
        return forbidden('Endpoint forbidden');
    }

    /**
     * Invalid input
     *
     * @param $errors
     * @return JsonResponse
     * */
    public function invalidInput($errors): JsonResponse
    {
        return forbidden('Invalid input', $errors);
    }

    /**
     * Not Found
     *
     * @return JsonResponse
     * */
    public function notFound()
    {
        return not_found('Item not found');
    }

}