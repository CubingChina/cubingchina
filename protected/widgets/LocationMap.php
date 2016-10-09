<?php

class LocationMap extends Widget {
	public $competition;
	public $location;

	public function run() {
		$location = $this->location;
		if ($location->longitude == 0 && $location->latitude == 0) {
			return;
		}
		echo CHtml::tag('div', [
			'class'=>'location-map',
			'data-longitude'=>floatval($location->longitude),
			'data-latitude'=>floatval($location->latitude),
			'data-venue'=>$location->getAttributeValue('venue'),
			'data-address'=>$location->getFullAddress(false),
		]);
		Yii::app()->clientScript->registerPackage('leaflet');
		Yii::app()->clientScript->registerScript('locationMap',
<<<EOT
$('.location-map').each(function() {
  var that = $(this);
  var data = that.data(),
    tiles = L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
      maxZoom: 18,
      attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a>'
    }),
    map = L.map(this, {
      center: L.latLng(data.latitude, data.longitude),
      zoom: 14,
      layers: [tiles]
    });
    marker = L.marker(new L.LatLng(data.latitude, data.longitude), {
      title: data.name,
    });
  marker.bindPopup([
    data.venue,
    data.address
  ].filter(function(val) {
    return val != '';
  }).join('<br>'));
  map.addLayer(marker);
});
EOT
);
	}
}
