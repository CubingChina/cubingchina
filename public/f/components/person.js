import L from 'leaflet'
import 'leaflet.markercluster'
import echarts from 'echarts'
import china from 'echarts/map/json/china'

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
})

if ($('body').hasClass('results-p')) {
  let map = false
  const personId = $('[data-person-id]').data('person-id')
  $(window).resize(function() {
    let win = $(window)
    $('#competition-cluster').height(win.height() - 20)
    $('#competition-provinces').height(Math.min(win.height() - 20, win.width() - 60))
    if (map) {
      map.resize()
    }
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
        const data = result.data
        //worlds
        const center = data.worlds.center
        const mapData = data.worlds.data
        const tiles = L.tileLayer('https://api.tiles.mapbox.com/v4/{id}/{z}/{x}/{y}.png?access_token={accessToken}', {
          maxZoom: 18,
          attribution: '&copy <a href="http://openstreetmap.org/copyright">OpenStreetMap</a>',
          id: 'baiqiang.22k7e3en',
          accessToken: 'pk.eyJ1IjoiYmFpcWlhbmciLCJhIjoiY2l2YjZ1cHoxMDBnMDJ4bG04dzdseHd6bSJ9.MsHNIxGXeC_w2BRpMUE4ng'
        })

        const worlds = L.map('competition-cluster', {
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
        worlds.addLayer(markers)

        //provinces
        map = echarts.init(document.getElementById('competition-provinces'))
        echarts.registerMap('China', china)
        const values = data.provinces.map((province) => province.value)
        const option = {
            tooltip: {
              trigger: 'item',
              showDelay: 0,
              transitionDuration: 0.2,
              formatter: function (params) {
                return params.dataIndex < 0 ? '' : params.data.province + ': ' + params.value;
              }
            },
            visualMap: {
              left: 'right',
              min: Math.min(...values),
              max: Math.max(...[10, ...values]),
              inRange: {
                color: [
                  '#fae5f8',
                  '#f2baec',
                  '#e990e0',
                  '#e165d4',
                  '#d93ac9',
                  '#bc24ac',
                  '#911c85',
                  '#7c1871',
                  '#66145e',
                  '#51104a',
                ]
              },
              text: ['Max', 'Min'],
              calculable: true
            },
            series: [
              {
                name: 'Visited Provinces',
                type: 'map',
                roam: true,
                map: 'China',
                itemStyle: {
                  emphasis: {
                    label: {
                      show: true
                    }
                  }
                },
                data: result.data.provinces
              }
            ]
        }
        map.setOption(option)
      })
    }
  })
}
