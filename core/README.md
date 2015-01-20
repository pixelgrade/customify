
Field Configuration Spec
========================

The following are system reserved field metadata keys:

 - type
 - name (optional)
 - idname (auto-generated based on name)
 - default (optional)
 - label (optional)
 - desc (optional)
 - form (internal)
 - cleanup (optional)
 - checks (optional)
 - rendering (optional)

Field detection
---------------

If an array inside the dedicated fields configuration block has the "type"
key it is considered to be a field configuration regardless of where it is
inside the configuration (since fields may have other fields as children
that themselves have other fields, etc).

Field name
----------

The key pointing to a field configuration is considered the name of the
field unless a the optional key "name" is provided. If neither is available
or the name key is empty the field is considered esthetic and while it will
be processed by the rendering routines it won't be processed by the data
handling routines (since it's not a named entity).
