<?php

namespace blink\database\schema;

interface RelationContract
{
    const HAS_ONE = 'hasOne';
    const HAS_MANY = 'hasMany';
    const HAS_MANY_INPLACE = 'hasManyInplace';

    public function getName(): string;
    public function getType(): string;
    public function getLocalKey(): string;
    public function getForeignKey(): string;
    public function getRelatedTable(): string;
}
