<?php

namespace Iono\Proto\Container;

/**
 * Class Container
 * @package Iono\Proto\Container
 * @author  yuuki.takezawa<yuuki.takezawa@comnect.jp.net>
 */
class Container implements ContainerInterface, ContextualInterface
{

    /** @var array */
    protected $bindings = [];

    /** @var array */
    protected $parameters = [];

    /** @var array */
    protected $shares = [];

    /** @var array */
    protected $component = [];

    /**
     * get instance from container
     * @param       $abstract
     * @param array $parameters
     * @return object
     */
    public function newInstance($abstract, array $parameters = [])
    {
        return (new Resolver($this))->makeInstance($abstract, $parameters);
    }

    /**
     * @param          $abstract
     * @param          $concrete
     * @param bool|int $scope
     * @return Component
     */
    public function register($abstract, $concrete, $scope = Scope::PROTOTYPE)
    {
        $this->bindings[$abstract] = $concrete;
        if ($scope) {
            $this->shares[$abstract] = $scope;
        }

        return new Component($this, $abstract);
    }

    /**
     * @param $abstract
     * @param $concrete
     * @return Component
     */
    public function singleton($abstract, $concrete)
    {
        return $this->register($abstract, $concrete, Scope::SINGLETON);
    }

    /**
     * @param       $abstract
     * @param array $parameters
     * @return void
     */
    public function setParameters($abstract, array $parameters = [])
    {
        $this->parameters[$abstract] = $parameters;
    }

    /**
     * @param $abstract
     * @return null
     */
    public function getParameters($abstract = null)
    {
        if (is_null($abstract)) {
            return $this->parameters;
        }

        return (isset($this->parameters[$abstract])) ? $this->parameters[$abstract] : null;
    }

    /**
     * @param $abstract
     * @return null
     */
    public function getBinding($abstract = null)
    {
        if (is_null($abstract)) {
            return $this->bindings;
        }

        return (isset($this->bindings[$abstract])) ? $this->bindings[$abstract] : null;
    }

    /**
     * @param $abstract
     * @return null
     */
    public function getShare($abstract)
    {
        return (isset($this->shares[$abstract])) ? $this->shares[$abstract] : null;
    }

    /**
     * @param null $abstract
     */
    public function flushInstance($abstract = null)
    {
        if (is_null($abstract)) {
            $this->bindings = [];
            $this->parameters = [];
            $this->shares = [];
            $this->component = [];

            return;
        }
        unset($this->bindings[$abstract]);
        unset($this->parameters[$abstract]);
        unset($this->shares[$abstract]);

        foreach ($this->component as $key => $bind) {
            unset($this->component[$key]);
        }
    }

    /**
     * @param $name
     * @param $abstract
     */
    public function addComponent($name, $abstract)
    {
        $this->component[$name][$abstract] = $this->bindings[$abstract];
    }

    /**
     * use id, get instance from container
     * @param $name
     * @return null|object
     */
    public function qualifier($name)
    {
        if (isset($this->component[$name])) {
            foreach ($this->component[$name] as $key => $bind) {
                $tmp = $this->bindings[$key];
                $this->bindings[$key] = $bind;
                $instance = $this->newInstance($key);
                $this->bindings[$key] = $tmp;
                unset($tmp);
                return $instance;
            }
        }

        return null;
    }

}
