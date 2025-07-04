<?php

namespace VersionCreator;

use PHPUnit\Event\Runtime\PHP;

class Executor
{
    private string $outputDirectory; // Directory where output files will be stored
    private string $jsonOutputFile; // JSON output file name
    private Version $version; // Version object for processing
    private string $scadFileName; // Name of the SCAD file to process


    /**
     * Constructor for the Executor class.
     * Initializes the output directory, creating it if it doesn't exist.
     *
     * @param string $outputDirectory Optional output directory path.
     */
    public function __construct(string $outputDirectory = '')
        {
        // If no output directory is provided, use the default directory
        if (empty($outputDirectory) === true) {
            $outputDirectory =  (constant('ROOT_DIR') ?? '.') . '/ScadVersions/Output';
        }
        
        // Create the directory if it doesn't exist
        if (is_dir($outputDirectory) === false) {
            mkdir($outputDirectory, 0777, true);
        }
        $this->outputDirectory = $outputDirectory;
    }

    public function getScadFileName(): string
    {
        return $this->scadFileName;
    }

    public function setScadFileName(string $scadFileName): void
    {
        $this->scadFileName = $scadFileName;
    }

    public function generateBaseSTL($name = 'base.stl'): void
    {
        if (empty($this->scadFileName)) {
            throw new \RuntimeException("SCAD file name is not set. Use setScadFileName() to define it.");
        }
    
        $scadFilePath = $this->scadFileName;
        if (!file_exists($scadFilePath)) {
            throw new \RuntimeException("SCAD file '{$scadFilePath}' does not exist.");
        }
    
        $outputDirectory = $this->outputDirectory;
        $baseStlFile = $outputDirectory . '/' . $name;
    
        // Ensure the output directory exists
        if (!is_dir($outputDirectory)) {
            mkdir($outputDirectory, 0777, true);
        }
    
        // Command to generate the base STL
        $command = escapeshellcmd("openscad -o {$baseStlFile} {$scadFilePath}");
        exec($command, $output, $returnVar);
    
        if ($returnVar !== 0) {
            throw new \RuntimeException("Failed to generate base STL file. Command: $command");
        }
    
        echo "Base STL file generated successfully: {$baseStlFile}" . PHP_EOL;
    }

    public static function getLibrariesPaths(): array
    {

        $command = escapeshellcmd("openscad --info");
        exec($command, $output, $returnVar);

        $libraryPaths = [];
        foreach ($output as $k => $line) {
            if ($line === 'OpenSCAD library path:') {
                $k += 2;
                while (empty( trim($output[$k])) === false) {
                    $libraryPaths[] = trim($output[$k]);
                    $k++;
                }
                break;
            }
        }
        return $libraryPaths;
    }

