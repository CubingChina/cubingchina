<div class="col-lg-12 results-cert">
  <div class="text-center">
    <p>
      <?php if ($this->user && $user->id == $this->user->id): ?>
      <?php echo CHtml::link(Yii::t('common', 'Download Result Certificate'), $cert->getImageUrl('results'), [
        'download'=>Yii::t('common', 'Result Certificate') . '.jpg',
        'target'=>'_blank',
        'class'=>'btn btn-theme',
      ]); ?>
      <?php if ($cert->has_participations): ?>
      <?php echo CHtml::link(Yii::t('common', 'Download Participation Certificate'), $cert->getImageUrl('participations'), [
        'download'=>Yii::t('common', 'Participation Certificate') . '.jpg',
        'target'=>'_blank',
        'class'=>'btn btn-success',
      ]); ?>
      <?php endif; ?>
      <?php endif; ?>
      <?php echo CHtml::link(Yii::t('common', 'My Certificates'), ['/user/cert'], [
        'class'=>'btn btn-info',
      ]); ?>
    </p>
    <p>
      <?php echo CHtml::image($cert->getImageUrl('results')); ?>
    </p>
    <?php if ($cert->has_participations && $this->user && $user->id == $this->user->id): ?>
    <p>
      <?php echo CHtml::image($cert->getImageUrl('participations')); ?>
    </p>
    <?php endif; ?>
    <p>
      <?php if ($this->user && $user->id == $this->user->id): ?>
      <?php echo CHtml::link(Yii::t('common', 'Download Result Certificate'), $cert->getImageUrl('results'), [
        'download'=>Yii::t('common', 'Result Certificate') . '.jpg',
        'target'=>'_blank',
        'class'=>'btn btn-theme',
      ]); ?>
      <?php if ($cert->has_participations): ?>
      <?php echo CHtml::link(Yii::t('common', 'Download Participation Certificate'), $cert->getImageUrl('participations'), [
        'download'=>Yii::t('common', 'Participation Certificate') . '.jpg',
        'target'=>'_blank',
        'class'=>'btn btn-success',
      ]); ?>
      <?php endif; ?>
      <?php endif; ?>
      <?php echo CHtml::link(Yii::t('common', 'My Certificates'), ['/user/cert'], [
        'class'=>'btn btn-info',
      ]); ?>
    </p>
  </div>
</div>
<?php
$data = json_encode([
  'title'=>$cert->getShareTitle(),
  'desc'=>$cert->getShareDesc(),
  'imgUrl'=>$cert->getShareIcon(),
]);
Yii::app()->clientScript->registerScript('cert',
<<<EOT
  var data = {$data};
  data.link = location.href;
  wx.config({$config});
  wx.ready(function() {
    wx.onMenuShareTimeline(data);
    wx.onMenuShareAppMessage(data);
    wx.onMenuShareQQ(data);
    wx.onMenuShareWeibo(data);
    wx.onMenuShareQZone(data);
  });
  if (navigator.userAgent.match(/MicroMessenger/i) && !navigator.userAgent.match(/WindowsWechat/i)) {
    $('a[download]').on('click', function(e) {
      location.href = this.href;
    });
  }
EOT
);


