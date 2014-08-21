<div class="col-lg-12 competition-<?php echo strtolower($competition->type); ?>">
  <?php $columns = $competition->getEventsColumns(); ?>
  <?php $this->widget('RepeatHeaderGridView', array(
    'dataProvider'=>$model->search($columns),
    // 'filter'=>false,
    // 'enableSorting'=>false,
    'front'=>true,
    'columns'=>$columns,
  )); ?>
</div>
<?php
Yii::app()->clientScript->registerScript('competitors',
<<<EOT
  if (!('ontouchstart' in window)) {
    (function() {
      var table = $('.table-responsive table');
      var tableParent = table.parent();
      var scroll = $('<div>');
      var scrollParent = $('<div class="table-responsive">');
      var win = $(window);
      scroll.css({
        height: 1,
        width: table.width()
      });
      scrollParent.append(scroll).insertAfter(tableParent).css({
        position: 'fixed',
        width: tableParent.width(),
        bottom: 0
      }).on('scroll', function() {
        tableParent[0].scrollLeft = this.scrollLeft;
      });
      tableParent.on('scroll', function() {
        scrollParent[0].scrollLeft = this.scrollLeft;
      });
      win.on('scroll', function() {
        if (win.height() + win.scrollTop() > tableParent.offset().top + tableParent.height()) {
          scrollParent.hide();
        } else {
          scrollParent.show();
        }
      }).on('resize', function() {
        scrollParent.width(tableParent.width());
      });
    })();
  }
EOT
);