# OpenSCAD Version Creator

**OpenSCAD Version Creator** is a script designed to process JSON input files and generate output files for OpenSCAD projects.

## Features

- Processes configurations defined in JSON files.
- Generates multiple versions of 3D models in formats such as `.stl` and `.png`.
- Compatible with PHP 8.1 or higher.
- Uses a PSR-4-based autoloading system.

## Requirements

- **PHP**: >= 8.1
- **Composer**: To manage dependencies.
- **OpenSCAD**: To render 3D models.

## Installation

1. Clone this repository to your local machine:

   ```bash
   git clone betobetok/openscad-version-creator
   cd VersionCreator
   ```

2. Install dependencies using Composer:

   ```bash
   composer install
   ```

3. Install OpenSCAD:

   - **Windows**:
     1. Download the installer from the [OpenSCAD website](https://openscad.org/downloads.html).
     2. Run the installer and follow the instructions.
     3. Ensure the OpenSCAD executable is added to your system's PATH.

   - **Linux**:
     1. Use your package manager to install OpenSCAD. For example:
        ```bash
        sudo apt update
        sudo apt install openscad
        ```
     2. Verify the installation by running:
        ```bash
        openscad --version
        ```

   - **macOS**:
     1. Install Homebrew if you haven't already:
        ```bash
        /bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
        ```
     2. Use Homebrew to install OpenSCAD:
        ```bash
        brew install --cask openscad
        ```
     3. Verify the installation by running:
        ```bash
        openscad --version
        ```

4. Ensure OpenSCAD is installed and accessible from the command line.

## Usage

### Running the Script

The main script is located at `bin/openscad-executor`. You can run it from the command line:

```bash
php bin/openscad-executor
```

### Configuration

The script uses JSON files to define input configurations. An example configuration file can be found in `New set 1_config.json`.

#### Example Configuration: `New set 1_config.json`

This file defines the parameters and variables used to generate multiple versions of 3D models. Below is an explanation of its structure:

- **`set_name`**: Defines the naming pattern for the generated models. Placeholders (e.g., `($font)`, `($fontSize)`) will be replaced with actual values during processing.

- **`set`**: Specifies the type of each variable in the configuration:
  - `-array`: Indicates that the variable is a list of predefined values.
  - `-range`: Indicates that the variable is a range of numeric values.
  - Other values (e.g., `"15"`) are treated as static values.

- **`variables`**: Contains the actual values for each variable:
  - **`$font`**: A list of font styles to be used in the models.
  - **`$fontSize`**: A numeric range `[0:0.1:2]` specifying font sizes from 0 to 2, incrementing by 0.1.
  - **`cut_corners`**: A boolean array (`true` or `false`) indicating whether corners should be cut.
  - **`name`**: A list of names for different shapes (e.g., `tetrahedron`, `cube`, etc.).

#### Example Breakdown

```json
{
    "set_name": "dice5-1_($font)__($fontSize)__(cut_corners)__(name)",
    "set": {
        "$font": "-array",
        "$fontSize": "-range",
        "cut_corners": "-array",
        "name": "-array",
        "size": "15"
    },
    "variables": {
        "$font": [
            "Centaur:style=Regular",
            "Century:style=Regular",
            "Chiller:style=Regular"
        ],
        "$fontSize": "[0:0.1:2]",
        "cut_corners": [
            "true",
            "false"
        ],
        "name": [
            "tetrahedron",
            "cube",
            "octahedron"
        ]
    }
}
```

- **`set_name`**: The output file names will follow the pattern `dice5-1_<font>_<fontSize>_<cut_corners>_<name>`.
- **`$font`**: The script will iterate through the list of fonts.
- **`$fontSize`**: The script will generate models for font sizes between 0 and 2, incrementing by 0.1.
- **`cut_corners`**: Models will be generated with and without cut corners.
- **`name`**: Models will be named after the specified shapes.

This configuration will generate multiple combinations of models based on the defined variables and their values.

### Generating Models

The script will generate `.stl` and `.png` files in the configured output directory.

### Example: Using the `Executor` Class

The `Executor` class is the main entry point for processing SCAD files and generating outputs. Below is an example of how to use it in a PHP script:

#### Example Script

```php
<?php

require_once 'vendor/autoload.php'; // Ensure Composer's autoloader is included

use VersionCreator\Executor;

try {
    // Define the output directory
    $outputDirectory = __DIR__ . '/output';

    // Create an instance of the Executor class
    $executor = new Executor($outputDirectory);

    // Define the SCAD file name and options
    $scadFileName = 'example.scad'; //SCAD file to be prosseced to get the stl archives
    $options = [
        'input-json' => __DIR__ . '/New set 1_config.json', // Path to the input JSON configuration (see example configuration)
        'output-json' => __DIR__ . '/output/versions.json', // Path to the output JSON file (Openscad customizer compatible json)
        'images' => true, // Enable PNG generation
        'sets' => [], // Process all sets (leave empty for all, or use a generated name to get just the desired stl)
        'force' => false // Do not force regeneration if files already exist
    ];

    // Run the Executor to process the SCAD file
    $result = $executor->run($scadFileName, $options);

    // Output the result
    echo "Generated files:\n";
    echo "PNG files: " . implode(', ', $result['pngs']) . "\n";
    echo "STL files: " . implode(', ', $result['stls']) . "\n";
    echo "JSON file: " . $result['json'] . "\n";

} catch (Exception $e) {
    // Handle any errors
    echo "Error: " . $e->getMessage() . "\n";
}
```

#### Explanation

1. **Create an Instance**: Instantiate the `Executor` class, specifying the output directory.
2. **Define Options**: Provide the SCAD file name and options (in the options at least “input-json” must be added, the rest of the options are optional).
3. **Run the Executor**: Call the `run` method to process the SCAD file and generate outputs.
4. **Handle Results**: The `run` method returns an array containing the generated PNG files, STL files, and the JSON output file.

#### Output Example

If the script runs successfully, it will output something like this:

```
Generated files:
PNG files: tetrahedron.png, cube.png, octahedron.png
STL files: tetrahedron.stl, cube.stl, octahedron.stl
JSON file: /path/to/output/versions.json
```

This example demonstrates how to integrate the `Executor` class into your workflow to automate the generation of 3D model files.

## Project Structure

```
VersionCreator/
├── bin/
│   └── openscad-executor
├── src/
│   ├── Executor.php
│   ├── Set.php
│   ├── Version.php
│   └── Variables/
│       ├── OArray.php
│       ├── ORange.php
│       ├── OStatic.php
│       └── Variable.php
├── vendor/
├── composer.json
└── README.md
```

## License

This project uses a **proprietary** license. Redistribution or modification of this software is not permitted without explicit permission from the author.

## Author

- **A. Kraemer**

For questions or support, feel free to contact me.