<article class="events-item page-row row has-divider clearfix">
  <div class="details col-xs-12">
    <h3 class="title"><?php echo CHtml::encode($data->getAttributeValue('title')); ?></h3>
    <p class="meta">
      <span class="date"><i class="fa fa-calendar"></i> <?php echo date('Y-m-d', $data->date); ?></span>
      <span class="time"><i class="fa fa-clock-o"></i> <?php echo date('H:i', $data->date); ?></span>
      <span class="author"><i class="fa fa-user"></i> <?php echo $data->user->getAttributeValue('name'); ?></span>
    </p>  
    <div class="desc"><?php echo $data->getAttributeValue('content'); ?></div>
  </div><!--//details-->
</article>