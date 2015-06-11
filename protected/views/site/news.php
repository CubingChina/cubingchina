<article class="events-item row clearfix">
  <div class="panel panel-<?php if ($data->weight > 0) echo 'theme'; else echo 'info'; ?>">
    <div class="panel-heading">
      <h3 class="panel-title"><?php echo CHtml::encode($data->getAttributeValue('title')); ?></h3>
    </div>
    <div class="panel-body">
      <p class="meta">
        <span class="date"><i class="fa fa-calendar"></i> <?php echo date('Y-m-d', $data->date); ?></span>
        <span class="time"><i class="fa fa-clock-o"></i> <?php echo date('H:i', $data->date); ?></span>
        <span class="author"><i class="fa fa-user"></i> <?php echo $data->user->getAttributeValue('name', true); ?></span>
      </p>  
      <div class="desc"><?php echo $data->getAttributeValue('content'); ?></div>
    </div>
  </div>
</article>