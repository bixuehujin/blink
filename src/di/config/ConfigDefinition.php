<?php

namespace blink\di\config;

/**
 * Class ConfigDefinition
 *
 * @package blink\di\config
 */
class ConfigDefinition
{
    /**
     * The name of the config.
     *
     * @var string
     */
    protected string $name;
    /**
     * Is this config required.
     *
     * @var bool
     */
    protected bool   $required = false;
    /**
     * The default value if the config is not required.
     *
     * @var mixed
     */
    protected mixed  $default;

    /**
     * ConfigDefinition constructor.
     *
     * @param string $name
     * @param mixed $default
     */
    public function __construct(string $name, mixed $default = null)
    {
        $this->name    = $name;
        $this->default = $default;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function default(mixed $value)
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
