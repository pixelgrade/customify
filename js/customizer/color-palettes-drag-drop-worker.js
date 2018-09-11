importScripts('../vendor/clusters.js');

function comparePixelsBy( pixel1, pixel2, sortBy ) {
    return pixel1[sortBy] > pixel2[sortBy] ? 1 : pixel1[sortBy] < pixel2[sortBy] ? -1 : 0;
}

/**
 * Converts an HSL color value to RGB. Conversion formula
 * adapted from http://en.wikipedia.org/wiki/HSL_color_space.
 * Assumes h, s, and l are contained in the set [0, 1] and
 * returns r, g, and b in the set [0, 255].
 *
 * @param   {number}  h       The hue
 * @param   {number}  s       The saturation
 * @param   {number}  l       The lightness
 * @return  {Array}           The RGB representation
 */
function hslToRgb(h, s, l){
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
        }

        var q = l < 0.5 ? l * (1 + s) : l + s - l * s;
        var p = 2 * l - q;
        r = hue2rgb(p, q, h + 1/3);
        g = hue2rgb(p, q, h);
        b = hue2rgb(p, q, h - 1/3);
    }

    return [Math.round(r * 255), Math.round(g * 255), Math.round(b * 255)];
}

function sortPixelsBy( pixels, sortBy ) {

    if ( 'hue' === sortBy ) {
        pixels = pixels.filter( function( pixel ) {
            return pixel.saturation > 0.67 && pixel.lightness < 0.67;
        } )
    }

    if ( 'auto' === sortBy ) {
        sortBy = getMostVaried( pixels );
    }

    return pixels.sort( function( pixel1, pixel2 ) {
        return comparePixelsBy( pixel1, pixel2, sortBy );
    } );
}

function sortBucketsBy( buckets, sortBy ) {
    var newBuckets = new Array();

    for ( var i = 0; i < buckets.length; i++ ) {
        newBuckets.push( sortPixelsBy( buckets[i], sortBy ) );
    }
    return newBuckets;
}

function orderBucketsBy( buckets, sortBy ) {
    return buckets.sort(function(bucket1, bucket2) {
        var averagePixel1 = getAveragePixel( bucket1 );
        var averagePixel2 = getAveragePixel( bucket2 );
        return comparePixelsBy(averagePixel1, averagePixel2, sortBy);
    });
}

function orderColorsBy( colors, sortBy ) {
    return colors.sort(function(color1, color2) {
        return comparePixelsBy(color1.color, color2.color, sortBy);
    });
}

function getMostVaried( bucket ) {
    var red = [256,0];
    var green = [256,0];
    var blue = [256,0];
    var luma = [256,0];
    var h = [1,0];
    var s = [1,0];
    var l = [1,0];

    for ( var i = 0; i < bucket.length; i++ ) {
        var pixel = bucket[i];
        if ( pixel.red < red[0] ) { red[0] = pixel.red; }
        if ( pixel.red > red[1] ) { red[1] = pixel.red; }
        if ( pixel.green < green[0] ) { green[0] = pixel.green; }
        if ( pixel.green > green[1] ) { green[1] = pixel.green; }
        if ( pixel.blue < blue[0] ) { blue[0] = pixel.blue; }
        if ( pixel.blue > blue[1] ) { blue[1] = pixel.blue; }
        if ( pixel.hue < h[0] ) { h[0] = pixel.hue; }
        if ( pixel.hue > h[1] ) { h[1] = pixel.hue; }
        if ( pixel.saturation < s[0] ) { s[0] = pixel.saturation; }
        if ( pixel.saturation > s[1] ) { s[1] = pixel.saturation; }
        if ( pixel.lightness < l[0] ) { l[0] = pixel.lightness; }
        if ( pixel.lightness > l[1] ) { l[1] = pixel.lightness; }
        if ( pixel.luma < luma[0] ) { luma[0] = pixel.lightness; }
        if ( pixel.luma > luma[1] ) { luma[1] = pixel.lightness; }
    }

    h[0] *= 255;
    h[1] *= 255;
    s[0] *= 255;
    s[1] *= 255;
    l[0] *= 255;
    l[1] *= 255;

    var max = red;
    var orderBy = 'red';

    if ( blue[1] - blue[0] > max[1] - max[0] ) {
      max = blue;
      orderBy = 'blue';
  }

  if ( green[1] - green[0] > max[1] - max[0] ) {
      max = green;
      orderBy = 'green';
  }

  if ( luma[1] - luma[0] > max[1] - max[0] ) {
      max = luma;
      orderBy = 'luma';
  }

  if ( h[1] - h[0] > max[1] - max[0] ) {
      max = h;
      orderBy = 'hue';
  }

  if ( s[1] - s[0] > max[1] - max[0] ) {
      max = s;
      orderBy = 'saturation';
  }

  if ( l[1] - l[0] > max[1] - max[0] ) {
      max = l;
      orderBy = 'lightness';
  }

  return orderBy;
}

