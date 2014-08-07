<?php

namespace Builder;

/**
 * @method static App i()
 * @method \MongoDb db()
 * @method \Klein\Router router()
 * @method \stdClass config()
 * @method \Twig_Environment view()
 * @method Model\BuildTable build_table()
 */
class App extends \Klein\App
{
    private $factories = [];

    public function isGuest()
    {
        return is_null($this->mserver()->getHTTPClient()->getLogin());
    }

    public function __call($key, $args)
    {
        if (isset($this->factories[$key])) {
            return $this->factories[$key]($args);
        }

        return parent::__call($key, $args);
    }

    public function factory($key, $callable)
    {
        $this->factories[$key] = $callable;
    }
}
