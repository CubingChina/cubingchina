<?php $this->widget('ListView', array(
  'itemView'=>'news',
  'dataProvider'=>$news->search(),
  'htmlOptions'=>array(
    'class'=>'news-wrapper col-md-9 col-sm-8',
  ),
  'front'=>true,
  'template'=>"{items}\n{pager}",
  'emptyText'=>'',
)); ?>
<aside class="page-sidebar col-md-3 col-sm-4">
  <section class="widget has-divider">
    <div class="panel panel-theme weibo-widget">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-weibo"></i> 微博</h3>
      </div>
      <div class="panel-body">
        <iframe width="100%" height="380" class="share_self"  frameborder="0" scrolling="no" src="http://widget.weibo.com/weiboshow/index.php?language=&width=0&height=420&fansRow=2&ptype=1&speed=0&skin=5&isTitle=0&noborder=0&isWeibo=1&isFans=0&uid=5118940638&verifier=2f59659a&dpc=1"></iframe>
      </div>
    </div>
  </section>
  <?php if (count($upcomingCompetitions) > 0): ?>
  <section class="widget has-divider">
    <h3 class="title"><?php echo Yii::t('common', 'Upcoming Competitions'); ?></h3>
    <?php foreach ($upcomingCompetitions as $competition): ?>
    <article class="events-item row page-row">
      <div class="date-label-wrapper col-md-3 col-sm-4 col-xs-2">
        <p class="date-label">
          <span class="month"><?php echo Yii::t('date', strtoupper(date('M', $competition->date))); ?></span>
          <span class="date-number"><?php echo date('d', $competition->date); ?></span>
        </p>
      </div><!--//date-label-wrapper-->
      <div class="details col-md-9 col-sm-8 col-xs-10">
        <h5 class="title"><?php echo CHtml::link($competition->getAttributeValue('name'), $competition->getUrl('detail')); ?></h5>
        <p class="time text-muted"><?php echo $competition->getAttributeValue('venue'); ?></p>
      </div><!--//details-->
    </article>
    <?php endforeach; ?>
  </section>
  <?php endif; ?>
</aside>