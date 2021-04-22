import './style.scss';

import React, { useEffect, useRef, useState } from 'react';
import ReactDOM from 'react-dom';

import Overlay from '../components/overlay';
import Preview from '../components/preview';

const PreviewTabs = ( props ) => {
  const [ active, setActive ] = useState( 'site' );
  const previewdDevice = wp.customize.previewedDevice.get();
  const [ visible, setVisible ] = useState( previewdDevice === 'desktop' );

  const previewRef = useRef();
  const previewHeaderRef = useRef();

  const setting = wp.customize( 'sm_advanced_palette_output' );

  const tabs = [
    { id: 'site', label: 'Live site' },
    { id: 'colors', label: 'Color system', callback: () => {
        wp.customize.section( 'sm_color_palettes_section', section => {
          section.focus();
        } )
      } }
  ];

  useEffect( () => {

    const previewResizer = window?.sm?.customizer?.resizer;

    if ( ! previewResizer ) {
      return;
    }

    const top = previewHeaderRef.current.offsetHeight;

    const style = getComputedStyle( previewRef.current, null );
    const left = parseFloat( style.left.replace( "px", "" ) );
    const right = parseFloat( style.right.replace( "px", "" ) );

    previewResizer.setOffset( {
      top,
      right,
      bottom: 0,
      left,
    } );

    previewResizer.resize();

  }, [] );

  useEffect( () => {

    const callback = ( previewdDevice ) => {
      setVisible( previewdDevice === 'desktop' );
    }

    wp.customize.previewedDevice.bind( callback );

    return () => {
      wp.customize.previewedDevice.unbind( callback );
    }

  }, [] )

  return (
    <div className={ `sm-preview ${ visible ? 'sm-preview--visible' : '' }` } ref={ previewRef }>
      <div className="sm-preview__header" ref={ previewHeaderRef }>
        <div className="sm-preview__tabs">
          { tabs.map( tab => {
            const isActive = active === tab.id;
            const noop = () => {};
            const callback = typeof tab.callback === 'function' ? tab.callback : noop;

            return (
              <div className={ `sm-preview__tab ${ isActive ? 'sm-preview__tab--active' : '' }` } onClick={ () => {
                setActive( tab.id );
                callback();
              } }>{ tab.label }</div>
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
    wp.customize.panel( 'style_manager_panel', smPanel => {
      wp.customize.section( 'sm_color_palettes_section', function( smColorsSection ) {
        wp.customize.previewer.bind( 'ready', () => {

          const iframe = document.querySelector( '#customize-preview iframe' );

          if ( ! iframe ) {
            return;
          }

          const smPreviewTabs = document.createElement( 'div' );
          iframe.insertAdjacentElement( 'beforebegin', smPreviewTabs );
          ReactDOM.render( <PreviewTabs smPanel={ smPanel } />, smPreviewTabs );

        } );
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
