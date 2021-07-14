<?php

namespace ViaRest\Http\Controllers\Api;

class DynamicRestController extends AbstractRestController implements RestControllerInterface
{

    /**
     * @var string
     * */
    protected static $modelClass;


    /**
     * Constructor
     *
     * @param string $modelClass
     */
    public function __construct(string $modelClass)
    {
        self::$modelClass = $modelClass;
    }

    /**
     * @return string
     */
    public static function getModelClass(): string
    {
        return self::$model;
    }

}
