#!/usr/bin/env ruby

# change to script
Dir.chdir File.expand_path(File.dirname(__FILE__))
# run compass compiler
Kernel.exec('sass --compass --force --update scss:css --style expanded -E utf-8 2> /dev/null')
