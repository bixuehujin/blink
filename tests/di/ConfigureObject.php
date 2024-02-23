<?php

namespace blink\tests\di;

use blink\core\BaseObject;

class ConfigureObject extends BaseObject
{
    public string $attr1;
    public string $attr2;

    public array $result;

    public function init(): void
    {
        $this->result = [
            'attr1' => $this->attr1,
            'attr2' => $this->attr2,
        ];
    }
}
