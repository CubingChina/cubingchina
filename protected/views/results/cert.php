<div class="col-lg-12 results-cert">
  <div class="text-center">
    <p>
      <?php echo CHtml::link(Yii::t('common', 'Download Result Certificate'), $cert->getImageUrl('results'), [
        'download'=>Yii::t('common', 'Result Certificate') . '.jpg',
        'target'=>'_blank',
        'class'=>'btn btn-theme',
      ]); ?>
      <?php if ($cert->has_participations && $this->user && $user->id == $this->user->id): ?>
      <?php echo CHtml::link(Yii::t('common', 'Download Participation Certificate'), $cert->getImageUrl('participations'), [
        'download'=>Yii::t('common', 'Participation Certificate') . '.jpg',
        'target'=>'_blank',
        'class'=>'btn btn-success',
      ]); ?>
      <?php endif; ?>
      <?php if ($this->user && $this->user->hasCerts): ?>
      <?php echo CHtml::link(Yii::t('common', 'My Certificates'), ['/user/cert'], [
        'class'=>'btn btn-info',
      ]); ?>
      <?php endif; ?>
    </p>
    <p>
      <?php echo CHtml::image($cert->getImageUrl('results')); ?>
    </p>
    <p>
      <?php echo CHtml::link(Yii::t('common', 'Download Result Certificate'), $cert->getImageUrl('results'), [
        'download'=>Yii::t('common', 'Result Certificate') . '.jpg',
        'target'=>'_blank',
        'class'=>'btn btn-theme',
      ]); ?>
      <?php if ($cert->has_participations && $this->user && $user->id == $this->user->id): ?>
      <?php echo CHtml::link(Yii::t('common', 'Download Participation Certificate'), $cert->getImageUrl('participations'), [
        'download'=>Yii::t('common', 'Participation Certificate') . '.jpg',
        'target'=>'_blank',
        'class'=>'btn btn-success',
      ]); ?>
      <?php endif; ?>
      <?php if ($this->user && $this->user->hasCerts): ?>
      <?php echo CHtml::link(Yii::t('common', 'My Certificates'), ['/user/cert'], [
        'class'=>'btn btn-info',
      ]); ?>
      <?php endif; ?>
    </p>
  </div>
</div>