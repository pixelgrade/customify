var gulp = require('gulp'),
  fs = require('fs'),
  plugins = require('gulp-load-plugins')()

if (fs.existsSync('./tasks/gulpconfig.json')) {
  gulpconfig = require('./gulpconfig.json')
} else {
  gulpconfig = require('./gulpconfig.example.json')
  console.warn('Don\'t forget to create your own gulpconfig.json from gulpconfig.json.example')
}

var slug = gulpconfig.slug
var packageName = gulpconfig.packagename
var textdomain = gulpconfig.textdomain
var bugReport = gulpconfig.bugreport

// -----------------------------------------------------------------------------
// Replace the plugin's text domain with the actual text domain.
// -----------------------------------------------------------------------------
function pluginTextdomainReplace () {
  return gulp.src([
    '../build/' + slug + '/**/*.php',
    '../build/' + slug + '/**/*.js',
    '../build/' + slug + '/**/*.css',
    '../build/' + slug + '/**/*.pot'
  ])
    .pipe(plugins.replace(/__plugin_txtd/g, textdomain))
    .pipe(gulp.dest('../build/' + slug))
}

pluginTextdomainReplace.description = 'Replace the __plugin_txtd text-domain placeholder with the actual text-domain, in the build files.'
gulp.task('build:translate:replacetxtdomain', pluginTextdomainReplace)

function generatePotFile () {
  return gulp.src([
    '../build/' + slug + '/**/*.php'
  ])
    .pipe(plugins.wpPot({
      domain: textdomain,
      package: packageName,
      relativeTo: '../build/' + slug + '/languages/',
      bugReport: bugReport
    }))
    .pipe(gulp.dest('../build/' + slug + '/languages/' + slug + '.pot'))
}

generatePotFile.description = 'Scan the build files and generate the .pot file.'
gulp.task('build:translate:generatepot', generatePotFile)

gulp.task('build:translate', gulp.series(
  'build:translate:replacetxtdomain',
  'build:translate:generatepot'
))
