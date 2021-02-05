
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
  r /= 255;
  g /= 255;
  b /= 255;
  var max = Math.max(r, g, b), min = Math.min(r, g, b);
  var h, s, l = (max + min) / 2;

  if(max === min){
    h = s = 0; // achromatic
  }else{
    var d = max - min;
    s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
    switch(max){
      case r: h = (g - b) / d + (g < b ? 6 : 0); break;
      case g: h = (b - r) / d + 2; break;
      case b: h = (r - g) / d + 4; break;
      default: break;
    }
    h /= 6;
  }
  return [h, s, l];
}


function getRgbArrayFromImageData( imageData, width, height ) {
  var rgbArray = [];

  var i, j, temparray,
    chunk = 4,
    col = 0,
    row = 0;

  for ( i = 0, j = imageData.length; i < j; i += chunk ) {
    temparray = imageData.slice( i, i + chunk );

    if ( temparray[3] !== 0 ) {
      rgbArray.push( [temparray[0], temparray[1], temparray[2]] );
    }

    if ( col < width - 1 ) {
      col = col + 1;
    } else {
      col = 0;
      row = row + 1;
    }
  }
  return rgbArray;
}

function getClusters( array, k = 10, iterations = 10 ) {
  // clusters count
  clusterMaker.k( k ); // eslint-disable-line no-undef
  // iterations (more means more precision but longer time to process)
  clusterMaker.iterations( iterations ); // eslint-disable-line no-undef
  // set data
  clusterMaker.data(array); // eslint-disable-line no-undef
  // get clusters
  return clusterMaker.clusters();
}

function sendPalette( label, colors ) {
  self.postMessage({ // eslint-disable-line no-restricted-globals
    type: 'palette',
    label: label,
    colors: colors
  });
}

function getLuminance( rgb ) {
  return Number((0.2126 * rgb[0] + 0.7152 * rgb[1] + 0.0722 * rgb[2]).toFixed(3));
}

addEventListener( 'message', function( event ) {  // eslint-disable-line no-restricted-globals
  const rgbArray = getRgbArrayFromImageData( event.data.imageData );
//	const hslArray = rgbArray.map( rgb => rgbToHsl( rgb[0], rgb[1], rgb[2] ) );
//	let buckets = getClusters( hslArray ).map( cluster => cluster.points );
//
//	buckets = buckets.filter( bucket => !! bucket.length );


  let lightnessClusters = getClusters( rgbArray.map( rgb => [ getLuminance( rgb ) ] ), 2 ).map( cluster => cluster.centroid );
  let lightnessBuckets = lightnessClusters.map( x => [] );

  rgbArray.forEach( rgb => {
    let diff = 1;
    let index = 0;

    lightnessClusters.forEach( ( centroid, i ) => {
      let l = getLuminance( rgb );
      let newDiff = Math.abs( l - centroid );

      if ( newDiff < diff ) {
        index = i;
        diff = newDiff;
      }
    } );

    lightnessBuckets[index].push( rgb );
  } );

  function filterOutClusters( clusters, threshold ) {
    const total = clusters.reduce( ( sum, cluster ) => cluster.length + sum, 0 );
    return clusters.filter( cluster => {
      const ratio = cluster.length / total;
      return ratio.toFixed( 2 ) >= threshold;
    } );
  }

  lightnessBuckets.sort( ( bucket1, bucket2 ) => bucket1.length > bucket2.length ? -1 : 1 );
  lightnessBuckets = lightnessBuckets.filter( bucket => !! bucket.length ).map( bucket => {
    const clusters = getClusters( bucket ).map( cluster => cluster.points );
    return filterOutClusters( clusters, 0.03 );
  } )

  let buckets = lightnessBuckets.reduce( ( sum, bucket ) => {
    return sum.concat( bucket );
  }, [] );

  buckets = filterOutClusters( buckets, 0.03 );

  // cleanup
  buckets = buckets.map( bucket => {
    const clusters = getClusters( bucket, 4, 10 ).map( cluster => cluster.points );
    clusters.sort( ( bucket1, bucket2 ) => bucket1.length > bucket2.length ? -1 : 1 );
    return filterOutClusters( clusters, 0.2 );
  } )

  buckets = buckets.reduce( ( sum, bucket ) => {
    return sum.concat( bucket );
  }, [] );

  buckets.sort( ( bucket1, bucket2 ) => bucket1.length > bucket2.length ? -1 : 1 );

  const average = buckets.map( bucket => {

    const count = bucket.length;
    const sum = bucket.reduce( ( p1, p2 ) => {
      return [
        p1[0] + p2[0],
        p1[1] + p2[1],
        p1[2] + p2[2]
      ]
    } );

    return [
      sum[0] / count,
      sum[1] / count,
      sum[2] / count,
    ];
  } );

  // remove colors that are too light or too dark asuming they're white or black
  let palette = average.filter( rgb => {
    const hsl = rgbToHsl( ...rgb );
    return 0.1 < hsl[2] && hsl[2] < 0.9;
  } );

  const finalPalette = [];

  palette.reverse();

  palette.slice().forEach( ( color1, idx1 ) => {
    const hasSimilarColors = palette.slice().slice( idx1 + 1, palette.length ).some( ( color2, idx2 ) => {
      const hsl1 = rgbToHsl( ...color1 );
      const hsl2 = rgbToHsl( ...color2 );
      const hueMin = hsl1[0] < hsl2[0] ? hsl1[0] : hsl2[0];
      const hueMax = hsl1[0] >= hsl2[0] ? hsl1[0] : hsl2[0];
      const threshold = 0.1;
      const dh = Math.min( hueMax - hueMin, Math.abs( hueMin + 1 - hueMax ) );
      const ds = Math.abs( hsl1[1] - hsl2[1] );
      const dl = Math.abs( hsl1[2] - hsl2[2] );
      const remove = dh < threshold && ds < threshold && dl < threshold;

      return remove;
    } );

    if ( ! hasSimilarColors ) {
      finalPalette.push( color1 );
    }
  } );

  finalPalette.reverse();

  sendPalette( 'Palette', finalPalette );
} );

