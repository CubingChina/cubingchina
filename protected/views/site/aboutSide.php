<aside class="page-sidebar col-md-2 col-sm-3 affix-top hidden-xs">
  <section class="widget">
    <?php $this->widget('zii.widgets.CMenu', array(
      'htmlOptions'=>array(
        'class'=>'nav',
      ),
      'items'=>array(
        array(
          'url'=>array('/site/page', 'view'=>'about'),
          'label'=>Yii::t('common', 'About'),
        ),
        array(
          'url'=>array('/site/page', 'view'=>'contact'),
          'label'=>Yii::t('common', 'Contact'),
        ),
        array(
          'url'=>array('/site/page', 'view'=>'links'),
          'label'=>Yii::t('common', 'Links'),
        ),
      ),
    )); ?>
  </section><!--//widget-->
</aside>