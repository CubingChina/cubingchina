<aside class="page-sidebar col-md-2 col-sm-3 affix-top hidden-xs">
  <section class="widget">
    <?php $this->widget('zii.widgets.CMenu', array(
      'htmlOptions'=>array(
        'class'=>'nav',
      ),
      'items'=>array(
        array(
          'url'=>array('/user/profile'),
          'label'=>Yii::t('common', 'Profile'),
        ),
        // array(
        //   'url'=>array('/user/password'),
        //   'label'=>Yii::t('common', 'Change Password'),
        // ),
        array(
          'url'=>array('/user/competitions'),
          'label'=>Yii::t('common', 'My Competitions'),
        ),
      ),
    )); ?>
  </section><!--//widget-->
</aside>