<aside class="page-sidebar col-md-2 col-sm-3 affix-top">
  <?php $items = [
    [
      'url'=>['/user/profile'],
      'label'=>Yii::t('common', 'Profile'),
      'active'=>in_array($this->action->id, ['profile', 'edit']),
    ],
    [
      'url'=>['/user/preferredEvents'],
      'label'=>Yii::t('common', 'Preferred Events'),
    ],
    [
      'url'=>['/user/bind'],
      'label'=>Yii::t('common', 'Bind Account'),
    ],
    [
      'url'=>['/user/competitions'],
      'label'=>Yii::t('common', 'My Registration'),
    ],
    [
      'url'=>['/user/competitionHistory'],
      'label'=>Yii::t('common', 'Competition History'),
      'visible'=>$this->user->wcaid != '',
    ],
    [
      'url'=>['/user/cert'],
      'label'=>Yii::t('common', 'My Certificates'),
      'visible'=>$this->user->hasCerts,
    ],
  ]; ?>
  <section class="widget hidden-xs">
    <?php $this->widget('zii.widgets.CMenu', array(
      'htmlOptions'=>array(
        'class'=>'nav',
      ),
      'items'=>$items,
    )); ?>
  </section><!--//widget-->
  <?php $this->widget('zii.widgets.CMenu', array(
    'htmlOptions'=>array(
      'class'=>'nav nav-tabs visible-xs',
    ),
    'items'=>$items,
  )); ?>
</aside>
