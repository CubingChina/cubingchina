<aside class="page-sidebar col-md-2 col-sm-3 affix-top hidden-xs">
  <section class="widget">
    <?php $this->widget('zii.widgets.CMenu', array(
      'htmlOptions'=>array(
        'class'=>'nav',
      ),
      'items'=>array(
        array(
          'url'=>$step > 1 ? array('/site/register', 'step'=>1) : 'javascript:;',
          'label'=>Yii::t('common', 'Enter WCA ID'),
          'active'=>$step == 1,
        ),
        array(
          'url'=>'javascript:;',
          'label'=>Yii::t('common', 'Fill More Information'),
          'active'=>$step == 2,
        ),
        array(
          'url'=>'javascript:;',
          'label'=>Yii::t('common', 'Registration Done'),
          'active'=>$step == 3,
        ),
      ),
    )); ?>
  </section><!--//widget-->
</aside>