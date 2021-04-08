import Worker from "worker-loader!../../worker.js";
import './style.scss';

import React, { useState, useEffect, useRef } from 'react';

export const myWorker = new Worker();

const DropZone = () => {

  const [ files, setFiles ] = useState( null );
  const imgSourceRef = useRef( null );
  const imgPreviewRef = useRef( null );
  const canvasRef = useRef( null );

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
      <div className="dropzone-container" onDragOver={dragOver}
           onDragEnter={dragEnter}
           onDragLeave={dragLeave}
           onDrop={fileDrop}>
        <img alt="Preview" className="dropzone-image-preview" ref={ imgPreviewRef } />
      </div>
      <img alt="Source" className="dropzone-image-source" ref={ imgSourceRef } onLoad={ onImageLoad } />
      <canvas className="dropzone-canvas" ref={ canvasRef }></canvas>
    </div>
  )
}

export default DropZone;
