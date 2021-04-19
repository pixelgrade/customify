import Preview from "../components/preview";
import Overlay from "../components/overlay";

import React, { useEffect, useState } from 'react';
import ReactDOM from 'react-dom';

const PreviewTabs = () => {

  const [ active, setActive ] = useState( 'site' );

  const setting = wp.customize( 'sm_advanced_palette_output' );
  const tabs = [
    { id: 'site', label: 'Live site' },
    { id: 'colors', label: 'Color system' }
  ];

  return (
    <div className={ `sm-preview` }>
      <div className="sm-preview__header">
        <div className="sm-preview__tabs">
          { tabs.map( tab => {
            const isActive = active === tab.id;

            return (
              <div className={ `sm-preview__tab ${ isActive ? 'sm-preview__tab--active' : '' }` } onClick={ () => { setActive( tab.id ) } }>{ tab.label }</div>
            )
          } ) }
        </div>
      </div>
      <div className="sm-preview__content">
        <ColorsOverlay show={ active === 'colors' } setting={ setting } />
      </div>
    </div>
  );
}

const initializePreview = () => {

  wp.customize.bind( 'ready', function() {
    wp.customize.section( 'sm_color_palettes_section', function( section ) {
      wp.customize.previewer.bind( 'ready', () => {

        const iframe = document.querySelector( '#customize-preview iframe' );

        if ( ! iframe ) {
          return;
        }

        const smPreviewTabs = document.createElement( 'div' );
        iframe.insertAdjacentElement( 'beforebegin', smPreviewTabs );
        ReactDOM.render( <PreviewTabs />, smPreviewTabs );
      } );
    } );
  } );

}

const ColorsOverlay = ( props ) => {
  const { setting, show } = props;
  const [ palettes, setPalettes ] = useState( JSON.parse( setting() ) );

  const changeListener = ( newValue ) => {
    setPalettes( JSON.parse( newValue ) );
  }

  useEffect( () => {
    // Attach the listeners on component mount.
    setting.bind( changeListener );

    // Detach the listeners on component unmount.
    return () => {
      setting.unbind( changeListener );
    }
  }, [] );

  return (
    <Overlay show={ show }>
      <Preview palettes={ palettes } />
    </Overlay>
  )
}

export default initializePreview;
