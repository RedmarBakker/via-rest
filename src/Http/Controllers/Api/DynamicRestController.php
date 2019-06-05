<?php

namespace ViaRest\Http\Controllers\Api;

use ViaRest\Models\DynamicModelInterface;

class DynamicRestController extends AbstractRestController
{

    /**
     * @var DynamicModelInterface
     * */
    protected $model;


    /**
     * Constructor
     *
     * @param $model DynamicModelInterface
     * */
    public function __construct(DynamicModelInterface $model)
    {
        $this->model = $model;
    }

    /**
     * @return DynamicModelInterface
     */
    public function getModel(): DynamicModelInterface
    {
        return $this->model;
    }

}
