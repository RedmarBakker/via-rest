<?php
/**
 * Created by PhpStorm.
 * User: redmarbakker
 * Date: 2019-06-05
 * Time: 21:17
 */

namespace ViaRest\Http\Router;


class MeRoute
{

    const ALLOWED_ACTIONS = [
        AbstractRoute::READ_ONE_ACTION,
        AbstractRoute::UPDATE_ACTION,
    ];

    /**
     * Linked route
     *
     * @var RouteInterface
     * */
    protected $route;

    /**
     * Constructor
     *
     * @param $route RouteInterface
     * */
    public function __construct(RouteInterface $route)
    {
        $this->route = $route;
    }

    /**
     * Get the linked route
     *
     * @return RouteInterface
     * */
    public function getLinkedRoute(): RouteInterface
    {
        return $this->route;
    }

}
