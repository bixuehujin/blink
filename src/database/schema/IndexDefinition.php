<?php

namespace blink\database\schema;

class IndexDefinition
{
    public string $name;
    public string $type;
    public array $columns;
    public array $options;

    public function __construct(string $name, string $type, array $columns, array $options = [])
    {
        $this->name = $name;
        $this->type = $type;
        $this->columns = $columns;
        $this->options = $options;
    }
}
