<?php
/**
 * Created by PhpStorm.
 * User: redmarbakker
 * Date: 2019-06-12
 * Time: 23:02
 */

namespace ViaRest\Providers;


class CacheProvider
{

    /**
     * @var string
     * */
    private $namespace;


    /**
     * Constructor
     *
     * @param $namespace string
     * */
    public function __construct(string $namespace)
    {
        $this->namespace = $namespace;
    }

    /**
     * Set Cache
     *
     * @param $key string
     * @param $value mixed
     * */
    public function set(string $key, $value)
    {
        $value = var_export($value, true);

        $value = str_replace('stdClass::__set_state', '(object)', $value);

        $tmp = "/tmp/$key." . uniqid('', true) . '.tmp';

        file_put_contents($tmp, '<?php $value = ' . $value . ';', LOCK_EX);

        rename($tmp, "/tmp/$this->namespace.$key");
    }

    /**
     * Get
     *
     * @param $key string
     * @return mixed|bool
     * */
    public function get(string $key)
    {
        @include "/tmp/$this->namespace.$key";
        return isset($value) ? $value : false;
    }

}