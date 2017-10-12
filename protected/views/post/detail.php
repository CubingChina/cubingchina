<div class="news-wrapper col-lg-12">
  <article class="events-item clearfix">
    <div class="panel panel-<?php if ($news->weight > 0) echo 'theme'; else echo 'info'; ?>">
      <div class="panel-heading">
        <h3 class="panel-title"><?php echo CHtml::link(CHtml::encode($news->getAttributeValue('title')), $news->url); ?></h3>
      </div>
      <div class="panel-body">
        <p class="meta">
          <span class="date"><i class="fa fa-calendar"></i> <?php echo date('Y-m-d', $news->date); ?></span>
          <span class="time"><i class="fa fa-clock-o"></i> <?php echo date('H:i', $news->date); ?></span>
          <span class="author"><i class="fa fa-user"></i> <?php echo $news->user->getAttributeValue('name', true); ?></span>
        </p>
        <div class="desc"><?php echo $news->getAttributeValue('content'); ?></div>
      </div>
    </div>
  </article>
</div>
