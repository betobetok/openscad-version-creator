<?php

namespace VersionCreator;

use VersionCreator\Variables\Variable;

class Set
{
    /**
     * The name used to get the set of parameters to make the stl version
     *
     * @var string
     */
    private string $name;
    private array $attributes;
    private array $variables;

    public function __construct(string $name)
    {
        $this->name = $name;
    }
    /**
     * Add a variable to the variables array
     *
     * @param Variable|null $var
     * @return self
     */
    public function addVariable(?Variable $var)
    {
        if ($var === null) {
            return $this;
        }

        $this->variables[] = $var;

        return $this;
    }
    /**
     * Add an attribute to the attributes array
     *
     * @param mixed $value
     * @return self
     */
    public function addAttribute(string $value)
    {
        $this->attributes[] = $value;

        return $this;
    }

    /**
     * Set the value of attributes
     *
     * @return  self
     */
    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * Get the value of attributes
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Set the value of variables
     *
     * @return  self
     */
    public function setVariables($variables)
    {
        $this->variables = $variables;

        return $this;
    }

    /**
     * Get the value of variables
     */
    public function getVariables()
    {
        return $this->variables;
    }

    /**
     * Get the name used to get the set of parameters to make the stl version
     *
     * @return  string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
    * Retrieves a specific parameter set based on the given index.
    *
    * This method calculates the values for each variable in the set
    * by determining the appropriate index for each variable based on
    * the provided set index. It uses modular arithmetic to cycle through
    * the possible values of each variable.
    *
    * @param int $setIndex The index of the parameter set to retrieve.
    * @return array An associative array where the keys are variable names
    *               and the values are the corresponding variable values
    *               for the specified set index.
    */
    public function getSet(int $setIndex): array
    {
        $set = [];
        $prev = 1;
    
        foreach ($this->variables as $k => $variable) {
            $count = $variable->count();
            $varIndex = (int)floor($setIndex / $prev) % $count;
            $prev *= $count;
            if ($varIndex >= 0) {
                $set[$variable->getName()] = $variable->getValue($varIndex);
            }

        }
    
        return $set;
    }

    public function getNumberOfSets(): int
    {
        $count = 1;
        foreach ($this->variables as $variable) {
            $count *= $variable->count();
        }

        return $count;
    }
}
