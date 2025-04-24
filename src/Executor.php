<?php

namespace VersionCreator;

class Executor
{
    private string $outputDirectory;
    private string $scadFileName;
    private string $jsonInput;

    public function __construct(string $outputDirectory = '')
    {
        if (empty($outputDirectory) === true) {
            $outputDirectory =  ROOT_DIR . '/ScadVersions/Output';
            if (is_dir($outputDirectory) === false) {
                mkdir($outputDirectory, 0777, true);
            }
        }
        $this->outputDirectory = $outputDirectory;
    }

    public function run(string $scadFileName, array $options = [])
    {
        if (str_ends_with($scadFileName, '.scad') === false) {
            $jsonInput = substr($scadFileName, 0, -4) . '.json';
            $fileName = substr($scadFileName, 0, -5);
        } else {
            $fileName = $scadFileName;
            $scadFileName = $scadFileName . '.scad';
            $jsonInput = $scadFileName . '.json';
        }
        $jsonOutputFile = empty($options['output-json']) === false ? $options['output-json'] : $fileName . '.json';
        $outputDirectory = $this->outputDirectory;

        $jsonInput = $options['i'] ?? $options['jsoni'];

        if (file_exists($jsonOutputFile)) {
            $result = json_decode(file_get_contents($jsonOutputFile), true);
        } else {
            $versions = new Version($jsonInput);
            $result = $versions->toArray();
            $result['count'] = count($result['parameterSets']);
            file_put_contents($jsonOutputFile, json_encode($result));
        }

        // Directorios
        $pngDir = $outputDirectory . '_png/';
        $stlDir = $outputDirectory . '_stl/';
        $missingPng = [];
        $missingStl = [];

        if (is_dir($stlDir) === false) {
            mkdir($stlDir);
        }
        if (is_dir($pngDir) === false) {
            mkdir($pngDir);
        }
        // Recopilar archivos que faltan
        foreach ($result['parameterSets'] as $name => $item) {
            if (!file_exists($pngDir . $name . '.png') && key_exists('images', $options) === true) {
                $missingPng[] = $name;
            }

            if (!file_exists($stlDir . $name . '.stl')) {
                $missingStl[] = $name;
            }
        }

        // Generar PNGs en paralelo
        if (!empty($missingPng) && key_exists('images', $options) === true) {
            $commands = array_map(fn($name) => "openscad --render -q -o {$pngDir}{$name}.png -p {$jsonOutputFile} -P {$name} {$scadFileName}.scad", $missingPng);
            self::parallelExec($commands);
        }

        // Generar STLs en paralelo
        if (!empty($missingStl)) {
            $commands = array_map(fn($name) => "openscad -q -o {$stlDir}{$name}.stl -p {$jsonOutputFile} -P {$name} {$fileName}.scad", $missingStl);
            self::parallelExec($commands);
        }
    }

    /**
     * Ejecuta comandos en paralelo
     * @param array $commands
     * @param int $concurrency
     */
    static public function parallelExec(array $commands, int $concurrency = 4)
    {
        $processes = [];

        foreach (array_chunk($commands, $concurrency) as $chunk) {
            foreach ($chunk as $command) {
                echo '.';
                $processes[] = proc_open($command, [], $pipes);
            }
            // Esperar a que terminen los procesos
            foreach ($processes as $process) {
                if (is_resource($process)) {
                    proc_close($process);
                }
            }
            $processes = [];
        }
    }

    function makeName($name, $placeholder, $value): string
    {
        return str_replace('(' . $placeholder . ')',  preg_replace("/[^A-Za-z0-9]/", '-', $value), $name);
    }
}
