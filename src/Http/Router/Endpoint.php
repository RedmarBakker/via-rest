<?php
/**
 * Created by PhpStorm.
 * User: redmarbakker
 * Date: 2019-06-05
 * Time: 21:17
 */

namespace ViaRest\Http\Router;

use Illuminate\Http\Request;
use ViaRest\Http\Exceptions\Api\ConfigurationException;

class Endpoint
{

    /**
     * @var string $target
     * */
    public $target;

    /**
     * Method of the route
     *
     * @var string
     * */
    public $method = Request::METHOD_GET;

    /**
     * Id integrationn
     *
     * @var bool
     * */
    public $idIntegration = false;

    /**
     * Route where
     *
     * @var array
     * */
    public $where = [];


    /**
     * Constructor
     *
     * @param string $target
     * */
    public function __construct(string $target)
    {
        $this->target = $target;
    }

    /**
     * Method of the route
     *
     * @param string $method
     * @return self
     * */
    public function setMethod(string $method): self
    {
        $allowedMethods = [
            Request::METHOD_GET,
            Request::METHOD_POST,
            Request::METHOD_PUT,
            Request::METHOD_DELETE,
        ];

        if (! in_array($method, $allowedMethods)) {
            throw new ConfigurationException(sprintf(
                'See the docs: https://github.com/RedmarBakker/via-rest#configuring-your-routes',
            ));
        }

        $this->method = $method;
        return $this;
    }

    /**
     * Is get endpoint
     *
     * @return self
     * @throws ConfigurationException
     * */
    public function isGet():self
    {
        $this->setMethod(Request::METHOD_GET);
        return $this;
    }

    /**
     * Is post endpoint
     *
     * @return self
     * @throws ConfigurationException
     * */
    public function isPost():self
    {
        $this->setMethod(Request::METHOD_POST);
        return $this;
    }

    /**
     * Is put endpoint
     *
     * @return self
     * @throws ConfigurationException
     * */
    public function isPut():self
    {
        $this->setMethod(Request::METHOD_PUT);
        return $this;
    }

    /**
     * Is delete endpoint
     *
     * @return self
     * @throws ConfigurationException
     * */
    public function isDelete():self
    {
        $this->setMethod(Request::METHOD_DELETE);
        return $this;
    }

    /**
     * Route with id integration
     *
     * @param bool $integration
     * @return self
     * */
    public function withIdIntegration($integration = true): self
    {
        $this->idIntegration = $integration;
        return $this;
    }

    /**
     * Route where
     *
     * @param mixed $where
     * @param mixed $value
     * @return self
     * */
    public function where($where, $value = false): self
    {
        if (is_array($where)) {
            $this->where = $where;
        } elseif($value != false) {
            $this->where[$where] = $value;
        } else {
            throw new \Exception('EndpointRoute::where() expects first parameter to be array or first and second parameter to be a key value pair.');
        }

        return $this;
    }

    /**
     * Instantiate get endpoint
     *
     * @return self
     * */
    public static function get($target): self
    {
        return (new self($target))->isGet();
    }

    /**
     * Instantiate post endpoint
     *
     * @return self
     * */
    public static function post($target): self
    {
        return (new self($target))->isPost();
    }

    /**
     * Instantiate put endpoint
     *
     * @return self
     * */
    public static function put($target): self
    {
        return (new self($target))->isPut();
    }

    /**
     * Instantiate delete endpoint
     *
     * @return self
     * */
    public static function delete($target): self
    {
        return (new self($target))->isDelete();
    }

}
