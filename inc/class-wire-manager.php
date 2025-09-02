<?php

namespace LooseWire\Classes;

use Exception;
use ReflectionProperty;
use ReflectionClass;

class WireManager
{

    public function pullTheWire($className, $method, $publicVars)
    {

        $wire = self::getClassInstance($className);

        $wire->setPublicProperties($publicVars);

        if ($method && method_exists($wire, $method)) {
            $wire->$method();
        }

        // rerenders the wire inside the wrapper
        return "<div wire-render>" . $wire->render() . "</div>";
    }

    public function extractPublicProperties($wire)
    {
        $reflection = new ReflectionClass($wire);
        $publicProps = [];

        foreach ($reflection->getProperties(ReflectionProperty::IS_PUBLIC) as $prop) {
            $publicProps[$prop->name] = $prop->getValue($wire);
        }

        return $publicProps;
    }

    /**
     * Returns class instance from your template wires
     * get_template_directory() . '/wires/' . $fileClassName . '.php';
     * @param string $fileClassName - name of your class and its containing file
     * @throws \Exception - if no file exists
     * @return Wired
     * 
     */
    private static function getClassInstance(string $fileClassName): Wired
    {
        $wireFile = get_template_directory() . '/wires/' . $fileClassName . '.php';

        if (!file_exists($wireFile)) {
            throw new Exception("Wire file not found: $wireFile");
        }

        require_once $wireFile;

        if (!class_exists($fileClassName)) {
            throw new Exception("Wire class not found: $fileClassName");
        }
        return new $fileClassName();

    }

    /**
     * Setsup and returns wire render by class name
     * The class name should be the same as the file name 
     * @param string $fileClassName - used to create className and fileName
     * @throws \Exception
     * 
     * @example basic usage
     *  // load class from /theme/wires/Clicker.php
     *  use LooseWire\Classes\WireManager;
     *  echo WireManager::setupWire('Clicker');
     * 
     */
    public static function setupWire(string $fileClassName): string
    {

        $wire = self::getClassInstance($fileClassName);

        return "<div wire-render>" . $wire->render() . "</div>";
    }

    /**
     * Setsup and prints wire render by class name
     *
     * The class name should be the same as the file name 
     * @param string $fileClassName - used to create className and fileName
     * @throws \Exception
     * 
     * @example basic usage
     *  // load class from /theme/wires/Clicker.php
     *  use LooseWire\Classes\WireManager;
     *  echo WireManager::setupWire('Clicker');
     * 
     */
    public static function setupTheWire($fileClassName)
    {
        echo self::setupWire($fileClassName);
    }
}