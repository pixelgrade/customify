const deepCopy = object => JSON.parse( JSON.stringify( object ) );

export const getNewColorHex = () => {
  return '#111111'
};

export const getNewColor = ( label = 'Color' ) => {
  return {
    uid: `color_${ new Date().getTime() }`,
    showPicker: true,
    label: label,
    value: getNewColorHex()
  }
}

export const getNewColorGroup = () => {
  return {
    uid: `color_group_${ new Date().getTime() }`,
    sources: [ getNewColor() ]
  }
}

export const addNewColorGroup = ( config, groupIndex = 0 ) => {
  const newConfig = deepCopy( config ).map( group => {
    return {
      ...group,
      sources: group.sources.map( color => {
        const { showPicker, ...otherProps } = color;
        return otherProps;
      } )
    }
  } );
  newConfig.splice( groupIndex + 1, 0, getNewColorGroup() );
  return newConfig;
};

export const addNewColorToGroup = ( config, groupIndex, index ) => {
  const newConfig = deepCopy( config );
  newConfig[groupIndex].sources.splice( index + 1, 0, getNewColor( 'Interpolated Color' ) );
  return newConfig;
}

export const deleteColor = ( config, groupIndex, index ) => {
  const newConfig = deepCopy( config );
  newConfig[groupIndex].sources.splice( index, 1 );

  if ( ! newConfig[groupIndex].sources.length ) {
    newConfig.splice( groupIndex, 1 );
  }

  return newConfig;
}

export const updateColor = ( config, groupIndex, index, newValue ) => {
  const newConfig = deepCopy( config );
  newConfig[groupIndex].sources[index] = Object.assign( {}, newConfig[groupIndex].sources[index], newValue );
  return newConfig;
}
