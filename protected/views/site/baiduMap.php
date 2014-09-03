<div id="map" style="height:100%"></div>
<script type="text/javascript" src="http://api.map.baidu.com/api?ak=5MnAdRxax54stQ29hR91Fxl1&v=2.0"></script>
<script type="text/javascript">
  var params = <?php echo json_encode($_GET); ?>;
  var map = new BMap.Map('map');
  var point = new BMap.Point(params.longitude || 116.331398, params.latitude || 39.897445);
  var marker = new BMap.Marker(point);
  var infoWindow = new BMap.InfoWindow(params.address || '', {
    title: params.title || '',
    enableMessage : true
  });
  //地图
  map.centerAndZoom(point, 12);
  map.addControl(new BMap.NavigationControl());
  map.enableScrollWheelZoom();
  map.addOverlay(marker);
  marker.addEventListener("click", function(e){
    map.openInfoWindow(infoWindow, point);
  });
  map.openInfoWindow(infoWindow, point);
  //标记
</script>
