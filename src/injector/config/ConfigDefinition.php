<?php

namespace blink\injector\config;

use blink\injector\Definition;

/**
 * Class ConfigDefinition
 *
 * @package blink\injector\config
 */
class ConfigDefinition implements Definition
{
    protected string $name;
    protected bool   $required = false;
    protected        $default;

    /**
     * ConfigDefinition constructor.
     *
     * @param string $name
     * @param mixed $default
     */
    public function __construct(string $name, $default = null)
    {
        $this->name    = $name;
        $this->default = $default;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function default($value)
    {
        $this->default = $value;
        return $this;
    }

    public function required()
    {
        $this->required = true;
        return $this;
    }

    public function isRequired()
    {
        return $this->required;
    }

    public function defaultValue()
    {
        return $this->default;
    }
}
