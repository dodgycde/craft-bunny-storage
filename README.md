# Bunny Storage for Craft CMS
This plugin provides a [bunny.net](https://bunny.net/) Storage & CDN integration for [Craft CMS](https://craftcms.com/).

## What Does This Plugin Do?
This plugin allows you to use bunny.net Storage as your filesystem for storing assets and the like. It also allows usage of the Bunny CDN (pull zone) to serve bunny.net storage assets.

## Requirements
This plugin requires Craft CMS 4.0.0+ or 5.0.0+.

&nbsp;

## Installation
You can install this plugin from the Plugin Store or by using Composer.

### From The Plugin Store
Go to the Plugin Store in your project’s Control Panel and search for “Bunny Storage”. Then press Install in its modal window.

### Using Composer
Open your terminal and run the following commands:

```
# go to the project directory
cd /path/to/my-project.test

# tell Composer to load the plugin
composer require dodgycode/craft-bunny-storage

# tell Craft to install the plugin
./craft plugin/install craft-bunny-storage
```

### ENV File Values
In your project's ENV file, you should define the following, using your bunny.net account information:

```
# Bunny Storage
BUNNY_STORAGE_NAME=
BUNNY_STORAGE_PASSWORD=

# The primary region of the storage bucket
BUNNY_REGION=

# The URL used for the CDN/Pull zone (to serve files)
BUNNY_CDN='https://your-pull-zone.b-cdn.net'

# Your account level API key. Used for clearing caches automatically
BUNNY_API=
```

To find this information, log in to your Bunny dashboard and go to **Storage**. To access your files via CDN, connect your storage zone to a pull zone. For more setup information, please reference the Bunny Storage [Quickstart Guide](https://docs.bunny.net/storage/quickstart).

&nbsp;

## Usage
To create a new asset filesystem on a Craft CMS project using Bunny Storage, go to **Settings** → **Filesystems**, create a new filesystem, and set the Filesystem Type setting to “Bunny Storage”. When configuring your filesystem, make sure you use ENV variables, since some of the settings contain secrets that should not be exposed through the generated project config files that result from entering data into these fields directly. Safety First!

> Note: If using Bunny CDN, the Base URL for assets should be your CDN url

&nbsp;

## License & Support
This is a paid plugin offered by [Dodgy Code](https://dodgyco.de) through the Craft CMS plugin marketplace. Please log any issues under **Issues** on [GitHub](https://github.com/dodgycde/craft-bunny-storage/issues). Thank You!



