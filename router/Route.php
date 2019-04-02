<?php

namespace ClementPatigny\Router;

class Route {

    private $_path;
    private $_callable;
    private $_matches = [];

    public function __construct($path, $callable) {
        $this->_path = trim($path, '/');
        $this->_callable = $callable;
    }

    public function match($url) {
        $url = trim($url, '/');
        $path = preg_replace('#:([\w]+)#', '([^/]+)', $this->_path);
        $regex = "#^$path$#i";

        if (preg_match($regex, $url, $matches)) {
            array_shift($matches);
            $this->_matches = $matches;
            return true;
        } else {
            return false;
        }
    }

    public function call() {
        return call_user_func_array($this->_callable, $this->_matches);
    }
}
