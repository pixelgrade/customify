var gulp = require( 'gulp' ),
	sass = require( 'gulp-sass' ),
	sassUnicode = require('gulp-sass-unicode'),
	rtlcss = require( 'gulp-rtlcss' ),
	rename = require( 'gulp-rename' ),
	replace = require( 'gulp-replace' );

sass.compiler = require( 'node-sass' );

function stylesBase( src, dest, cb ) {
	return gulp.src( src )
	           .pipe( sass().on( 'error', sass.logError ) )
	           .pipe( sassUnicode() )
	           .pipe( replace( /^@charset "UTF-8";\n/gm, '' ) )
	           .pipe( gulp.dest( dest ) );
}

function compileStyles( cb ) {
	return stylesBase( './src/_scss/**/*.scss', './dist/css/', cb );
}

function stylesRTL( cb ) {
	return gulp.src( [ './dist/css/**/*.css', '!./dist/css/**/*-rtl.css' ], { base: './' } )
	           .pipe( rtlcss() )
	           .pipe( rename( function( path ) { path.basename += "-rtl"; } ) )
	           .pipe( gulp.dest( '.' ) );
}

stylesRTL.description = 'Generate style-rtl.css file based on style.css';

function watch( cb ) {
	gulp.watch( [ './src/_scss/**/*.scss' ], compile );
}

const compile = gulp.series( compileStyles, stylesRTL );

gulp.task( 'compile:styles', compile );
gulp.task( 'watch:styles', gulp.series( compile, watch ) );
