# Customify Development

## Repository
- **GitHub:** https://github.com/pixelgrade/customify
- **WordPress.org:** https://wordpress.org/plugins/customify/
- **Current version:** 2.10.6
- **Branch:** `dev` (main development branch)

## Prerequisites
- **Node.js 14** (see `.nvmrc`)
- **Gulp 4** (installed via devDependencies)
- **PHP 7.4+**
- **WordPress 5.9+**

## Setup
```bash
nvm use 14
npm install
```
Note: `fsevents` build warning on macOS is non-fatal (optional dependency).

## Development Mode
```bash
gulp watch
```
Watches all SCSS and JS files, recompiles on change.

## Gulp Commands

| Command | Description |
|---------|-------------|
| `gulp start` | Compile all styles and scripts (production) |
| `gulp watch` | Watch SCSS + JS and recompile on change |
| `gulp styles-dev` | Compile styles with sourcemaps (expanded) |
| `gulp styles` | Compile styles (compressed, production) |
| `gulp scripts` | Minify all JS scripts |
| `gulp styles-watch` | Watch only SCSS files |
| `gulp scripts-watch` | Watch only JS files |
| `gulp build` | Create clean build directory |
| `gulp zip` | Create distributable ZIP archive |

## File Structure
- `scss/*.scss` - Source stylesheets, compiled to `css/`
- `js/customizer/*.js`, `js/*.js` - Source scripts, minified versions get `.min.js` suffix
- RTL stylesheets are auto-generated with `-rtl` suffix
- `CLAUDE.md` is excluded from zip builds (listed in `gulpfile.js` `removeUnneededFiles`)

## Release Process

### 1. Version bump
Update version in three places:
- `customify.php` — plugin header `Version:` line
- `customify.php` — `PixCustomifyPlugin::instance()` second argument
- `readme.txt` — `Stable tag:` header

Also update as needed:
- `readme.txt` — `Tested up to:`, `Requires PHP:`, `Requires at least:`
- `customify.php` — matching plugin headers
- `includes/class-pixcustomify.php` — `$minimalRequiredPhpVersion` property (~line 117)

### 2. Build zip
```bash
nvm use 14
npx gulp zip
```
Output: `../Customify-X-X-X.zip` (in parent plugins directory)

### 3. GitHub release
```bash
git push origin dev
gh release create vX.X.X ../Customify-X-X-X.zip --title "vX.X.X" --notes "changelog"
```
To update an existing release zip: `gh release upload vX.X.X ../Customify-X-X-X.zip --clobber`

### 4. WordPress.org SVN
SVN repo: `https://plugins.svn.wordpress.org/customify/`
SVN username: `babbardel`

```bash
# Checkout trunk
svn checkout https://plugins.svn.wordpress.org/customify/trunk /tmp/customify-svn

# Sync from zip (preserves .svn metadata)
unzip -o ../Customify-X-X-X.zip -d /tmp/customify-unzipped
rsync -a --delete --exclude='.svn' /tmp/customify-unzipped/customify/ /tmp/customify-svn/

# Add new files, remove deleted files
cd /tmp/customify-svn
svn status | grep '^\?' | awk '{print $2}' | xargs -I{} svn add "{}"
svn status | grep '^\!' | awk '{print $2}' | xargs -I{} svn delete "{}"

# Commit trunk
echo 'PASSWORD' | svn commit -m "message" --username babbardel --force-interactive

# Tag the release (wordpress.org reads metadata from the tag, not trunk)
echo 'PASSWORD' | svn copy \
  https://plugins.svn.wordpress.org/customify/trunk \
  https://plugins.svn.wordpress.org/customify/tags/X.X.X \
  -m "Tag X.X.X" --username babbardel --force-interactive
```

**Important:** WordPress.org sidebar metadata (Tested up to, Requires PHP, etc.) comes from the **tagged version's** readme.txt, not trunk. If you update metadata after tagging, you must delete and recreate the tag:
```bash
echo 'PASSWORD' | svn delete https://plugins.svn.wordpress.org/customify/tags/X.X.X -m "Remove old tag" --username babbardel --force-interactive
echo 'PASSWORD' | svn copy https://plugins.svn.wordpress.org/customify/trunk https://plugins.svn.wordpress.org/customify/tags/X.X.X -m "Re-tag X.X.X" --username babbardel --force-interactive
```

Note: `--non-interactive` does NOT work for SVN auth here. Must use `--force-interactive` with echo pipe.

## Security Considerations

### AJAX handlers
All `wp_ajax_` handlers must have both:
1. **Nonce verification:** `check_ajax_referer()`
2. **Capability check:** `current_user_can('manage_options')`

Current AJAX handlers:
- `customify_migrate_customizations_from_parent_to_child_theme` — `extras.php`
- `customify_style_manager_user_feedback` — `class-customify-style-manager.php`

### REST API endpoints
- `customify/v1/delete_theme_mod` — `class-customify-settings.php` (has both nonce + capability check)

### Settings form
- `class-customify-settings.php` — uses `check_admin_referer()` + `manage_options` capability via `add_options_page()`

## PHP Compatibility Notes
- Minimum PHP 7.4 (set in `class-pixcustomify.php:$minimalRequiredPhpVersion`)
- Added null safety guards for `get_option()`, `apply_filters()`, `preg_split()` returns (PHP 8.x compat)
- All classes have explicit property declarations (PHP 8.2 dynamic properties deprecation)

## Key Files
- `customify.php` — main plugin file, version, bootstrap
- `includes/class-pixcustomify.php` — main plugin class, hooks, enqueuing
- `includes/extras.php` — helper functions, theme migration AJAX handler
- `includes/class-customify-style-manager.php` — Style Manager, color/font palettes, feedback handler
- `includes/class-customify-settings.php` — admin settings page, REST API, nonce handling
- `includes/class-customify-color-palettes.php` — color palette rendering
- `includes/class-customify-block-editor.php` — Gutenberg integration
- `gulpfile.js` — build system configuration

## Local Environment
- **URL:** http://barba.local/
- **Admin:** http://barba.local/wp-admin/
- **Plugin path:** /Users/georgeolaru/Local Sites/barba/app/public/wp-content/plugins/customify
