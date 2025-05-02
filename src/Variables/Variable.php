<?php

namespace VersionCreator\Variables;

abstract class Variable
{
    protected string $name;
    protected string $type;

    public static function getVariable($type, $value, $name): Variable
    {
        switch ($type) {
            case '-array':
                if (is_array($value)) {
                    return new OArray($value, $name);
                }
                break;
            case '-range':
                if (is_string($value) && strpos($value, ':') !== false) {
                    return new ORange($value, $name);
                }
                break;
            default:
                if (is_array($value) === false) {
                    return new OStatic($value, $name);
                }
        }
        return new OArray($value, $name);
    }

    public function getType()
    {
        return array_pop(explode('\\', self::class));
    }

    public function __construct($type, $value)
    {
        $this->type = $type;
    }

    public function getValue(int $key): mixed
    {
        return null;
    }

    public function toArray(): array
    {
        return [];
    }
}
