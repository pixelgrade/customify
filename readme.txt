=== Customify - Intuitive Website Styling ===
Contributors: pixelgrade, vlad.olaru, babbardel, razvanonofrei, gorby31
Tags: design, customizer, fonts, colors, gutenberg, font palettes, color palettes
Requires at least: 4.9.14
Tested up to: 5.9.5
Stable tag: 2.10.5
Requires PHP: 5.6.40
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Customify is a theme Customizer booster to easily customize Fonts, Colors, and other options for a certain WordPress theme.

== Description ==

With [Customify](https://github.com/pixelgrade/customify), developers can easily create **advanced theme-specific options** inside the WordPress Customizer. Using those options, a user can make presentational changes without having to know or edit the theme code.

This plugin is **primarily intended** to be used together with [Pixelgrade themes](https://wordpress.org/themes/author/pixelgrade/). So the best way to get acquainted with it's capabilities is to study the way [one of Pixelgrade's themes](https://github.com/pixelgrade/rosa2-lite/tree/master/inc/integrations/customify) integrates with it.

**Made with care by Pixelgrade**

== Credits ==

* [Select2](https://select2.github.io) JavaScript library - License: MIT
* [CSSOM.js](https://github.com/NV/CSSOM) JavaScript library - License: MIT
* [Ace Editor](https://ace.c9.io/) JavaScript editor - License: BSD
* [jQuery React](https://github.com/natedavisolds/jquery-react) JavaScript jQuery plugin - License: MIT
* [Web Font Loader](https://github.com/typekit/webfontloader) JavaScript library - License: Apache 2.0
* [Fuse.js](http://fusejs.io) Lightweight fuzzy-search JavaScript library - License: Apache 2.0
* Default [image](https://unsplash.com/photos/OgM4RKdr2kY) for Style Manager Color Palette control - License: [Unsplash](https://unsplash.com/license)

== Changelog ==

= 2.10.5 =
* Security fixes.

= 2.10.4 =
* Tested with the latest WordPress version.
* Better handling of third-party provided fonts.
* Provide the pixelgrade_option() getter for better compatibility.

= 2.10.3 =
* Avoid invisible text failure in the Customizer Preview when all webfonts fail to load.
* Better missing font-variants handling.
* Update the Google Webfonts list.

= 2.10.2 =
* Improve Font Palettes behavior for a fresh installation (no Font Palette selected).

= 2.10.1 =
* Fix live preview for the advanced Dark Mode control introduced in 2.10.0

= 2.10.0 =
* Add an enhanced version of the Dark Mode control that can be enabled by themes.

= 2.9.2 =
* Fix compatibility with WordPress 5.5.
* Styling fixes and improvements.

= 2.9.1 =
* Fixes errors with Google Fonts in some instances.

= 2.9.0 =
* **Feature:** Introduces the ability to easily **search through all Customizer settings, menus, and widgets**
* **Feature:** Introduces the ability to migrate parent theme customization options when switching to a child theme; this way you don't lose your existing customizations
* **Improvement:** Switches to using **modern browser capabilities for fonts loading** on the frontend of your site; this should lead to better web performance and user experience, and save CO2
* **Improvement:** Use the swap font-display technique for better performance and PageSpeed score
* Updates the Google Fonts list
* Switches to using the Google Fonts CSS API V2 instead of the previous V1
* Removes the font subsets control since it is no longer needed; the needed (and available) subsets will be loaded automatically by the browser

= 2.8.0 =
* **Improvement:** **Better font palettes logic** for even more accurate site typography
* **Improvement:** All fonts now have **fallback font stacks** added for the font family CSS property
* **Improvement:** Made it much easier to use system fonts instead or beside web fonts
* **Improvement:** Allow font palettes to provide font stacks instead of a single font family
* Fix inconsistencies between fonts behavior in the Customizer preview and site frontend
* Properly sanitize font family names before using them in CSS
* Convert inconsistent font variants to standard values (regular, normal, bold)
* Handle both numerical and string font variant values
* Fix missing fonts loaded class and JavaScript event when no web fonts used
* Fix backward compatibility with WordPress 4.9.14

= 2.7.3 =
* Fixes for some edge situations when fonts would not apply on the frontend, after some time. Mainly related to cache issues.
* Fix for missing font fields CSS properties.
* Fix compatibility with the The Events Calendar plugin.

= 2.7.2 =
* Better handle legacy font values upon upgrade.

= 2.7.1 =
* Fixed a couple of inconsistencies related to cache invalidation
* Added integrations for the main caching plugins to prevent them minifying or moving the Web Font Loader script

= 2.7.0 =
* Overall performance improvements, especially on the client-side (JavaScript).
* Overall logic cleanup with a focus on consistency both on the server-side and client-side.
* Standardized data throughout the server-side and client-side.
* More consistent behavior in the Customizer, but also when editing posts or in the frontend.
* Fixed inconsistencies in block editor and classic editor integration.
* Styling fixes for the Customizer controls.
* Behavior fixes and improvements for Customizer controls, with a big focus on font controls.
* Fixed custom background control.
* Improved web font handling.
* Updated the Google Fonts list.
* Enhanced configuration capabilities for font field type CSS selectors (ability to specify allowed CSS properties per selector).
* Removed 'typography' field type with automatic conversion to the new 'font' field type.
* Minor fixes for the Style Manager behavior.
* Tested with the latest WordPress version (v5.4).
* Increased minimum required PHP version to 5.4.

= 2.6.0 =
* Fixes related to cache invalidation.
* Improvements to Font Palettes.
* Fixes for missing Customizer theme controls.

= 2.5.9 =
* Fixes Style Presets preview display
* Fixes Color Palettes integration with Gutenberg
* Improve display of radio and range controls in Customizer

= 2.5.8 =
* Styling fixes for Color Palettes.
* Added reset font logic for better default font palettes.
* Fixes for Font Palettes font weights.

= 2.5.7 =
* Styling fixes for the Font control.
* Bug fixes for the Font control.
* Added RTL stylesheets.

= 2.5.6 =
* Improved styling for the Font control.
* Improved handling of minimal required PHP version.
* Cleanup stylesheets and scripts for better performance and easier maintenance.
* Updated Google Fonts list to include the latest additions.
* Compatibility with WordPress 5.3.0.

= 2.5.5 =
* Fixed an issue with the default font weight for the Typography field.
* Improved range field behavior when writing directly in the numerical field.
* Fixed inconsistency on switch theme with the Style Manager coloration level, diversity and shuffle controls.

= 2.5.4 =
* Improved default font palettes configuration.

= 2.5.3 =
* Fixed as series of issues with Font Palettes in Style Manager.
* Improved the Color Palettes.
* Fixes a series of edge-cases in the Customizer.

= 2.5.2 =
* Improved the predictability and resilience of the Style Manager controls.

= 2.5.1 =
* Styling improvements to the Style Manager controls.
* Fixed a strange bug that would result in a fatal error upon activation.

= 2.5.0 =
* JavaScript cleanup and performance enhancements
* Fixed some bugs with the Style Manager.
* Fixed some rare bugs with the plugin config merge.

= 2.4.0 =
* Big performance enhancements related to how customization settings and configurations get loaded.
* Fixed a nasty regression that caused customization settings saved in a option to not be loaded, causing styles to be missing.
* Pretty important code refactoring and cleanup to make things more predictable and stable.

= 2.3.5.1 =
* Minor configuration fix.

= 2.3.5 =
* Minor refactoring.
* Fixed issues where customization values were not stored properly.

= 2.3.4 =
* Fixed warnings that were appearing when PHP has version 7.2.0+.

= 2.3.3 =
* Fixed Google Fonts not working in the new block editor (Gutenberg).

= 2.3.2 =
* Fixed the fact that Customizer style changes were not reflected in the live preview. A problem introduced in the previous update.

= 2.3.1 =
* Fixed some warnings that appeared in certain situations.

= 2.3.0 =
* Improved support for the new **Gutenberg block editor.** Compatible with the latest WordPress 5.0 beta version.
* Big **performance improvements** both in the frontend and also in the Customizer.
* Cleanup regarding old and deprecated features.

= 2.2.0 =
* Added support for the new **Gutenberg block editor.**

= 2.1.3 =
* Improve Customizer section grouping in the Theme Options Panel
* Fix Font Palettes giving huge sizes to font options set in ems

= 2.1.2 =
* Avoid short array syntax to ensure PHP 5.2 compatibility

= 2.1.1 =
* Hide the Fonts section for themes that do not declare support for Font Palettes

= 2.1.0 =
* This new version of Customify lets you conveniently change the design of your site with font palettes. Easy as pie.
* Added previews for color palette filters.

= 2.0.2 =
* Fixed bug where no CSS was output for some settings with default value.

= 2.0.1 =
* Minor fix for the color pickers.

= 2.0.0 =
* Added the much improved and overall awesome **Color Palettes v2.0 styling system** (all modesty aside).
* Minor improvements that are secondary to the one above. Enjoy.

= 1.9.1 =
* Fixed the Customizer JS crash due to wrong merge.

= 1.9.0 =
* Added ability to modify existing Customizer panels, sections, controls
* Added system for admin notifications
* Overall enhancements for more performance and stability

= 1.8.0 =
* Added altered state for colors in the current color palette when any of the controls connected to the color has been modified
* Added the colors from the current palette to all the color pickers in the Theme Options section
* Fixed bug where default values were being forced in Customizer Preview at first load
* Fixed bug preventing CSS output for color controls in the Style Manager section of the Customizer

= 1.7.4 =
* Reorganized Customizer custom sections and grouped them into Theme Options, thus making the Style Manager panel stand out.
* Refactored parts for more performance and clarity.

= 1.7.3 =
* Added HEX field for colors in the current Color Palette
* Updated Google Webfonts list

= 1.7.2 =
* Fixed issue with **Color Palettes** working only after choosing one variation
* Fixed bug preventing some options to live update the Customizer preview

= 1.7.1 =
* Fixed issue with **Color Palettes** overwriting custom colors in Live Preview

= 1.7.0 =
* Added **Dynamic Color Palettes** for a smoother experience
* Fixed issue with the Style Manager crashing the Customizer when not using a theme with support for it.

= 1.6.5 =
* Added **Color Palettes Variations** to the Style Manager Customizer section
* Improved Color Palettes logic to better handle differences between various color palettes
* Improved master color connected fields logic to allow for a smoother experience
* Updated Google Fonts list
* Fixed some issues with the connected fields logic
* Fixed some Customizer preview scaling issues
* Fixed a potential bug with the options' CSS config (multiple configs with the same property but with different selectors)

= 1.6.0 =
* Added **Style Manager** Customizer section with theme supports logic
* Added connected fields logic for easy chaining of Customizer controls
* Fixed a couple of styling inconsistencies regarding the Customizer

= 1.5.7 =
* Improved development logic for easier testing
* Improved and fixed reset settings buttons
* Fixed a couple of styling inconsistencies regarding the Customizer

= 1.5.6 =
* New Fields Styling Improvements

= 1.5.5 =
* Added Compatibility with WordPress 4.9

= 1.5.4 =
* Allow 0 values for fonts line-height and letter-spacing
* Improved the plugin loading process and the CSS inline output
* Fixed small style issues for the Customizer bar

= 1.5.3 =
* Update Style for WordPress 4.8
* Updated Google Fonts list
* Fixed the double output of the custom CSS
* Fixed Menu Add Button overlap

= 1.5.2 =
* Fixed Background field output
* Fixed Font's preview in wp-editor
* Added Reset Theme Mods tool

= 1.5.1 =
* Added support for `active_callback` argument for customizer controls
* Customizer assets refactor

= 1.5.0 =
* Plugin core refactored for a better performance
* Fixed Font Weight saving
* Fixed Font Subset saving
* Fix Select2 enqueue_script

= 1.4.2 =
* Improved Font style output in front-end. Now is just one style element with all the fonts inside.
* Improved Fonts panels, now only one can be opened to avoid confusion
* Fixed Presets with fonts
* Fixed Google Fonts with italic weights
* Fixed Range input field
* Small Fixes

= 1.4.1 =
* Fixed Multiple local fonts

= 1.4.0 =
* Make Customify compatible with the [4.7 customizer changes](https://make.wordpress.org/core/2016/10/12/customize-changesets-technical-design-decisions)
* Add `show_if` [config option](https://github.com/pixelgrade/customify#conditional-fields)
* Fix Conflict with Jetpack - Related posts
* Fix Javascript callbacks loss
* Switch de default storage from option to theme_mod
* Fixed Incorrect Color Panel Height
* Fixed Font field weight in customizer preview

= 1.3.1 =
* Fixed compatibility with PHP <= 5.3.x

= 1.3.0 =
* Added the new and awesome `font` selector
* The live CSS editor is now removed for 4.7, but don't worry, your style will be imported into the new [CSS Editor](https://make.wordpress.org/core/2016/11/26/extending-the-custom-css-editor/)
* Added compatibility with 4.7

= 1.2.7 =
* Added capability to control the Jetpack Sharing default options

= 1.2.6 =
* Added capability to define Jetpack default and hidden modules

= 1.2.5 =
* Fixed WordPress 4.7 incompatibilities

= 1.2.4 =
* Added: Support for Fonto plugin
* Improved the font selector
* Fixed presets on ssl

= 1.2.3 =
* Added: Support for conditional fields display
* Fixed weights for local fonts
* Fixed Ace editor warnings
* Fixed some rare PHP warnings

= 1.2.2 =
* Added: Customizer styling
* Fixed some rare warnings with google fonts

= 1.2.1 =
* Improve default fonts parse, and fix some legacy cases
* Remove google api code when google fonts is disabled

= 1.2.0 =
* Added: Compatibility with WordPress 4.4.0
* Added: Presets can now set fonts and font weights
* Fixed: Now range fields can have `0` as default
* Fixed: Font subsets style
* Fixed: Fixed some PHP and javascript warnings
* Updated: Font field style

= 1.1.7 =
* Added: Compatibility with WordPress 4.3.1
* Added: Custom fonts can be used now as defaults
* Fixed: Fonts preview
* Fixed: Some rare errors with PHP 5.2.x
* Fixed: Some font variants warnings with PHP 5.2.x

= 1.1.6 =
* Added: Custom background field with bacgkround-* css properties selects
* Added: Compatibility with WordPress 4.3.x
* Added: Compatibility with PHP 5.2.x
* Improved: Live CSS Editor is now live...for real
* Updated: ACE Editor
* Updated: The list of google fonts is now up to date


= 1.1.5 =
* Added: Live-preview support for `text` and `textarea` fields.
* Added: **Unit** parameter for css values(now we can use all the css units like em, rem, vh, all of them :D).
* Fixed: Editor style for Typekit fonts.
* Fixed: Editor style with default values.
* Fixed: Live Preview small fixes
* Updated: The list of google fonts is now up to date

= 1.1.4 =
* Added: Ace Editor field.
* Added: HTML field.
* Added: Sanitize callbacks parameter and a default sanitizer for the checkbox field.
* Fixed: Slight styling issues.

= 1.1.2 =
* Added: Option to add Customify's changes in the editor.
* Added: Possibility to load Typekit fonts through config.

= 1.1.1 =
* Added: Radio input with image label.
* Added: Javascript callback for css properties.
* Update: Updated Ace editor.

= 1.1.0 =
* Added: [Preset](https://github.com/pixelgrade/customify/blob/master/README.md#presets_title) field type.
* Added: Reset buttons (disabled by default).
* Added: Button field.

== Installation ==

1. Install Customify either via the WordPress.org plugin directory, or by uploading the files to your `/wp-content/plugins/` directory
2. After activating Customify go to `Appearance → Customize` and have fun with the new felds
3. For further instructions and how to setup your own fields, read our [detailed documentation](http://github.com/pixelgrade/customify/blob/dev/README.md)

== Frequently Asked Questions ==

= Is there a way to reset Customify to defaults? =
Reset buttons are available for all the options or for individual sections or panels.
They are disabled by default to avoid useless/accidental resets.
To enable them simply go to Dashboard -> Settings -> Customify and check "Enable Reset Buttons"
