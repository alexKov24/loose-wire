<?php
namespace LooseWire;

use ReflectionClass;
use ReflectionProperty;

interface Wired
{
    public function render(): string;
    public function getPublicProperties(): array;

    public function setPublicProperties(array $data): void;

}

abstract class Wire implements Wired
{

    public function getPublicProperties(): array
    {
        $reflection = new ReflectionClass($this);
        $props = [];

        foreach ($reflection->getProperties(ReflectionProperty::IS_PUBLIC) as $prop) {
            $props[$prop->name] = $prop->getValue($this);
        }

        return $props;
    }

    public function getEncodedPublicProperties(): string
    {
        //return htmlentities(json_encode($this->getPublicProperties()));
        return base64_encode(json_encode($this->getPublicProperties()));
    }

    public function setPublicProperties(array $data): void
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    abstract public function render(): string;
}