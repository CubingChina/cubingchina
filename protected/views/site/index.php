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
  <?php if (count($upcomingCompetitions) > 0): ?>
  <section class="widget">
    <h3 class="title"><?php echo Yii::t('common', 'Upcoming Competitions'); ?></h3>
    <?php foreach ($upcomingCompetitions as $competition): ?>
    <article class="events-item row page-row">
      <div class="date-label-wrapper col-md-3 col-sm-4 col-xs-2">
        <p class="date-label">
          <span class="month"><?php echo Yii::t('date', $competition->tba == Competition::YES ? 'TBA' : strtoupper(date('M', $competition->date))); ?></span>
          <span class="date-number"><?php echo $competition->tba == Competition::YES ? '' : date('d', $competition->date); ?></span>
        </p>
      </div>
      <div class="details col-md-9 col-sm-8 col-xs-10">
        <h5 class="title">
          <?php echo $competition->getCompetitionLink(); ?>
        </h5>
        <p class="time text-muted"><?php echo $competition->tba == Competition::YES ? Yii::t('common', 'To be announced') : ($competition->isMultiLocation() ? $competition->getLocationInfo('venue') : $competition->location[0]->getFullAddress()); ?></p>
      </div>
    </article>
    <?php endforeach; ?>
  </section>
  <?php endif; ?>
</aside>