<?php
namespace LooseWire\Classes;

use ReflectionClass;
use ReflectionProperty;

interface Wired
{
    public function render(): string;
}

abstract class Wire implements Wired
{

    protected function pullOn($method)
    {
        $className = get_class($this);
        $props = base64_encode(json_encode($this->getPublicProperties()));

        return "loose.pullOn(this, \"$className\", \"$method\", \"$props\")";
    }

    public function getPublicProperties()
    {
        $reflection = new ReflectionClass($this);
        $props = [];

        foreach ($reflection->getProperties(ReflectionProperty::IS_PUBLIC) as $prop) {
            $props[$prop->name] = $prop->getValue($this);
        }

        return $props;
    }

    public function setPublicProperties($data)
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    abstract public function render(): string;
}