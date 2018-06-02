import L from 'leaflet'
import 'leaflet.markercluster'

import 'leaflet/dist/leaflet.css'
import 'leaflet.markercluster/dist/MarkerCluster.css'
import 'leaflet.markercluster/dist/MarkerCluster.Default.css'


// a bug of leaflet
import marker from 'leaflet/dist/images/marker-icon.png'
import marker2x from 'leaflet/dist/images/marker-icon-2x.png'
import markerShadow from 'leaflet/dist/images/marker-shadow.png'

delete L.Icon.Default.prototype._getIconUrl

L.Icon.Default.mergeOptions({
  iconRetinaUrl: marker2x,
  iconUrl: marker,
  shadowUrl: markerShadow
});

if ($('body').hasClass('results-p')) {
  let map
  const personId = $('[data-person-id]').data('person-id')
  $(window).resize(function() {
    $('#competition-cluster').height($(window).height() - 20)
  }).resize()
  $('a[href="#person-map"]').on('shown.bs.tab', function() {
    if (!map) {
      $.ajax({
        url: '/api/results/personMap',
        data: {
          id: personId
        },
        dataType: 'json'
      }).then((result) => {
        const center = result.data.worlds.center
        const mapData = result.data.worlds.data
        const tiles = L.tileLayer('https://api.tiles.mapbox.com/v4/{id}/{z}/{x}/{y}.png?access_token={accessToken}', {
          maxZoom: 18,
          attribution: '&copy <a href="http://openstreetmap.org/copyright">OpenStreetMap</a>',
          id: 'baiqiang.22k7e3en',
          accessToken: 'pk.eyJ1IjoiYmFpcWlhbmciLCJhIjoiY2l2YjZ1cHoxMDBnMDJ4bG04dzdseHd6bSJ9.MsHNIxGXeC_w2BRpMUE4ng'
        })

        map = L.map('competition-cluster', {
          center: L.latLng(center.latitude, center.longitude),
          zoom: 4,
          layers: [tiles]
        })

        const markers = L.markerClusterGroup()
        let marker
        for (let i = 0; i < mapData.length; i++) {
          marker = L.marker(new L.LatLng(mapData[i].latitude, mapData[i].longitude), {
            title: mapData[i].name,
          })
          marker.bindPopup([
            '<a href="' + mapData[i].url + '" target="_blank">' + mapData[i].name + '</a>',
            mapData[i].city_name,
            mapData[i].date
          ].join('<br>'))
          markers.addLayer(marker)
        }
        map.addLayer(markers)
      })
    }
  })
}
