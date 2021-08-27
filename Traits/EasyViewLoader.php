<?php
namespace App\Larabookir\Traits;

use Aws\Common\Exception\BadMethodCallException;
use Illuminate\Contracts\View\Factory as FactoryContract;
use Illuminate\Support\MessageBag;

trait EasyViewLoader
{

    public function __get($prop)
    {
        if ($prop == 'view' && empty($this->view))
            $this->view = new  Factory(view());
        return $this->{$prop};
    }


    public function __call($method, $parameters)
    {
        if (strtolower($method) == 'view') {
            return call_user_func_array(array($this->view, 'render'), $parameters);
        }
        parent::__call($method, $parameters);
    }
}



class Factory implements FactoryContract
{
    public $factory;
    public $_data = [];


    function __construct($factory)
    {
        $this->factory = $factory;
    }

    /**
     * Add a piece of data to the view.
     *
     * @param  string|array $key
     * @param  mixed $value
     * @return $this
     */
    public function with($key, $value = null)
    {
        if (is_array($key)) {
            $this->_data = array_merge($this->_data, $key);
        } else {
            $this->_data[$key] = $value;
        }

        return $this;
    }

    /**
     * Add validation errors to the view.
     *
     * @param  \Illuminate\Contracts\Support\MessageProvider|array $provider
     * @return $this
     */
    public function withErrors($provider)
    {
        if ($provider instanceof MessageProvider) {
            $this->with('errors', $provider->getMessageBag());
        } else {
            $this->with('errors', new MessageBag((array)$provider));
        }

        return $this;
    }


    /**
     * Dynamically bind parameters to the view.
     *
     * @param  string $method
     * @param  array $parameters
     * @return \Illuminate\View\View
     *
     * @throws \BadMethodCallException
     */
    public function __call($method, $parameters)
    {
        // calling methods started by "with"
        if (starts_with($method, 'with'))
            return $this->with(snake_case(substr($method, 4)), $parameters[0]);


        // calling methods exists in factory like composer() , exist() , ....
        if (method_exists($this->factory,$method))
            return call_user_func_array(array($this->factory, $method), $parameters);

        throw new BadMethodCallException("Method [$method] does not exist on view.");
    }

    /**
     * Get the evaluated view contents for the given view.
     *
     * @param  string $view
     * @param  array $mergeData
     * @return \Illuminate\View\View
     */
    public function render($view = null, $data = array(), $mergeData = array())
    {
        if (func_num_args() === 0) {
            return $this->factory;
        }

        return $this->make($view, $data, $mergeData);
    }

    /**
     * Get the array of view data.
     *
     * @return array
     */
    public function getData()
    {
        return $this->_data;
    }


    /**
     * Get a piece of data from the view.
     *
     * @param  string $key
     * @return mixed
     */
    public function &__get($key)
    {
        return $this->_data[$key];
    }

    /**
     * Set a piece of data on the view.
     *
     * @param  string $key
     * @param  mixed $value
     * @return void
     */
    public function __set($key, $value)
    {
        $this->with($key, $value);
    }

    /**
     * Check if a piece of data is bound to the view.
     *
     * @param  string $key
     * @return bool
     */
    public function __isset($key)
    {
        return isset($this->_data[$key]);
    }

    /**
     * Remove a piece of bound data from the view.
     *
     * @param  string $key
     * @return bool
     */
    public function __unset($key)
    {
        unset($this->_data[$key]);
    }

    /**
     * Determine if a given view exists.
     *
     * @param  string  $view
     * @return bool
     */
    public function exists($view){
        return $this->factory->exists($view);
    }

    /**
     * Get the evaluated view contents for the given path.
     *
     * @param  string  $path
     * @param  array  $data
     * @param  array  $mergeData
     * @return \Illuminate\Contracts\View\View
     */
    public function file($path, $data = array(), $mergeData = array()){
        return $this->factory->file($path, $this->_data, array_merge($data, $mergeData));
    }
    /**
     * Get the evaluated view contents for the given view.
     *
     * @param  string  $view
     * @param  array  $data
     * @param  array  $mergeData
     * @return \Illuminate\Contracts\View\View
     */
    public function make($view, $data = array(), $mergeData = array()){
        return $this->factory->make($view, $this->_data, array_merge($data, $mergeData));
    }

    /**
     * Add a piece of shared data to the environment.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return void
     */
    public function share($key, $value = null){
        return $this->factory->share($key, $value);
    }

    /**
     * Register a view composer event.
     *
     * @param  array|string  $views
     * @param  \Closure|string  $callback
     * @param  int|null  $priority
     * @return array
     */
    public function composer($views, $callback, $priority = null){
        return $this->factory->composer($views, $callback, $priority);
    }

    /**
     * Register a view creator event.
     *
     * @param  array|string  $views
     * @param  \Closure|string  $callback
     * @return array
     */
    public function creator($views, $callback){
        return $this->factory->creator($views, $callback);
    }

    /**
     * Add a new namespace to the loader.
     *
     * @param  string  $namespace
     * @param  string|array  $hints
     * @return void
     */
    public function addNamespace($namespace, $hints){
        return $this->factory->addNamespace($namespace, $hints);
    }

}