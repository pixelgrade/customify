# Customify - Intuitive Website Styling for WordPress

With [Customify](https://github.com/pixelgrade/customify), developers can easily create **advanced theme-specific options** inside the WordPress Customizer. Using those options, a user can make presentational changes without having to know or edit the theme code.

This plugin is **primarily intended** to be used together with [Pixelgrade themes](https://wordpress.org/themes/author/pixelgrade/). So the best way to get acquainted with it's capabilities is to study the way [one of Pixelgrade's themes](https://github.com/pixelgrade/rosa2-lite/tree/master/inc/integrations/customify) integrates with it.

**Made with care by Pixelgrade**

## How to use it?

First you need to install and activate the stable version. This will always be on [wordpress.org](https://wordpress.org/plugins/customify/)

Now go to ‘Appearance -> Customize’ menu and have fun with the new fields provided by your active theme.

## WordPress Developer Love

We know developers are a special kind of breed and they need special kinds of treats. That is why we have introduced options dedicated to them.

### Reset Buttons

In the plugin's settings page (*WP Dashboard > Settings > Customify*) you will find a checkbox called **Enable Reset Buttons** that once activated will show a new Customizer section called **Customify Toolbox** and also introduce buttons in each section or panel configured via the plugin.

All these buttons will reset the options to their default values.

### Continuous Default Values

If you want to go even further, there is a nuclear option. Simply define the `CUSTOMIFY_DEV_FORCE_DEFAULTS` constant to `true` and everywhere the default value will be used. You can play with the values in the Customizer and the live preview will work, but no value gets saved in the database.

Add this in your `wp-config.php` file:
```php
define( 'CUSTOMIFY_DEV_FORCE_DEFAULTS', true);
```

## Developing Customify

Before you can get developing, you need to have `node` and `composer` installed globally. Google is your best friend to get you to the resource to set things up.

Once you clone the Git repo, to get started open a shell/terminal in the cloned directory and run these from the command line (in this order):

```shell
composer run dev-install

npm run dev
```

You will set up all node_modules, composer packages, and compile the scripts and styles with watchers waiting for your next move.

## Building The Release .zip 

Since Customify is intended for distribution on WordPress.org you will need to build the plugin files, transpile them to the appropriate PHP version (5.6), and generate a cleaned-up zip.

After you have updated the version, added the changelog, blessed everything, **you need to clone the repo in a temporary directory** since **the build process is destructive.**

**From the temporary directory,** run this from the command line:

```shell
composer run zip
```

## Running Unit Tests

To run the PHPUnit tests, in the root directory of the plugin, run something like:

```shell
./vendor/bin/phpunit --testsuite=Unit --colors=always
```
or
```shell
composer run test
```

Bear in mind that there are **simple unit tests** (hence the `--testsuite=Unit` parameter) that are very fast to run, and there are **integration tests** (`--testsuite=Integration`) that need to load the entire WordPress codebase, recreate the db, etc. Choose which ones you want to run depending on what you are after.

**Important:** Before you can run the tests, you need to create a `.env` file in `tests/phpunit/` with the necessary data. You can copy the already existing `.env.example` file. Further instructions are in the `.env.example` file.

## License

GPLv2 and later, of course!

## Thanks!
This plugin also includes the following libraries:

* Select 2 - https://select2.github.io/
* Ace Editor - https://ace.c9.io/
* React jQuery Plugin - https://github.com/natedavisolds/jquery-react
