<?php

namespace blink\database\schema;

class RelationDefinition implements RelationContract
{
    public string $name;
    public string $type;
    public string $target;
    public string $foreignKey;
    public string $localKey;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public static function make(string $name): self
    {
        return new self($name);
    }

    public function hasOne(string $target, string $foreignKey = null, string $localKey = null): self
    {
        $this->type = 'hasOne';
        $this->target = $target;
        $this->foreignKey = $foreignKey;
        $this->localKey = $localKey;
        return $this;
    }

    public function hasMany(string $target, string $foreignKey = null, string $localKey = null): self
    {
        $this->type = 'hasMany';
        $this->target = $target;
        $this->foreignKey = $foreignKey;
        $this->localKey = $localKey;
        return $this;
    }

    public function hasManyInplace(string $target, string $foreignKey = null, string $localKey = null): self
    {
        $this->type = 'hasManyInplace';
        $this->target = $target;
        $this->foreignKey = $foreignKey;
        $this->localKey = $localKey;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getLocalKey(): string
    {
        return $this->localKey;
    }

    public function getForeignKey(): string
    {
        return $this->foreignKey;
    }

    public function getRelatedTable(): string
    {
        return $this->target;
    }


}
