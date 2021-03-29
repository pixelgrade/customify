import { Preview } from "../components/preview";

const { useEffect, useState } = wp.element;

export const initialize = () => {

  wp.customize.bind( 'ready', function() {
    wp.customize.section( 'sm_color_palettes_section', function( section ) {
      wp.customize.previewer.bind( 'ready', () => {
        const targetWindow = wp.customize.previewer.preview.targetWindow();
        const containerID = `sm-color-palettes-preview`;
        const container = targetWindow.document.getElementById( containerID );

        if ( typeof container === "undefined" ) {
          return;
        }

        wp.customize( 'sm_advanced_palette_output', setting => {
          wp.element.render( <PalettesPreview section={ section } setting={ setting } />, container );
        } );

      } );
    } );
  } );

}

initialize();

const Overlay = ( props ) => {
  const { show } = props;

  return (
    <div className={ `sm-overlay sm-overlay--${ show ? 'visible' : 'hidden' }` }>
      { props.children }
    </div>
  )
}

const PalettesPreview = ( props ) => {
  const { section, setting } = props;
  const [ show, setShow ] = useState( false );
  const [ palettes, setPalettes ] = useState( JSON.parse( setting() ) );

  const toggleSection = ( isExpanded ) => {
    setShow( isExpanded );
  };

  const changeListener = ( newValue ) => {
    setPalettes( JSON.parse( newValue ) );
  }

  useEffect( () => {
    // Attach the listeners on component mount.
    section.expanded.bind( toggleSection );
    setting.bind( changeListener );

    // Detach the listeners on component unmount.
    return () => {
      section.expanded.unbind( toggleSection );
      setting.unbind( changeListener );
    }
  }, [] );

  return (
    <Overlay show={ show }>
      <Preview palettes={ palettes } />
    </Overlay>
  )
}
