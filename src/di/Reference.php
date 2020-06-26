<?php

namespace blink\di;

/**
 * Reference represents a reference in property or argument.
 *
 * @package blink\di
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

    protected bool $required = true;
    /**
     * The default value if referentName is not available.
     */
    protected mixed $default;

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

    public function withDefault(mixed $default)
    {
        $this->required = false;
        $this->default = $default;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function getDefault()
    {
        return $this->default;
    }
}
