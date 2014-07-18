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