<?php

namespace ViaRest\Http\Controllers\Api;

use ViaRest\Models\DynamicModelInterface;

class DynamicRestController extends AbstractRestController implements RestControllerInterface
{

    /**
     * @var string
     * */
    protected $modelClass;


    /**
     * Constructor
     *
     * @param string $modelClass
     */
    public function __construct(string $modelClass)
    {
        $this->modelClass = $modelClass;
    }

    /**
     * @return string
     */
    public function getModelClass(): string
    {
        return $this->model;
    }

}
