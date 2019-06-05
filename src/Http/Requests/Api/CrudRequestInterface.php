<?php

namespace ViaRest\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

interface CrudRequestInterface
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize();

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules();

}
