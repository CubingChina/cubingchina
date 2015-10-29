<div class="row clearfix">
  <div class="col-lg-3 col-md-4 col-sm-6">
    <?php echo CHtml::dropdownList('event', '', array_intersect_key(Events::getNormalTranslatedEvents(), array_flip(array_map(function($result) {
      return $result->eventId;
    }, $results))), array(
      'class'=>'form-control',
    )); ?>
  </div>
</div>
<?php
Yii::app()->clientScript->registerScript('dropdownEvents',
<<<EOT
  $('select[name="event"]').on('change', function() {
    location.href = '#' + $(this).val();
  });
EOT
);
