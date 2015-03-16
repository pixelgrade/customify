#!/usr/bin/env ruby

# run compass compiler
puts 'Compass/Sass now running in the background.'
Kernel.exec('sass --watch --compass --sourcemap scss:css --style expanded -E utf-8  2> /dev/null')
