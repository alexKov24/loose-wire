<?php

namespace LooseWire\Classes;

use Exception;

class WireManager
{

    public function pullTheWire($className, $method, $publicVars)
    {
        $wireFile = get_template_directory() . '/wires/' . strtolower($className) . '.php';

        if (!file_exists($wireFile)) {
            throw new Exception("Wire class not found: $className");
        }

        require_once $wireFile;
        $wire = new $className();

        // Set properties using your existing method
        $wire->setPublicProperties($publicVars);

        // Execute method if provided
        if ($method && method_exists($wire, $method)) {
            $wire->$method();
        }

        return $wire->render();
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
}