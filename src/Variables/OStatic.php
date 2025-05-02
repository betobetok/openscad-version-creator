<?php

namespace VersionCreator\Variables;

use VersionCreator\Variables\Variable;

/**
 * OStatic
 */
class OStatic extends Variable
{
    protected string $name;
    private $value;

    public function __construct($config, string $name)
    {
        $this->name = $name;
        $this->value = is_numeric($config) ? (float)$config : $config;
        $this->type = 'static';
    }

    public function getValue(int $key): mixed
    {
        return $this->value;
    }

    public function count()
    {
        return 1;
    }

    /**
     * Get the value of name
     */
    public function getName()
    {
        return $this->name;
    }
}
