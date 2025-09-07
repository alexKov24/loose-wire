<?php

namespace LooseWire;

use Exception;
use ReflectionProperty;
use ReflectionClass;

class WireManager
{

    /**
     * Runs on subsequent request
     * @param string $className
     * @param string $method
     * @param array $publicVars
     * @return string
     */
    public static function getTheWire(string $fileClassName, string $method = '', array  $publicVars = []): string
    {
        // inits and runs methods
        $wire = self::setupWire($fileClassName, $method, $publicVars);

        // parses html and adds events
        return self::parseWire($wire);
    }

    public static function setupWire(string $fileClassName, string $method = '', array  $publicVars = []){
        $wire = self::getClassInstance($fileClassName);

        if(count($publicVars)) {
            $wire->setPublicProperties($publicVars);
        }

        if(!$method) return $wire;


        preg_match('/([^(]+)\((.+)\)$/', $method, $matches);
        if($matches && method_exists($wire, $matches[1])) {
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


    static function parseWire($wire):string{

        $wire_html = HtmlProps::replaceProp($wire->render(), 'wire:click', function($prop, $value) use($wire) {
            return [
                'name' => 'onclick',
                'value' => $wire->pullOn($value)
            ];
        });

        //$data = $wire->getEncodedPublicProperties();

        $data = html_entity_decode(json_encode($wire->getPublicProperties()));

        return <<<HTML
            <div wire:data='{$data}'>{$wire_html}</div>
        HTML;

    }

}
class HtmlProps 
{
    /**
     * Extracts all properties from an HTML element
     * @param string $html HTML string containing element with properties
     * @return array Associative array of property => value pairs
     */
    public static function getProps(string $html): array {
        $pattern = '/([a-zA-Z][\w:-]*)\s*=\s*(["\'])([^"\']*)\2/';
        preg_match_all($pattern, $html, $matches, PREG_SET_ORDER);
        
        $props = [];
        foreach ($matches as $match) {
            $props[$match[1]] = $match[3];
        }
        
        return $props;
    }

    /**
     * Replaces properties using a callback function
     * @param string $html HTML string to modify
     * @param string $prop Property name to target (or '*' for all properties)
     * @param callable $callback Function that receives (propName, currentValue) and returns ['name' => newName, 'value' => newValue] or null to remove
     * @return string Modified HTML string
     */
    public static function replaceProp(string $html, string $prop, callable $callback): string {
        if ($prop === '*') {
            return self::replaceAllProps($html, $callback);
        }
        
        return self::replaceSingleProp($html, $prop, $callback);
    }
    
    private static function replaceSingleProp(string $html, string $prop, callable $callback): string {
        $escapedProp = preg_quote($prop, '/');
        // Remove \b word boundary and use more specific pattern
        $pattern = "/(?<=\\s|^)({$escapedProp})\\s*=\\s*([\"'])([^\"']*?)\\2/";
        
        return preg_replace_callback($pattern, function($matches) use ($callback) {
            $propName = $matches[1];
            $currentValue = $matches[3];
            $result = $callback($propName, $currentValue);
            
            return self::formatResult($result);
        }, $html);
    }
    
    private static function replaceAllProps(string $html, callable $callback): string {
        $pattern = '/([a-zA-Z][\w:-]*)\s*=\s*(["\'])([^"\']*)\2/';
        
        return preg_replace_callback($pattern, function($matches) use ($callback) {
            $propName = $matches[1];
            $currentValue = $matches[3];
            $result = $callback($propName, $currentValue);
            
            return self::formatResult($result);
        }, $html);
    }

    /**
     * Formats callback result into HTML attribute
     */
    private static function formatResult($result): string {
        if ($result === null) {
            return '';
        }
        
        if (is_string($result)) {
            return $result;
        }
        
        if (is_array($result) && isset($result['name'], $result['value'])) {
            $name = $result['name'];
            $value = htmlspecialchars($result['value'], ENT_QUOTES);
            return "{$name}=\"{$value}\"";
        }
        
        throw new InvalidArgumentException("Callback must return null, string, or ['name' => string, 'value' => string]");
    }
}