function getAveragePixel( pixels ) {
    var averagePixel = {
        red: 0,
        green: 0,
        blue: 0,
        alpha: 0,
        hue: 0,
        saturation: 0,
        lightness: 0,
        luma: 0
    };

    for ( var i = 0; i < pixels.length; i++ ) {
        var pixel = pixels[i];

        for ( var k in averagePixel ) {
            averagePixel[k] += pixel[k];
        }
    }

    for ( var k in averagePixel ) {
        averagePixel[k] /= pixels.length;
    }

    return averagePixel;
}

function getAveragePixelFromBuckets( buckets ) {
    var averagePixels = new Array();
    for ( var i = 0; i < buckets.length; i++ ) {
        averagePixels.push({
            color: getAveragePixel( buckets[i] ),
            count: buckets[i].length
        });
    }
    return averagePixels;
}

function splitBuckets( buckets, count ) {
    var newBuckets = new Array();
    for ( var b = 0; b < buckets.length; b++ ) {
        var bucket = buckets[b];
        var chunk = Math.ceil( bucket.length / count );
        var i, j;

        for ( i = 0, j = bucket.length; i < j; i += chunk ) {
            newBuckets.push( bucket.slice( i, i + chunk ) );
        }
    }

    return newBuckets;
}

function getLuma(r,g,b) {
    return 0.2126 * r + 0.7152 * g + 0.0722 * b;
}

function filterColor( color ) {
    var hsl = rgbToHsl( color[0], color[1], color[2] );

    if ( hsl[2] < 0.3 || hsl[2] > 0.7 ) {
        return false;
    }

    if ( hsl[1] < 0.4 ) {
        return false;
    }

    return true;
}

function filterDark( color ) {
    var luma = getLuma(color[0],color[1],color[2]);
    var hsl = rgbToHsl( color[0], color[1], color[2] );
    return luma < 100;
}

function filterLight( color ) {
    var luma = getLuma(color[0],color[1],color[2]);
    var hsl = rgbToHsl( color[0], color[1], color[2] );
    return luma > 100;
}

function getRgbArrayFromImageData( imageData, width, height ) {
    var rgbArray = [];

    var i, j, temparray,
        chunk = 4,
        col = 0,
        row = 0;

    for ( i = 0, j = imageData.length; i < j; i += chunk ) {
        temparray = imageData.slice( i, i + chunk );
        rgbArray.push([temparray[0],temparray[1],temparray[2]]);

        if ( col < width - 1 ) {
            col = col + 1;
        } else {
            col = 0;
            row = row + 1;
        }
    }
    return rgbArray;
}

function getHslArrayFromRgb(pixels) {
    var newPixels = [];
    for (var i = pixels.length - 1; i >= 0; i--) {
        var pixel = rgbToHsl( pixels[i][0], pixels[i][1], pixels[i][2] );
        newPixels.push( pixel );
    }
    return newPixels;
}

function getHueArrayFromRgb(pixels) {
    var newPixels = [];
    for (var i = pixels.length - 1; i >= 0; i--) {
        var pixel = rgbToHsl( pixels[i][0], pixels[i][1], pixels[i][2] );
        newPixels.push( [pixel[0], 1, 0.5] );
    }
    return newPixels;
}

