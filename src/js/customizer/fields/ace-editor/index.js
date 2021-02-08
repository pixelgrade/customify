import $ from 'jquery';

export const handleAceEditors = () => {
  $( '.customify_ace_editor' ).each( function( key, el ) {
    const id = $( this ).attr( 'id' ),
      cssEditorInstance = ace.edit( id )

    const editor_type = $( this ).data( 'editor_type' )
    // init the ace editor
    cssEditorInstance.setTheme( 'ace/theme/github' )
    cssEditorInstance.getSession().setMode( 'ace/mode/' + editor_type )

    // hide the textarea and enable the ace editor
    const textarea = $( '#' + id + '_textarea' ).hide()
    cssEditorInstance.getSession().setValue( textarea.val() )

    // each time a change is triggered start a timeout of 1,5s and when is finished refresh the previewer
    // if the user types faster than this delay then reset it
    cssEditorInstance.getSession().on( 'change', function( event ) {
      if ( timeout !== null ) {
        clearTimeout( timeout )
        timeout = null
      } else {
        timeout = setTimeout( function() {
          textarea.val( cssEditorInstance.getSession().getValue() )
          textarea.trigger( 'change', ['customify'] )
        }, 1500 )
      }
    } )
  } )
}
