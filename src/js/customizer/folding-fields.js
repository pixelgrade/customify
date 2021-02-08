import $ from 'jquery';

/**
 * This function will search for all the interdependend fields and make a bound between them.
 * So whenever a target is changed, it will take actions to the dependent fields.
 * @TODO  this is still written in a barbaric way, refactor when needed
 */
export const handleFoldingFields = () => {

  if ( _.isUndefined( customify.config ) || _.isUndefined( customify.config.settings ) ) {
    return // bail
  }

  $.fn.reactor.defaults.compliant = function() {
    $( this ).slideDown()
    $( this ).find( ':disabled' ).attr( {disabled: false} )
  }

  $.fn.reactor.defaults.uncompliant = function() {
    $( this ).slideUp()
    $( this ).find( ':enabled' ).attr( {disabled: true} )
  }

  let IS = $.extend( {}, $.fn.reactor.helpers )

  const bindFoldingEvents = function( parentID, field, relation ) {

    let key = null

    if ( _.isString( field ) ) {
      key = field
    } else if ( !_.isUndefined( field.id ) ) {
      key = field.id
    } else if ( _.isString( field[0] ) ) {
      key = field[0]
    } else {
      return // no key, no fun
    }

    let value = 1, // by default we use 1 the most used value for checkboxes or inputs
      between = [0, 1] // can only be `show` or `hide`

    const target_key = customify.config.options_name + '[' + key + ']'
    const target_type = customify.config.settings[target_key].type

    // we support the usual syntax like a config array like `array( 'id' => $id, 'value' => $value, 'compare' => $compare )`
    // but we also support a non-associative array like `array( $id, $value, $compare )`
    if ( !_.isUndefined( field.value ) ) {
      value = field.value
    } else if ( !_.isUndefined( field[1] ) && !_.isString( field[1] ) ) {
      value = field[1]
    }

    if ( !_.isUndefined( field.between ) ) {
      between = field.between
    }

    /**
     * Now for each target we have, we will bind a change event to hide or show the dependent fields
     */
    const target_selector = '[data-customize-setting-link="' + customify.config.options_name + '[' + key + ']"]'

    switch ( target_type ) {
      case 'checkbox':
        $( parentID ).reactIf( target_selector, function() {
          return $( this ).is( ':checked' ) == value
        } )
        break

      case 'radio':
      case 'sm_radio':
      case 'sm_switch':
      case 'radio_image':
      case 'radio_html':

        // in case of an array of values we use the ( val in array) condition
        if ( _.isObject( value ) ) {
          value = _.toArray( value )
          $( parentID ).reactIf( target_selector, function() {
            return (
              value.indexOf( $( target_selector + ':checked' ).val() ) !== - 1
            )
          } )
        } else { // in any other case we use a simple == comparison
          $( parentID ).reactIf( target_selector, function() {
            return $( target_selector + ':checked' ).val() == value
          } )
        }
        break

      case 'range':
        const x = IS.Between( between[0], between[1] )

        $( parentID ).reactIf( target_selector, x )
        break

      default:
        // in case of an array of values we use the ( val in array) condition
        if ( _.isObject( value ) ) {
          value = _.toArray( value )
          $( parentID ).reactIf( target_selector, function() {
            return (
              value.indexOf( $( target_selector ).val() ) !== - 1
            )
          } )
        } else { // in any other case we use a simple == comparison
          $( parentID ).reactIf( target_selector, function() {
            return $( target_selector ).val() == value
          } )
        }
        break
    }

    $( target_selector ).trigger( 'change', ['customify'] )
    $( '.reactor' ).trigger( 'change.reactor' ) // triggers all events on load
  }

  $.each( customify.config.settings, function( id, field ) {
    /**
     * Here we have the id of the fields. but we know for sure that we just need his parent selector
     * So we just create it
     */
    let parentID = id.replace( '[', '-' )
    parentID = parentID.replace( ']', '' )
    parentID = '#customize-control-' + parentID + '_control'

    // get only the fields that have a 'show_if' property
    if ( field.hasOwnProperty( 'show_if' ) ) {
      let relation = 'AND'

      if ( !_.isUndefined( field.show_if.relation ) ) {
        relation = field.show_if.relation
        // remove the relation property, we need the config to be array based only
        delete field.show_if.relation
      }

      /**
       * The 'show_if' can be a simple array with one target like: [ id, value, comparison, action ]
       * Or it could be an array of multiple targets and we need to process both cases
       */

      if ( !_.isUndefined( field.show_if.id ) ) {
        bindFoldingEvents( parentID, field.show_if, relation )
      } else if ( _.isObject( field.show_if ) ) {
        $.each( field.show_if, function( i, j ) {
          bindFoldingEvents( parentID, j, relation )
        } )
      }
    }
  } )
}
