<div class="content-wrapper col-md-10 col-sm-9">
  <div class="panel-group" id="accordion">
    <?php foreach ($model->search()->getData() as $faq): ?>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h4 class="panel-title">
          <a data-toggle="collapse" data-parent="#accordion" href="#faq-<?php echo $faq->id; ?>" class="collapsed">
          <?php echo $faq->getAttributeValue('title'); ?>
          </a>
        </h4>
      </div><!--//pane-heading-->
      <div id="faq-<?php echo $faq->id; ?>" class="panel-collapse collapse">
        <div class="panel-body">
          <?php echo $faq->getAttributeValue('content'); ?>
        </div><!--//panel-body-->
      </div><!--//panel-colapse-->
    </div><!--//panel-->
    <?php endforeach; ?>
  </div>
</div>
<aside class="page-sidebar col-md-2 col-sm-3 affix-top">
  <section class="widget">
    <?php $this->widget('zii.widgets.CMenu', array(
      'htmlOptions'=>array(
        'class'=>'nav',
      ),
      'items'=>$categories,
    )); ?>
  </section><!--//widget-->
</aside>
<?php

Yii::app()->clientScript->registerScript('faq',
<<<EOT
  if (location.hash) {
    var faq = $('[href="#' + location.hash.replace(/^#/, '') + '"]');
    if (faq) {
      faq.click();
      $('html, body').animate({
        scrollTop: faq.offset().top
      });
    }
  }
EOT
);
