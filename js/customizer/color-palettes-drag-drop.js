  const hexDigits = ["0","1","2","3","4","5","6","7","8","9","a","b","c","d","e","f"];
  function hex( x ) {
    return isNaN(x) ? "00" : hexDigits[(x - x % 16) / 16] + hexDigits[x % 16];
  }

  function rgb2hex( color ) {
      return '#' + hex( color[0] ) + hex( color[1] ) + hex( color[2] );
  }

  function hsl2hex( color ) {
      var rgb = hsl2Rgb( color.hue, color.saturation, color.lightness );
      return rgb2hex( rgb );
  }

  function hsl2Rgb(h, s, l){
    var r, g, b;

    if(s == 0){
      r = g = b = l; // achromatic
    }else{
      var hue2rgb = function hue2rgb(p, q, t){
        if(t < 0) t += 1;
        if(t > 1) t -= 1;
        if(t < 1/6) return p + (q - p) * 6 * t;
        if(t < 1/2) return q;
        if(t < 2/3) return p + (q - p) * (2/3 - t) * 6;
        return p;
      };

      var q = l < 0.5 ? l * (1 + s) : l + s - l * s;
      var p = 2 * l - q;
      r = hue2rgb(p, q, h + 1/3);
      g = hue2rgb(p, q, h);
      b = hue2rgb(p, q, h - 1/3);
    }

    return [Math.round(r * 255), Math.round(g * 255), Math.round(b * 255)];
  }

function outputPalette( data ) {
  var pixels = data.colors;
  var colors = [];

  const $fields = $( '.c-color-palette .c-color-palette__fields input' );

  for ( i = 0; i < pixels.length; i++ ) {
    var pixel = pixels[i].color;
    var color;

    if ( typeof pixel.red !== "undefined" ) {
      color = [pixel.red, pixel.green, pixel.blue];
    } else {
      color = hsl2rgb(pixel[0], pixel[1], pixel[2]);
    }
    color = [parseInt(color[0]), parseInt(color[1]), parseInt(color[2])];
    color = rgb2hex(color);

    colors.push(color);
  }

  console.log(colors);

  $fields.filter( '.sm_color_primary' ).iris( 'color', colors[0] );
  $fields.filter( '.sm_color_secondary' ).iris( 'color', colors[1] );
  $fields.filter( '.sm_color_tertiary' ).iris( 'color', colors[2] );
  $fields.filter( '.sm_dark_primary' ).iris( 'color', colors[3] );
  $fields.filter( '.sm_dark_secondary' ).iris( 'color', colors[4] );
  $fields.filter( '.sm_dark_tertiary' ).iris( 'color', colors[5] );
  $fields.filter( '.sm_light_primary' ).iris( 'color', colors[6] );
  $fields.filter( '.sm_light_secondary' ).iris( 'color', colors[7] );
  $fields.filter( '.sm_light_tertiary' ).iris( 'color', colors[8] );

  $fields.trigger('input change');
}


( function( $, window, wp ) {

  wp.customize.bind( 'ready', function() {

    wp.customize.previewer.bind( 'ready', function() {

      $form = $( '.c-color-palette__form' );
      $body = $( 'body' );
      $targetBody = $( wp.customize.previewer.preview.targetWindow().document ).find( 'body' );
      $container = $body.add( $targetBody );

      $form.on( 'drag dragstart dragend dragover dragenter dragleave drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
      });

      $container.on( 'dragover dragenter', function() {
        $form.addClass( 'is-dragging' );
      });

      $container.on('dragleave dragend drop', function() {
        $form.removeClass( 'is-dragging' );
      });

      $form.on( 'dragover dragenter', function() {
        $form.addClass( 'is-dragover' );
      })

      $form.on( 'dragleave dragend drop', function() {
        $form.removeClass( 'is-dragover is-dragging' );
      });

      $form.on('drop', function(e) {
          var files = e.originalEvent.dataTransfer.files;

          // FileReader support
          if (FileReader && files && files.length) {
              var fr = new FileReader();
              fr.onload = function () {
                var img = document.getElementById('color-palette-output-image');

                img.src = fr.result;
                $(img).show();
                var canvas = document.getElementById('color-palette-canvas');
                var context = canvas.getContext('2d');

                img.addEventListener('load', function() {
                  canvas.width = 400;
                  canvas.height = canvas.width * img.height / img.width;
                  context.drawImage(img, 0, 0, canvas.width, canvas.height);

                  var imageData = context.getImageData(0, 0, canvas.width, canvas.height).data;
                  var w = new Worker("../wp-content/plugins/customify/js/customizer/color-palettes-drag-drop-worker.js");

                  w.postMessage({
                    type: 'image',
                    imageData: imageData,
                    width: canvas.width,
                    height: canvas.height
                  });

                  w.onmessage = function(event) {
                    var type = event.data.type;

                    if ( 'palette' === type ) {
                     outputPalette( event.data );
                    }

                  };

                });
              }
              fr.readAsDataURL(files[0]);

          }

          // Not supported
          else {
              // fallback -- perhaps submit the input to an iframe and temporarily store
              // them on the server until the user's session ends.
          }
      } );
    });

  } );
} )( jQuery, window, wp );