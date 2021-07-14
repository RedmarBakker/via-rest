<?php
/**
 * Created by PhpStorm.
 * User: redmarbakker
 * Date: 2019-06-05
 * Time: 21:17
 */

namespace ViaRest\Http\Router;


class MeRoute implements RouteInterface
{

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
     * @return string
     */
    public function getTarget(): string
    {
        return $this->route->getTarget();
    }

    /**
     * @param string $target
     */
    public function setTarget(string $target): void
    {
        $this->route->setTarget($target);
    }

    /**
     * @return array
     */
    public function getRelations(): array
    {
        return $this->route->getRelations();
    }

    /**
     * @param array $relations
     */
    public function setRelations(array $relations): void
    {
        $this->route->setRelations($relations);
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