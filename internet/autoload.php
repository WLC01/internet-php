<?php

spl_autoload_register(function($class){
    include_once dirname(dirname(__FILE__)) . '/' . (str_replace('\\', '/', $class)) . '.php';
}, true, false);