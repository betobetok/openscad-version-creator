<?php

namespace VersionCreator\Variables;

use VersionCreator\Variables\Variable;

class ORange extends Variable
{
    protected string $name;
    protected int $start = 0;
    protected int $end;
    protected int $step = 1;
    protected array $values = [];

    public function __construct(string $config, string $name)
    {
        $this->name = $name;
        $config = explode(':', $config);
        $this->start = $config[0] ?? 0;
        $this->end = $config[2];
        $this->step = $config[1] ?? 1;
        for ($i = $this->start; $i <= $this->end; $i += $this->step) {
            $this->values[] = $i;
        }
    }

    public function getValue(int $key): mixed
    {
        return $this->values[$key];
    }

    public function toArray(): array
    {
        return $this->values;
    }

    public function count()
    {
        return count($this->values);
    }

    /**
     * Get the value of name
     */
    public function getName()
    {
        return $this->name;
    }
}
