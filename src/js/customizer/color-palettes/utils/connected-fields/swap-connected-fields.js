import _ from "lodash";

export const swapConnectedFields = ( settings, swapMap ) => {
  // @todo This is weird. We should be able to have the settings in the proper form.
  const newSettings = JSON.parse( JSON.stringify( settings ) )
  const oldSettings = JSON.parse( JSON.stringify( settings ) )

  _.each( swapMap, function( fromArray, to ) {
    if ( typeof newSettings[to] !== 'undefined' ) {
      let newConnectedFields = []
      if ( fromArray instanceof Array ) {
        _.each( fromArray, function( from ) {
          let oldConnectedFields
          if ( _.isUndefined( oldSettings[from]['connected_fields'] ) ) {
            oldSettings[from]['connected_fields'] = []
          }
          oldConnectedFields = Object.values( oldSettings[from]['connected_fields'] )
          newConnectedFields = newConnectedFields.concat( oldConnectedFields )
        } )
      }
      newSettings[to]['connected_fields'] = Object.keys( newConnectedFields ).map( function( key ) {
        return newConnectedFields[key]
      } )
    }
  } )
  return _.clone( newSettings )
}
