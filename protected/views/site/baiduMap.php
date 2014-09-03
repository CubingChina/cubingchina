<div id="map" style="height:100%"></div>
<script>
  var isTouch = 'ontouchstart' in window;
  var src = 'http://api.map.baidu.com/api?ak=5MnAdRxax54stQ29hR91Fxl1&v=2.0';
  if (isTouch) {
    src += '&type=quick';
  }
  document.write(unescape('%3Cscript%20type%3D%22text/javascript%22%20src%3D%22' + src + '%22%3E%3C/script%3E'));
</script>
<script type="text/javascript">
  var params = <?php echo json_encode($_GET); ?>;
  var map = new BMap.Map('map');
  var point = new BMap.Point(params.longitude || 116.331398, params.latitude || 39.897445);
  var marker = new BMap.Marker(point);
  var infoWindow = new BMap.InfoWindow(params.address || '', {
    title: params.title || '',
    enableMessage : true
  });
  var controll = isTouch ? 'ZoomControl' : 'NavigationControl';
  //地图
  map.centerAndZoom(point, 12);
  map.addControl(new BMap[controll]());
  map.enableScrollWheelZoom && map.enableScrollWheelZoom();
  map.addOverlay(marker);
  marker.addEventListener("click", function(e){
    map.openInfoWindow(infoWindow, point);
  });
  map.openInfoWindow(infoWindow, point);
  var $ = parent.$, maps;
  $ && (maps = $('.baidumap')) && $(parent).on('resize', function() {
    maps.each(function() {
      var map = $(this)
      if (map.width() > map.parent().width()) {
        map.data('orig-width', map.width());
        map.width('100%');
      }
      if (map.data('orig-width') < map.parent().width()) {
        map.width(map.data('orig-width'));
      }
    });
  }) && $(parent).trigger('resize');
  //标记
</script>
