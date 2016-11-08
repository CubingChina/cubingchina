<div id="map" style="height:100%"></div>
<script>
  var isTouch = 'ontouchstart' in window;
  var src = 'https://api.map.baidu.com/api?ak=5MnAdRxax54stQ29hR91Fxl1&v=2.0&s=1';
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
  map.centerAndZoom(point, params.zoom || 12);
  map.addControl(new BMap[controll]());
  map.enableScrollWheelZoom && map.enableScrollWheelZoom();
  map.addOverlay(marker);
  marker.addEventListener("click", function(e){
    map.openInfoWindow(infoWindow, point);
  });
  map.openInfoWindow(infoWindow, point);
  if (self != top && parent.jQuery) {
    var $ = parent.jQuery, iframe = $(window.frameElement), $parent = $(parent);
    $parent.on('resize', function() {
      if (iframe.width() > iframe.parent().width()) {
        iframe.attr('data-orig-width', iframe.width());
        iframe.width('100%');
      }
      if (iframe.data('orig-width') < iframe.parent().width()) {
        iframe.width(iframe.data('orig-width'));
      }
    });
    setTimeout(function() {
      $parent.trigger('resize');
    }, 0)
  }
  //标记
</script>
