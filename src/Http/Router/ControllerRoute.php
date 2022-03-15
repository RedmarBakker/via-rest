<?php
/**
 * Created by PhpStorm.
 * User: redmarbakker
 * Date: 2019-06-05
 * Time: 21:17
 */

namespace ViaRest\Http\Router;


class ControllerRoute extends AbstractRoute
{

    /**
     * @var array
     * */
    private $endpoints = [];


    /**
     * @return array
     */
    public function getEndpoints(): array
    {
        return $this->endpoints;
    }

    /**
     * @param array $endpoints
     * @return self
     */
    public function setEndpoints(array $endpoints): self
    {
        $this->endpoints = $endpoints;
        return $this;
    }

}