const clusterMaker = {

  data: getterSetter([], function(arrayOfArrays) {
    var n = arrayOfArrays[0].length;
    return (arrayOfArrays.map(function(array) {
      return array.length === n;
    }).reduce(function(boolA, boolB) { return (boolA & boolB) }, true));
  }),

  clusters: function() {
    var pointsAndCentroids = kmeans(this.data(), {k: this.k(), iterations: this.iterations() });
    var points = pointsAndCentroids.points;
    var centroids = pointsAndCentroids.centroids;

    return centroids.map(function(centroid) {
      return {
        centroid: centroid.location(),
        points: points.filter(function(point) { return point.label() === centroid.label() }).map(function(point) { return point.location() }),
      };
    });
  },

  k: getterSetter(undefined, function(value) { return ((value % 1 === 0) & (value > 0)) }),

  iterations: getterSetter(Math.pow(10, 3), function(value) { return ((value % 1 === 0) & (value > 0)) }),

};

function kmeans(data, config) {
  // default k
  var k = config.k || Math.round(Math.sqrt(data.length / 2));
  var iterations = config.iterations;

  // initialize point objects with data
  var points = data.map(function(vector) { return new Point(vector) });

  // intialize centroids randomly
  var centroids = [];
  for (var i = 0; i < k; i++) {
    centroids.push(new Centroid(points[i % points.length].location(), i));
  };

  // update labels and centroid locations until convergence
  for (var iter = 0; iter < iterations; iter++) {
    points.forEach(function(point) { point.updateLabel(centroids) });
    centroids.forEach(function(centroid) { centroid.updateLocation(points) });
  };

  // return points and centroids
  return {
    points: points,
    centroids: centroids
  };

};

// objects
function Point(location) {
  var self = this;
  this.location = getterSetter(location);
  this.label = getterSetter();
  this.updateLabel = function(centroids) {
    var distancesSquared = centroids.map(function(centroid) {
      return sumOfSquareDiffs(self.location(), centroid.location());
    });
    self.label(mindex(distancesSquared));
  };
};

function Centroid(initialLocation, label) {
  var self = this;
  this.location = getterSetter(initialLocation);
  this.label = getterSetter(label);
  this.updateLocation = function(points) {
    var pointsWithThisCentroid = points.filter(function(point) { return point.label() === self.label() });
    if (pointsWithThisCentroid.length > 0) self.location(averageLocation(pointsWithThisCentroid));
  };
};

// convenience functions
function getterSetter(initialValue, validator) {
  var thingToGetSet = initialValue;
  var isValid = validator || function(val) { return true };
  return function(newValue) {
    if (typeof newValue === 'undefined') return thingToGetSet;
    if (isValid(newValue)) thingToGetSet = newValue;
  };
};

function sumOfSquareDiffs(oneVector, anotherVector) {
  var squareDiffs = oneVector.map(function(component, i) {
    return Math.pow(component - anotherVector[i], 2);
  });
  return squareDiffs.reduce(function(a, b) { return a + b }, 0);
};

function mindex(array) {
  var min = array.reduce(function(a, b) {
    return Math.min(a, b);
  });
  return array.indexOf(min);
};

function sumVectors(a, b) {
  return a.map(function(val, i) { return val + b[i] });
};

function averageLocation(points) {
  var zeroVector = points[0].location().map(function() { return 0 });
  var locations = points.map(function(point) { return point.location() });
  var vectorSum = locations.reduce(function(a, b) { return sumVectors(a, b) }, zeroVector);
  return vectorSum.map(function(val) { return val / points.length });
};
