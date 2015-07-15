
<?php
Yii::app()->clientScript->registerScript('pay',
<<<EOT
  $.ajax({
    url: '/pay/url',
    data: {
      id: '{$model->id}',
      is_mobile: 0 + ('ontouchstart' in window)
    },
    dataType: 'json',
    success: function(data) {
      location.href = data.data.url;
    }
  });
EOT
);