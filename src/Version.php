<?php

namespace VersionCreator;

use VersionCreator\Variables\Variable;

class Version
{
    protected Set $set;
    protected array $versions;
    protected array $config;

    public function __construct(string $json = '')
    {
        if (isset($json['fileFormatVersion']) === true) {
            $conf = $this->createBaseConfigJson($json);
        } else {
            $conf = json_decode($json, true);
        }
        $conf = json_decode($json, true);
        $this->set = new Set($conf['set_name']);
        foreach ($conf['set'] as $key => $item) {
            $this->set->addAttribute($key);
            $this->set->addVariable(Variable::getVariable($item, $conf['variables'][$key] ?? $conf['set'][$key], $key));
        }
        $this->versions['fileFormatVersion'] = 1;
        $this->versions['parameterSets'] = [];
        $setVersion = [];
        for ($i = 0; $i < $this->set->getNumberOfSets(); $i++) {
            $setVersion = $this->set->getSet($i);
            $this->versions['parameterSets'][] = $this->getArrayVersion($setVersion);
        }
    }

    public function toArray(): array
    {
        return $this->versions;
    }

    public function getArrayVersion(array $conf): array
    {
        $name = $this->set->getName();
        preg_match_all('/\((.*?)\)/', $name, $matches);

        $versions = [];
        if (count($conf) > count($matches[1])) {
            foreach ($matches[1] as $placeHolder) {
                $name = self::makeName($name, $placeHolder, $conf[$placeHolder] ?? '');
            }
            $versions[$name] = $conf;
        }
        return $versions;
    }

    public function createBaseConfigJson(string $scadJsonFile): string
    {
        if (file_exists($scadJsonFile) === false) {
            throw new \Exception('Json File not found');
        }
        $versionsScad = json_decode(file_get_contents($scadJsonFile), true);
        if (empty($versionsScad['parameterSets']) === true) {
            throw new \Exception('parameterSets must not be empty');
        }
        $fromScad = false;
        if (file_exists(substr($scadJsonFile, 0, -5) . '.scad') === true) {
            $scadFile = file_get_contents(substr($scadJsonFile, 0, -5) . '.scad');
            $fromScad = true;
        }

        $subNameA = explode('/', $scadJsonFile);
        $subName = array_pop($subNameA);
        $name = substr($subName, 0, -5);
        $versions = ["set_name" => $name, "set" => [], "variables" => []];
        foreach ($versionsScad['parameterSets'] as $name => $parameterSet) {
            foreach ($parameterSet as $key => $value) {
                if ($fromScad === true) {
                    $regKey = str_contains($key, '$') ? str_replace('$', '\$', $key) : $key;
                    preg_match('/' . $regKey . '\s*=\s?(.*?);\s*(\/\/.+)?/', $scadFile, $matches);
                    if (isset($matches[2]) && preg_match('/\[[0-9.,]+:[0-9.,]*:?[0-9.,]+\]/', $matches[2]) === 1) {
                        $versions["set_name"] .= '_(' . $key . ')';
                        $versions["set"][$key] = '-range';
                        $versions["variables"][$key] = trim(str_replace(['//'], '', $matches[2]));
                        continue;
                    }
                    if (isset($matches[2]) && preg_match('/\[[a-zA-Z:=" ]+(,[a-zA-Z:=" ]*)*\]/', $matches[2]) === 1) {
                        $values = explode(',', str_replace(['//', '[', ']', '"'], ['', '', '', ''], $matches[2]));
                        foreach ($values as $k => $v) {
                            $values[$k] = trim($v);
                        }
                        $versions["set_name"] .= '_(' . $key . ')';
                        $versions["set"][$key] = '-array';
                        $versions["variables"][$key] = $values;
                        continue;
                    }
                    if (isset($matches[2]) === false && isset($matches[1]) === true) {
                        $versions["set"][$key] = $matches[1];
                        continue;
                    }
                }
                $versions["set_name"] .= '_(' . $key . ')';
                $versions["set"][$key] = '-range | -array | 0';
                $versions["variables"][$key] = '["a", "b", "c"] | 0:1:10';
            }
        }
        if ($fromScad === false) {
            echo 'Warning: No scad file found' . PHP_EOL;
        }
        $this->config = $versions;
        return json_encode($versions);
    }

    static private function makeName($name, $placeholder, $value): string
    {
        return str_replace('(' . $placeholder . ')',  preg_replace("/[^A-Za-z0-9]/", '-', $value), $name);
    }
}
