<div class="col-lg-8 col-lg-offset-2 col-xs-12">
  <div class="alert alert-warning">
    <p><?php echo Yii::t('common', 'This legacy version of Cubing China is now read-only. Please visit the new site at {url}.', [
      '{url}'=>ArchiveMode::newSiteLink(ArchiveMode::newSiteUrl()),
    ]); ?></p>
    <p><?php echo Yii::t('common', 'Account registration and password reset are closed on this legacy site. Please use the new Cubing China.'); ?></p>
    <p>
      <?php echo ArchiveMode::newSiteLink(Yii::t('common', 'Go to the new Cubing China'), [
        'class'=>'btn btn-primary',
      ]); ?>
      <?php echo CHtml::link(Yii::t('common', 'Login'), ['/site/login'], [
        'class'=>'btn btn-default',
      ]); ?>
    </p>
  </div>
</div>
