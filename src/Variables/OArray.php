<?php

namespace VersionCreator\Variables;

use VersionCreator\Variables\Variable;

class OArray extends Variable
{
    private $elements;
    protected string $name;

    public function __construct(array $config, string $name)
    {
        $this->name = $name;
        $this->elements = $config;
    }

    public function getElements()
    {
        return $this->elements;
    }

    public function addElement($element)
    {
        $this->elements[] = $element;
    }

    public function removeElement($element)
    {
        $index = array_search($element, $this->elements);
        if ($index !== false) {
            unset($this->elements[$index]);
            $this->elements = array_values($this->elements);
        }
    }

    public function getValue(int $key): mixed
    {
        return $this->elements[$key] ?? null;
    }

    public function count()
    {
        return count($this->elements);
    }

    public function toArray(): array
    {
        return $this->elements;
    }

    /**
     * Get the value of name
     */
    public function getName()
    {
        return $this->name;
    }
}
