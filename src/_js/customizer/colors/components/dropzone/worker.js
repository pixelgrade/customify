importScripts( '../../vendor_js/chroma.min.js' );

function sendPalette( label, colors ) {
  self.postMessage({ // eslint-disable-line no-restricted-globals
    type: 'palette',
    label: label,
    colors: colors
  });
}

addEventListener( 'message', function( event ) {  // eslint-disable-line no-restricted-globals
  const points = getDataArrayFromImage( event.data.imageData );
  const clusters = getClusters( points, 5, 10 );
  clusters.sort( ( cluster1, cluster2 ) => cluster1.points.length > cluster2.points.length ? -1 : 1 );
  clusters.splice( 3 );

  const palette = clusters.map( cluster => chroma( cluster.centroid, 'lab' ).rgb() );

  sendPalette( 'Palette', palette );
} );


const getLuminance = ( rgb )  =>{
  return Number( (
    0.2126 * rgb[0] + 0.7152 * rgb[1] + 0.0722 * rgb[2]
  ).toFixed( 3 ) );
}

function getDataArrayFromImage( imageData, width, height ) {
  var rgbArray = [];

  var i, j, temparray,
    chunk = 4,
    col = 0,
    row = 0;

  for ( i = 0, j = imageData.length; i < j; i += chunk ) {
    temparray = imageData.slice( i, i + chunk );

    if ( temparray[3] !== 0 ) {
      const color = chroma( [ temparray[0], temparray[1], temparray[2] ] );
      const point = color.lab();

      if ( color.luminance() > 0.05 ) {
        rgbArray.push( point );
      }
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
