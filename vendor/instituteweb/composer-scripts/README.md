# Composer Scripts

On Windows you get errors when you try to execute this in script section:

    "bin/example"

Because Windows expects backslashes instead of slashes. When using ImprovedScriptExecution the
paths are adjusted if host system is Windows.

 
**Usage Example:**
 
     "scripts": {
         "post-autoload-dump": [
             "\\InstituteWeb\\ComposerScripts\\ImprovedScriptExecution::apply",
             "bin/example --help"
         ]
     }
     
