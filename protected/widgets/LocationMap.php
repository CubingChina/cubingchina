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
		Yii::app()->clientScript->registerScript('locationMap',
<<<EOT
$('.location-map').each(function() {
  var that = $(this);
  var data = that.data(),
    tiles = L.tileLayer('https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token={accessToken}', {
      tileSize: 512,
	  zoomOffset: -1,
      maxZoom: 18,
      attribution: '&copy; <a href="http://openstreetmap.org/copyright">OpenStreetMap</a>',
      id: 'mapbox/streets-v12',
      accessToken: 'pk.eyJ1IjoiYmFpcWlhbmciLCJhIjoiY2l2YjZ1cHoxMDBnMDJ4bG04dzdseHd6bSJ9.MsHNIxGXeC_w2BRpMUE4ng'
    });
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
