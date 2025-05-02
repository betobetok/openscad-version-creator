<?php

namespace VersionCreator\Variables;

use VersionCreator\Variables\Variable;

class ORange extends Variable
{
    protected string $name;
    protected float $start = 0;
    protected float $end;
    protected float $step = 1;
    protected array $values = [];

    public function __construct(string $config, string $name)
    {
        $this->name = $name;
        $config = explode(':', trim($config, "[]{}()"));
        if (count($config) < 2) {
            throw new \InvalidArgumentException('Invalid range format. Expected format: start:step:end or start:end');
        }
        $this->start = (float)$config[0] ?? 0;
        $this->end = count($config) === 2 ? $config[1] : (float)round($config[2], 2);
        $this->step = (count($config) === 2 ? 1 : (float)round($config[1], 2)) ?? 1;
        if ($this->step <= 0) {
            throw new \InvalidArgumentException('Step must be greater than 0.');
        }
        if ($this->start > $this->end) {
            throw new \InvalidArgumentException('Start must be less than or equal to end.');
        }
        if ($this->step > $this->end - $this->start) {
            throw new \InvalidArgumentException('Step must be less than or equal to the range.');
        }
        
        for ($i = $this->start; $i <= $this->end; $i += $this->step) {
            $this->values[] = $i;
        }
        $this->type = 'range';
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