function addDataToPixels(pixels, format) {
    var newPixels = [];

    for (var i = pixels.length - 1; i >= 0; i--) {
        var pixel = {};

        if ( format === "hsl" ) {
            pixels[i] = hslToRgb( pixels[i][0], pixels[i][1], pixels[i][2] );
        }

        pixel.red = pixels[i][0];
        pixel.green = pixels[i][1];
        pixel.blue = pixels[i][2];

        var hsl = rgbToHsl( pixel.red, pixel.green, pixel.blue );

        pixel.hue = hsl[0];
        pixel.saturation = hsl[1];
        pixel.lightness = hsl[2];
        pixel.luma = 0.2126 * pixel.red + 0.7152 * pixel.green + 0.0722 * pixel.blue;

        newPixels.push( pixel );
    }

    return newPixels;
}

function splitBucketsBy( buckets, splitBy, splitCount ) {
    buckets = sortBucketsBy( buckets, splitBy );
    buckets = splitBuckets( buckets, splitCount );
    return buckets;
}

function sendPalette( label, colors ) {
    self.postMessage({
        type: 'palette',
        label: label,
        colors: colors
    });
}

function getClusterBuckets( array, format ) {
    // clusters count
    clusterMaker.k(3);
    // iterations (more means more precision but longer time to process)
    clusterMaker.iterations(20);
    // set data
    clusterMaker.data(array);
    // get clusters
    var clusters = clusterMaker.clusters();
    // create pixelArray buckets from clusters
    var buckets = [];
    for (var i = clusters.length - 1; i >= 0; i--) {
        buckets.push( addDataToPixels( clusters[i].points, format ) );
    }
    return buckets;
}

addEventListener( 'message', function(event) {
    var rgbArray = getRgbArrayFromImageData( event.data.imageData, event.data.width, event.data.height );
    var colorArray = rgbArray.filter( filterColor );
    var darkArray = rgbArray.filter( filterDark );

    var colors = getAveragePixelFromBuckets( getClusterBuckets( colorArray ) );
    colors.sort(function(a, b) {
        return b.count - a.count
    });

    var dark = orderColorsBy( getAveragePixelFromBuckets( splitBucketsBy( [addDataToPixels(darkArray)], 'luma', 3 ) ), 'luma' );

    var lightArray = splitBucketsBy( [addDataToPixels(rgbArray)], 'lightness', 12 );

    lightArray = lightArray.slice(11).map( function( bucket ) {
        return bucket.map(function(pixel) {
            return [pixel.red, pixel.green, pixel.blue];
        });
    });

    lightArray = [].concat.apply([], lightArray);

    var light = orderColorsBy( getAveragePixelFromBuckets( getClusterBuckets( lightArray ) ), 'saturation' );

    light = light.map( function(color) {
        return color;
    } );

    var palette = [].concat.apply([], [colors,dark,light]);

    console.trace();

    sendPalette( 'Palette', palette );
});

/**
 * Converts an RGB color value to HSL. Conversion formula
 * adapted from http://en.wikipedia.org/wiki/HSL_color_space.
 * Assumes r, g, and b are contained in the set [0, 255] and
 * returns h, s, and l in the set [0, 1].
 *
 * @param   Number  r       The red color value
 * @param   Number  g       The green color value
 * @param   Number  b       The blue color value
 * @return  Array           The HSL representation
 */
 function rgbToHsl(r, g, b){
    r /= 255, g /= 255, b /= 255;
    var max = Math.max(r, g, b), min = Math.min(r, g, b);
    var h, s, l = (max + min) / 2;

    if(max == min){
        h = s = 0; // achromatic
    }else{
        var d = max - min;
        s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
        switch(max){
            case r: h = (g - b) / d + (g < b ? 6 : 0); break;
            case g: h = (b - r) / d + 2; break;
            case b: h = (r - g) / d + 4; break;
        }
        h /= 6;
    }
    return [h, s, l];
}
