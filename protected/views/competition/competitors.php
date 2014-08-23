<div class="col-lg-12 competition-<?php echo strtolower($competition->type); ?>">
  <?php $columns = $competition->getEventsColumns(); ?>
  <?php $this->widget('RepeatHeaderGridView', array(
    'dataProvider'=>$model->search($columns),
    // 'filter'=>false,
    // 'enableSorting'=>false,
    'front'=>true,
    'footerOnTop'=>true,
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
        position: 'fixed'
      }).on('scroll', function() {
        tableParent[0].scrollLeft = this.scrollLeft;
      });
      tableParent.on('scroll', function() {
        scrollParent[0].scrollLeft = this.scrollLeft;
      });
      win.on('scroll', function() {
        if (table.width() <= tableParent.width() || win.height() + win.scrollTop() > tableParent.offset().top + tableParent.height()) {
          scrollParent.hide();
        } else {
          scrollParent.show().scrollLeft(tableParent.scrollLeft());
        }
      }).on('resize', function() {
        scrollParent.css({
          width: tableParent.width(),
          bottom: -parseInt(tableParent.css('margin-bottom'))
        });
        win.trigger('scroll');
      }).trigger('scroll').trigger('resize');
    })();
  }
EOT
);