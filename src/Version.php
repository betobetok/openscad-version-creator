<?php

namespace VersionCreator;

use VersionCreator\Variables\Variable;

/**
 * The Version class is responsible for processing JSON configuration data 
 * to generate parameter sets and versions. It handles the creation of 
 * configurations, parsing of SCAD JSON files, and replacement of placeholders 
 * in version names. This class is used to manage and organize sets of attributes 
 * and variables for OpenSCAD models.
 */
class Version
{
    protected Set $set; // Represents a set of attributes and variables
    protected array $versions; // Stores the generated versions
    protected array $config; // Configuration data for the version

    /**
     * Constructor for the Version class.
     * Initializes the version object by processing the provided JSON input.
     *
     * @param string $json JSON string or file path containing configuration data.
     */
    public function __construct(string $json = '')
    {
        // Check if the JSON contains a file format version and create a base config if true
        if (isset($json['fileFormatVersion']) === true) {
            $conf = $this->createBaseConfigJson($json);
        } else {
            $conf = json_decode($json, true); // Decode the JSON input into an array
        }

        $this->set = new Set($conf['set_name']); // Initialize the set with its name

        // Process each attribute and variable in the configuration
        foreach ($conf['set'] as $key => $item) {
            $this->set->addAttribute($key); // Add the attribute to the set
            $this->set->addVariable(Variable::getVariable($item, $conf['variables'][$key] ?? $conf['set'][$key], $key)); // Add the variable
        }

        // Initialize the versions array with a file format version and empty parameter sets
        $this->versions['fileFormatVersion'] = 1;
        $this->versions['parameterSets'] = [];

        // Generate parameter sets for each combination in the set
        for ($i = 0; $i < $this->set->getNumberOfSets(); $i++) {
            $setVersion = $this->set->getSet($i); // Get the current set
            $this->versions['parameterSets'] = array_merge($this->versions['parameterSets'], $this->getArrayVersion($setVersion));  // Convert it to an array and add it to the versions
        }
    }

    /**
     * Converts the version data to an array.
     *
     * @return array The version data as an array.
     */
    public function toArray(): array
    {
        return $this->versions;
    }

    /**
     * Converts a configuration array into a version array with placeholders replaced.
     *
     * @param array $conf Configuration array for a specific set.
     * @return array The processed version array.
     */
    public function getArrayVersion(array $conf): array
    {
        $name = $this->set->getName(); // Get the base name of the set
        preg_match_all('/\((.*?)\)/', $name, $matches); // Extract placeholders from the name

        $versions = [];
        // If the configuration has more values than placeholders, replace them
        if (count($conf) > count($matches[1])) {
            foreach ($matches[1] as $placeHolder) {
                $name = self::makeName($name, $placeHolder, $conf[$placeHolder] ?? ''); // Replace placeholders with values
            }
            $versions[$name] = $conf; // Add the processed version to the array
        }
        return $versions;
    }

    /**
     * Creates a base configuration JSON from a SCAD JSON file.
     *
     * @param string $scadJsonFile Path to the SCAD JSON file.
     * @return string The generated configuration JSON.
     * @throws \Exception If the file does not exist or is invalid.
     */
    public function createBaseConfigJson(string $scadJsonFile): string
    {
        // Check if the SCAD JSON file exists
        if (file_exists($scadJsonFile) === false) {
            throw new \Exception('Json File not found');
        }

        $versionsScad = json_decode(file_get_contents($scadJsonFile), true); // Decode the SCAD JSON file
        if (empty($versionsScad['parameterSets']) === true) {
            throw new \Exception('parameterSets must not be empty'); // Ensure parameter sets are not empty
        }

        $fromScad = false; // Flag to indicate if a SCAD file is found
        if (file_exists(substr($scadJsonFile, 0, -5) . '.scad') === true) {
            $scadFile = file_get_contents(substr($scadJsonFile, 0, -5) . '.scad'); // Load the SCAD file
            $fromScad = true;
        }

        // Extract the base name of the SCAD JSON file
        $subNameA = explode('/', $scadJsonFile);
        $subName = array_pop($subNameA);
        $name = substr($subName, 0, -5);

        // Initialize the configuration array
        $versions = ["set_name" => $name, "set" => [], "variables" => []];

        // Process each parameter set in the SCAD JSON file
        foreach ($versionsScad['parameterSets'] as $name => $parameterSet) {
            foreach ($parameterSet as $key => $value) {
                if ($fromScad === true) {
                    // Handle SCAD-specific configurations
                    $regKey = str_contains($key, '$') ? str_replace('$', '\$', $key) : $key;
                    preg_match('/' . $regKey . '\s*=\s?(.*?);\s*(\/\/.+)?/', $scadFile, $matches);

                    // Handle range variables
                    if (isset($matches[2]) && preg_match('/\[[0-9.,]+:[0-9.,]*:?[0-9.,]+\]/', $matches[2]) === 1) {
                        $versions["set_name"] .= '_(' . $key . ')';
                        $versions["set"][$key] = '-range';
                        $versions["variables"][$key] = trim(str_replace(['//'], '', $matches[2]));
                        continue;
                    }

                    // Handle array variables
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

                    // Handle default variables
                    if (isset($matches[2]) === false && isset($matches[1]) === true) {
                        $versions["set"][$key] = $matches[1];
                        continue;
                    }
                }

                // Default handling for variables
                $versions["set_name"] .= '_(' . $key . ')';
                $versions["set"][$key] = '-range | -array | 0';
                $versions["variables"][$key] = '["a", "b", "c"] | 0:1:10';
            }
        }

        // Warn if no SCAD file was found
        if ($fromScad === false) {
            echo 'Warning: No scad file found' . PHP_EOL;
        }

        $this->config = $versions; // Store the configuration
        return json_encode($versions); // Return the configuration as JSON
    }

    /**
     * Replaces a placeholder in a string with a sanitized value.
     *
     * @param string $name The original string containing the placeholder.
     * @param string $placeholder The placeholder to replace.
     * @param string $value The value to replace the placeholder with.
     * @return string The modified string with the placeholder replaced.
     */
    static private function makeName($name, $placeholder, $value): string
    {
        return str_replace('(' . $placeholder . ')',  preg_replace("/[^A-Za-z0-9]/", '-', $value), $name);
    }

    /**
     * public method to get the version name by replacing placeholders with values.
     *
     * @param string $name The original string containing the placeholder.
     * @param array $value The value to replace the placeholder with.
     * @return string The modified string with the placeholder replaced.
     */
    static public function getVersionName(string $name, array $value): string
    {
        preg_match_all('/\((.*?)\)/', $name, $matches);
        if (count($value) > count($matches[1])) {
            foreach ($matches[1] as $placeHolder) {
                $name = self::makeName($name, $placeHolder, $conf[$placeHolder] ?? ''); // Replace placeholders with values
            }
            return $name;
        }
        return '';
    }
}