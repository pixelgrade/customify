const {
  useCallback,
  useEffect,
  useState,
} = wp.element;

import ColorControls from "./components/color-controls";
import { getPalettesFromColors } from "./utils";

const getColorsFromInputValue = ( value ) => {
  let colors;

  try {
    colors = JSON.parse( value );
  } catch( e ) {
    colors = [];
  }

  return colors;
}

const getValueFromColors = ( colors ) => {
  return JSON.stringify( colors );
}

const Builder = ( props ) => {
  const { sourceSettingID, outputSettingID } = props;
  const sourceSetting = wp.customize( sourceSettingID );
  const outputSetting = wp.customize( outputSettingID );
  const [ colors, setColors ] = useState( getColorsFromInputValue( sourceSetting() ) );

  const changeListener = useCallback( value => {
    const colors = getColorsFromInputValue( value );
    const palettes = getPalettesFromColors( colors );

    setColors( colors );

    if ( typeof outputSetting !== "undefined" ) {
      outputSetting.set( JSON.stringify( palettes ) );
    }
  }, [] );

  useEffect(() => {
    // Attach the listeners on component mount.
    sourceSetting.bind( changeListener );

    // Detach the listeners on component unmount.
    return () => {
      sourceSetting.unbind( changeListener );
    }
  }, []);

  useEffect(() => {
    sourceSetting.set( getValueFromColors( colors ) );
  }, [colors])

  return (
    <div>
      <ColorControls colors={ colors } setColors={ setColors } />
    </div>
  );
}

const initializePaletteBuilder = ( sourceSettingID, outputSettingID ) => {
  const containerID = `customize-control-${ sourceSettingID }_control`;
  const container = document.getElementById( containerID );
  const target = document.createElement( 'DIV' );

  if ( typeof container === "undefined" ) {
    return;
  }

  container.insertBefore( target, container.firstChild );
  wp.element.render( <Builder sourceSettingID={ sourceSettingID } outputSettingID={ outputSettingID }/>, target );
}

const getCSSFromPalette = ( palette ) => {
  const { colors } = palette;

  return colors.reduce( ( colorsAcc, color, colorIndex ) => {
    return `${ colorsAcc }
        --sm-current-color-${ colorIndex }: ${ color.value };`;
  }, '' );
}

const getCSSFromPalettes = ( palettes ) => {

  if ( ! palettes.length ) {
    return '';
  }

  // the old implementation generates 3 fallback palettes and
  // we need to overwrite all 3 of them when the user starts building a new palette
  // @todo this is necessary only in the Customizer preview
  while ( palettes.length < 3 ) {
    palettes.push( palettes[0] );
  }

  return palettes.reduce( ( palettesAcc, palette, paletteIndex ) => {

    let selector = `.sm-palette-${ paletteIndex }`;

    if ( paletteIndex === 0 ) {
      selector = `:root, ${ selector }`
    }

    return `
      ${ palettesAcc }
      
      ${ selector } { ${ getCSSFromPalette( palette ) } }
    `;
  }, '');
}

const getCSSFromInputValue = ( value ) => {
  const colors = getColorsFromInputValue( value );
  const palettes = getPalettesFromColors( colors );

  return getCSSFromPalettes( palettes );
}

export {
  initializePaletteBuilder,
  getCSSFromInputValue,
  getCSSFromPalettes,
}
