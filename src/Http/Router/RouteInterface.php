<?php
/**
 * Created by PhpStorm.
 * User: redmarbakker
 * Date: 2019-06-05
 * Time: 21:17
 */

namespace ViaRest\Http\Router;

interface RouteInterface
{

    /**
     * @return string
     */
    public function getTarget(): string;

    /**
     * @param string $target
     */
    public function setTarget(string $target): self;

    /**
     * @return array
     */
    public function getRelations(): array;

    /**
     * @param array $relations
     */
    public function setRelations(array $relations): self;

    /**
     * @return string
     */
    public function getPermission(): string;

    /**
     * @param string $permission
     */
    public function setPermission(string $permission): self;

}
