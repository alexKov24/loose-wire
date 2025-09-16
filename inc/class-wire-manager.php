<?php

namespace LooseWire;

use Exception;
use ReflectionProperty;
use ReflectionClass;


class WireManager
{


    /**
     * Same as prints the get function
     * @param string $fileClassName
     * @param string $method
     * @param array $publicVars
     * @return void
     */
    public static function print(string $fileClassName, string $method = '', array $publicVars = [])
    {
        echo self::get($fileClassName, $method, $publicVars);
    }

    /**
     * Runs on subsequent request
     * @param string $className
     * @param string $method
     * @param array $publicVars
     * @return string
     */
    public static function get(string $fileClassName, string $method = '', array $publicVars = []): string
    {
        // inits and runs methods
        $wire = self::runWire($fileClassName, $method, $publicVars);

        // parses html and adds events
        return self::render($wire);
    }

    private static function runWire(string $fileClassName, string $method = '', array $publicVars = [])
    {

        $wire = self::getClassInstance($fileClassName);

        if (count($publicVars)) {
            $wire->setPublicProperties($publicVars);
        }

        if (!$method)
            return $wire;


        preg_match('/([^(]+)\((.+)\)$/', $method, $matches);
        if ($matches && method_exists($wire, $matches[1])) {
            $parameters = explode(',', $matches[2]);
            $parameters = array_map('trim', $parameters); // Remove whitespace

            $wire->{$matches[1]}(...$parameters); // Spread operator

            return $wire;
        }

        if (method_exists($wire, $method)) {
            $wire->$method();

            return $wire;
        }
    }

    private function extractPublicProperties($wire)
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


    private static function render($wire): string
    {

        $data = html_entity_decode(json_encode([
            'common' => [
                'className' => get_class($wire)
            ],
            'props' => $wire->getPublicProperties()
        ]));

        return <<<HTML
            <div wire:data='{$data}'>{$wire->render()}</div>
        HTML;

    }

}
