<?php $this->renderPartial('side', $_data_); ?>
<div class="content-wrapper col-md-10 col-sm-9">
  <dl class="dl-horizontal">
    <dt>&nbsp;</dt>
    <dd><?php echo CHtml::link(Yii::t('common', 'Edit'), array('/user/edit'), array('class'=>'btn btn-theme')); ?></dd>
    <dt><?php echo Yii::t('common', 'CubingChina ID'); ?></dt>
    <dd><?php echo $user->id ?: '&nbsp;'; ?></dd>
    <dt><?php echo Yii::t('common', 'Name'); ?></dt>
    <dd><?php echo CHtml::encode($user->name); ?></dd>
    <dt><?php echo Yii::t('common', 'Name in Local Character'); ?></dt>
    <dd><?php echo CHtml::encode($user->name_zh) ?: '&nbsp;'; ?></dd>
    <dt><?php echo Yii::t('common', 'Email'); ?></dt>
    <dd><?php echo $user->email; ?></dd>
    <dt><?php echo Yii::t('common', 'WCA ID'); ?></dt>
    <dd><?php echo $user->getWcaLink($user->wcaid) ?: '&nbsp;'; ?></dd>
    <dt><?php echo Yii::t('common', 'Birthday'); ?></dt>
    <dd><?php echo date('Y-m-d', $user->birthday); ?></dd>
    <dt><?php echo Yii::t('common', 'Gender'); ?></dt>
    <dd><?php echo $user->getGenderText(); ?></dd>
    <dt><?php echo Yii::t('common', 'Mobile Number'); ?></dt>
    <dd><?php echo $user->mobile ?: '&nbsp;'; ?></dd>
    <dt><?php echo Yii::t('common', 'Region'); ?></dt>
    <dd><?php echo $user->getRegionName($user->country); ?></dd>
    <?php if ($user->country_id == 1): ?>
    <dt><?php echo Yii::t('common', 'Province'); ?></dt>
    <dd><?php echo $user->getRegionName($user->province); ?></dd>
    <dt><?php echo Yii::t('common', 'City'); ?></dt>
    <dd><?php echo $user->getRegionName($user->city); ?></dd>
    <?php endif; ?>
    <dt><?php echo Yii::t('common', 'Role'); ?></dt>
    <dd>
      <?php echo $user->getRoleName(); ?>
      <?php if ($user->isUnchecked()): ?>
      <br>
      <a href="<?php echo $this->createUrl('/site/reactivate'); ?>" class="btn btn-xs btn-theme"><?php echo Yii::t('common', 'Activate my account'); ?></a>
      <br>
      <?php echo Yii::t('common', 'If you have got problems in activating your account, please contact the administrator via {email}.', array(
        '{email}'=>CHtml::mailto('<i class="fa fa-envelope"></i> ' . Yii::app()->params->adminEmail, Yii::app()->params->adminEmail),
      )); ?>
      <?php endif; ?>
    </dd>
  </dl>
</div>