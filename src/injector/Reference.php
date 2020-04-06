<?php

namespace blink\injector;

/**
 * Reference represents a reference in property or argument.
 *
 * @package blink\injector
 */
class Reference
{
    /**
     * The name of the reference, property name or argument name.
     */
    protected string  $name;

    /**
     * The name of the referent.
     */
    protected ?string $referentName = null;

    protected bool $guarded = false;

    /**
     * The default value if referentName is not available.
     *
     * @var mixed
     */
    protected $default;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function referenceTo(string $referentName)
    {
        $this->referentName = $referentName;
        return $this;
    }

    public function guarded(bool $guarded= true)
    {
        $this->guarded = $guarded;
    }

    public function isGuarded()
    {
        return $this->guarded;
    }

    public function getReferentName(): ?string
    {
        return $this->referentName;
    }

    public function withValue($value)
    {
        $this->default = $value;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getValue()
    {
        return $this->default;
    }
}
