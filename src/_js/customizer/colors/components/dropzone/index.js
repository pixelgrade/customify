import React, { useContext, useEffect, useRef, useState } from 'react';
import chroma from "chroma-js";

import { PresetPreview } from '../palette-list';
import uploadIcon from "../../../svg/upload.svg";
import Worker from "worker-loader!./worker.js";
import ConfigContext from "../../context";

import './style.scss';
import getRandomStripes from "../palette-list/get-random-stripes";
import {getPalettesFromColors} from "../builder";

export const myWorker = new Worker();

const DropZone = () => {

  const { setConfig } = useContext( ConfigContext );

  const [ files, setFiles ] = useState( null );
  const [ stripes, setStripes ] = useState( [] );

  const imgSourceRef = useRef( null );
  const imgPreviewRef = useRef( null );
  const canvasRef = useRef( null );
  const previewRef = useRef( null );

  const dragOver = ( e ) => {
    e.preventDefault();
  }

  const dragEnter = ( e ) => {
    e.preventDefault();
  }

  const dragLeave = ( e ) => {
    e.preventDefault();
  }

  const fileDrop = ( e ) => {
    e.preventDefault();
    const files = e.dataTransfer.files;
    setFiles( files );
  }

  useEffect( () => {
    myWorker.onmessage = function( event ) {
      const type = event.data.type;

      if ( 'palette' === type ) {
        const colors = event.data.colors;
        const hexColors = colors.map( rgb => chroma( rgb ).hex() );
        const config = hexColors.map( ( hex, index ) => {
          return {
            uid: `color_group_${ index }`,
            sources: [ {
              uid: `color_0`,
              label: `Color ${ index + 1 }`,
              value: hex
            } ],
          }
        } );

        setConfig( config );

        const preset = {};
        preset.palettes = getPalettesFromColors( config );
        setStripes( getRandomStripes( preset ) );
      }
    };

    return () => {
      delete myWorker.onmessage;
    };

  }, [] );

  useEffect( () => {
    const imgSource = imgSourceRef.current;
    const imgPreview = imgPreviewRef.current;

    // FileReader support
    if ( FileReader && files && files.length ) {
      var fr = new FileReader();
      fr.onload = function() {
        imgSource.src = fr.result;
        imgPreview.src = fr.result;
      }
      fr.readAsDataURL( files[0] );
    }
  }, [ files ] );

  const onImageLoad = () => {
    const imgSource = imgSourceRef.current;

    const canvas = canvasRef.current;
    const context = canvas.getContext( '2d' );

    canvas.width = Math.min( imgSource.width, 200 );
    canvas.height = canvas.width * imgSource.height / imgSource.width;
    context.drawImage( imgSource, 0, 0, canvas.width, canvas.height );

    const imageData = context.getImageData( 0, 0, canvas.width, canvas.height ).data;

    myWorker.postMessage( {
      type: 'image',
      imageData: imageData,
      width: canvas.width,
      height: canvas.height
    } );
  }

  return (
    <div className="dropzone">
      <div className="dropzone-description">
        Extract colors from an image and generate a color palette for your design system.
      </div>
      <div className="dropzone-container" onDragOver={dragOver}
           onDragEnter={dragEnter}
           onDragLeave={dragLeave}
           onDrop={fileDrop}>
        <div className="dropzone-placeholder">
          <div className="dropzone-info">
            <div className="dropzone-info-icon" dangerouslySetInnerHTML={{
              __html: `
                <svg viewBox="${ uploadIcon.viewBox }">
                  <use xlink:href="#${ uploadIcon.id }" />
                </svg>`
            } } />
            <div className="dropzone-info-title">Drag and drop your image</div>
            <div className="dropzone-info-text">or <span className="dropzone-info-anchor">select a file</span> from your computer</div>
          </div>
        </div>
        <PresetPreview stripes={ stripes } />
        <img alt="Preview" className="dropzone-image-preview" ref={ imgPreviewRef } />
      </div>
      <img alt="Source" className="dropzone-image-source" ref={ imgSourceRef } onLoad={ onImageLoad } />
      <canvas className="dropzone-canvas" ref={ canvasRef } />
    </div>
  )
}

export default DropZone;
