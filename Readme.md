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
   git clone <REPOSITORY_URL>
   cd VersionCreator
   ```

2. Install dependencies using Composer:

   ```bash
   composer install
   ```

3. Ensure OpenSCAD is installed and accessible from the command line.

## Usage

### Running the Script

The main script is located at `bin/openscad-executor`. You can run it from the command line:

```bash
php bin/openscad-executor
```

### Configuration

The script uses JSON files to define input configurations. An example configuration file can be found in `New set 1_config.json`.

### Generating Models

The script will generate `.stl` and `.png` files in the configured output directory.

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