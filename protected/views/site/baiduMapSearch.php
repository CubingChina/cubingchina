<div id="map" style="width:100%; height:100%"></div>
<script charset="utf-8" src="http://api.map.baidu.com/api?ak=5MnAdRxax54stQ29hR91Fxl1&v=2.0"></script>
<script>
  var map = new BMap.Map('map');
  var point = new BMap.Point(116.331398,39.897445);
  var geocoder = new BMap.Geocoder();
  var mapAddress = parent.document.getElementById('kindeditor_plugin_map_address');
  var mapTitle = parent.document.getElementById('kindeditor_plugin_map_title');
  var geolocation = new BMap.Geolocation();
  var marker = new BMap.Marker(point);
  var infoWindow = new BMap.InfoWindow('地址获取中……');
  mapTitle.onkeyup = function() {
    infoWindow.setTitle(mapTitle.value);
  }
  infoWindow.setTitle(mapTitle.value);
  //地图
  map.centerAndZoom(point, 12);
  map.addControl(new BMap.NavigationControl());
  map.enableScrollWheelZoom();
  map.addOverlay(marker);
  setAddress(point);
  //标记
  marker.enableDragging();
  marker.addEventListener('dragend', function(e) {
    setAddress(e.point);
    map.closeInfoWindow(infoWindow);
  });
  marker.addEventListener('click', function(e) {
    setAddress(marker.getPosition());
    map.openInfoWindow(infoWindow, marker.getPosition());
  });
  geolocation.getCurrentPosition(function(result) {
    if (this.getStatus() == BMAP_STATUS_SUCCESS) {
      marker.setPosition(result.point);
      map.openInfoWindow(infoWindow, marker.getPosition());
      setAddress(result.point);
    } else {
      var localCity = new BMap.LocalCity();
      localCity.get(function(result) {
        marker.setPosition(result.center);
        map.openInfoWindow(infoWindow, marker.getPosition());
        setAddress(result.center);
      });
    }
  }, {enableHighAccuracy: true});
  function setAddress(point) {
    map.panTo(point);
    geocoder.getLocation(point, function(result) {
      mapAddress.value = result.address;
      infoWindow.setContent(result.address); 
    });
  }
  function search(address) {
    if (!map) return;
    var local = new BMap.LocalSearch(map, {
      renderOptions: {
        map: map,
        autoViewport: true,
        selectFirstResult: false
      },
      pageCapacity: 30,
      onMarkersSet: function(results) {
        results.forEach(function(result) {
          var newMarker = new BMap.Marker(result.marker.getPosition());
          map.addOverlay(newMarker);
          map.removeOverlay(result.marker);
          newMarker.addEventListener('click', function(e) {
            setAddress(newMarker.getPosition());
            marker.setPosition(newMarker.getPosition());
            marker.setTop(true);
            map.openInfoWindow(infoWindow, marker.getPosition());
          });
        })
      }
    });
    local.search(address);
  }
</script>