# Customify Development

## Prerequisites
- **Node.js 14** (see `.nvmrc`)
- **Gulp 4** (installed via devDependencies)

## Setup
```bash
nvm use 14
npm install
```

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

## Local Environment
- **URL:** http://barba.local/
- **Admin:** http://barba.local/wp-admin/
- **Plugin path:** /Users/georgeolaru/Local Sites/barba/app/public/wp-content/plugins/customify