    public static function getIncludedLibraries(string $scadFileContent): array
    {
        // Rutas de las librerías estándar de OpenSCAD (puedes obtenerlas con `openscad --info`)
        $libraryPaths = self::getLibrariesPaths();

        // Expresión regular para capturar los `include`
        preg_match_all('/^\s*include <([a-zA-Z0-9\-_\/\.]+)>/m', $scadFileContent, $matches);

        $installedLibraries = [];
        $customIncludes = [];

        foreach ($matches[1] as $include) {
            $isLibrary = false;

            // Verificar si el archivo pertenece a una de las rutas de librerías estándar
            foreach ($libraryPaths as $path) {
                if (strpos($include, $path) === 0) {
                    $installedLibraries[] = $include;
                    $isLibrary = true;
                    break;
                }
            }

            // Si no pertenece a las librerías estándar, es un include personalizado
            if (!$isLibrary) {
                $customIncludes[] = $include;
            }
        }

        return [
            'installed_libraries' => $installedLibraries,
            'custom_includes' => $customIncludes,
        ];
    }
    /**
     * Main method to process the SCAD file and generate outputs.
     *
     * @param string $scadFileName Name of the SCAD file to process.
     * @param array $options Additional options for processing. Possible values:
     *     - 'i' or 'input-json': Path to the JSON input config file (example file set_1_config.json).
     *     - 'output-json': Path to the JSON output file; the file to use in openscad (autogenerated).
     *     - 'images': Boolean flag to enable PNG generation.
     *     - 'sets' or 's': Array with names of sets to process; if empty, all sets will be processed.
     *     - 'force' or 'f': Boolean flag to force the generation of the JSON output file.
     *     - 'concurrency': Number of concurrent processes to run for generating PNG and STL files.
     *     - Any other custom options for processing.
     */
    public function run(string $scadFileName = '', array $options = []): array
    {
        if (empty($scadFileName) === true) {
            $scadFileName = $this->scadFileName;
        }
            
        // Determine the JSON input and SCAD file names based on the provided file name
        if (str_ends_with($scadFileName, '.scad') === true) {
            $jsonInput = substr($scadFileName, 0, -4) . '.json';
            $fileName = substr($scadFileName, 0, -5);
        } else {
            $fileName = $scadFileName;
            $scadFileName = $scadFileName . '.scad';
            $jsonInput = $scadFileName . '.json';
        }

        $force = $options['force'] ?? $options['f'] ?? false; // Get the force option
        $sets = $options['sets'] ?? $options['s'] ?? []; // Get the sets from options

        // Determine the JSON output file name
        $this->jsonOutputFile = empty($options['output-json']) === false ? $options['output-json'] : $fileName . '.json';
        $jsonOutputFile = $this->jsonOutputFile;
        $outputDirectory = $this->outputDirectory;

        // Get the JSON input file from options
        $jsonInput = $options['i'] ?? $options['input-json'];
        if (empty($jsonInput) === true) {
            throw new \Exception('No config input JSON file provided.'); // Throw an exception if no input JSON is provided
        }
        // If the JSON output file exists, load its content; otherwise, generate it
        

        // Define directories for PNG and STL outputs
        $pngDir = $outputDirectory . '/pngs/';
        $stlDir = $outputDirectory . '/stls/';
        $missingPng = []; // List of missing PNG files
        $missingStl = []; // List of missing STL files

        // Create the directories if they don't exist
        if (is_dir($stlDir) === false) {
            mkdir($stlDir);
        }
        if (is_dir($pngDir) === false && key_exists('images', $options) === true) {
            mkdir($pngDir);
        }

        $result = $this->createVersions($jsonInput, $jsonOutputFile, $force); // Get the JSON data

        // Identify missing PNG and STL files based on the parameter sets
        foreach ($result['parameterSets'] as $name => $item) {
            if (
                (in_array($name, $sets) === true || empty($sets) === true) &&
                (file_exists($pngDir . $name . '.png') === false || $force === true) && 
                key_exists('images', $options) === true
            ){
                $missingPng[] = $name;
            }

            if (
                (in_array($name, $sets) === true || empty($sets) === true) &&
                (file_exists($stlDir . $name . '.stl') === false || $force === true)
            ){
                $missingStl[] = $name;
            }
        }

        // Generate missing PNG files in parallel if the 'images' option is enabled
        if (empty($missingPng) === false  && key_exists('images', $options) === true) {
            $commands = array_map(function($name) use ($pngDir, $jsonOutputFile, $scadFileName, $sets) {
                if (in_array($name, $sets) === true || empty($sets) === true) {
                    return "openscad --render -q -o {$pngDir}{$name}.png -p {$jsonOutputFile} -P {$name} {$scadFileName}.scad";
                } 
                return null;
            }, $missingPng);
            self::parallelExec($commands, $options['concurrency'] ?? 4, $options['command-line'] ?? false);
        }

        // Generate missing STL files in parallel
        if (empty($missingStl) === false ) {
            $commands = array_map(function($name) use ($stlDir, $jsonOutputFile, $fileName, $sets) {
                if (in_array($name, $sets) === true || empty($sets) === true) {
                    return "openscad -q -o {$stlDir}{$name}.stl -p {$jsonOutputFile} -P {$name} {$fileName}.scad";
                } 
                return null;
            }, $missingStl);
            self::parallelExec($commands, $options['concurrency'] ?? 4, $options['in-command-line'] ?? false);
        }

        return [
            'pngs' => $missingPng,
            'stls' => $missingStl,
            'json' => $jsonOutputFile,
        ];
    }

    public function createVersions(string $jsonInput, string $jsonOutputFile = '', bool $force = false): array
    {
        if (empty($jsonOutputFile) === true) {
            $jsonOutputFile = $this->outputDirectory . '/versions.json'; // Default output file name
        }
        if (file_exists($jsonOutputFile) === true && $force === false) {
            $result = json_decode(file_get_contents($jsonOutputFile), true);
        } else {
            $this->version = new Version(file_get_contents($jsonInput)); // Create a Version object from the JSON input
            $result = $this->version->toArray();     // Convert the version data to an array
            $result['count'] = count($result['parameterSets']); // Add a count of parameter sets
            file_put_contents($jsonOutputFile, json_encode($result)); // Save the result to the JSON output file
        }
        return $result;
    }

    /**
     * Executes an array of commands in parallel with a specified concurrency level.
     *
     * @param array $commands List of commands to execute.
     * @param int $concurrency Number of commands to run in parallel.
     */
    static public function parallelExec(array $commands, int $concurrency = 4, bool $inCommandLine = false): void
    {
        $processes = []; // List of active processes

        // Split commands into chunks based on the concurrency level
        foreach (array_chunk($commands, $concurrency) as $chunk) {
            foreach ($chunk as $command) {
                if (empty($command) === true) {
                    continue; // Skip empty commands
                }
                if ($inCommandLine) {
                    echo '.'; // Print a dot for each command executed
                }
                $processes[] = proc_open($command, [], $pipes); // Start the process
            }
            if ($inCommandLine) {
                echo '.' . PHP_EOL; // Print a dot for each command executed
            }
            // Wait for all processes in the current chunk to finish
            foreach ($processes as $process) {
                if (is_resource($process)) {
                    proc_close($process);
                }
            }
            $processes = []; // Clear the process list for the next chunk
        }
    }

    /**
     * Replaces a placeholder in a string with a sanitized value.
     *
     * @param string $name The original string containing the placeholder.
     * @param string $placeholder The placeholder to replace.
     * @param string $value The value to replace the placeholder with.
     * @return string The modified string with the placeholder replaced.
     */
    function makeName($name, $placeholder, $value): string
    {
        return str_replace('(' . $placeholder . ')',  preg_replace("/[^A-Za-z0-9]/", '-', $value), $name);
    }

    /**
     * Returns the output directory path of stl archives.
     *
     * @return string The output directory path.
     */
    public function getStlOutputDir(): string
    {
        return realpath(dirname($this->outputDirectory)) . '/stls/';
    }
}