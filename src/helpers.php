<?php

if (!function_exists('get_instance')) {
    /**
     * Get the available class instance.
     *
     * @param string className
     *
     * @return mixed|class
     */
    function get_instance(string $className)
    {
        return $className::getInstance();
    }
}

if (!function_exists('get_classes')) {
    function get_classes(array $paths, string $excludeClass = '')
    {
        echo $excludeClass;
    }
}
