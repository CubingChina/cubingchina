<?php $this->renderPartial('side', $_data_); ?>
<div class="content-wrapper col-md-10 col-sm-9">
  <?php if ($this->isInWechat): ?>
    <?php if ($user->wechatUser): ?>
      <div class="text-center">
        <p><?php echo CHtml::image($user->wechatUser->avatar, $user->wechatUser->nickname); ?></p>
        <p><?php echo CHtml::encode($user->wechatUser->nickname); ?></p>
        <?php $this->widget('OneButtonForm', [
          'text'=>Yii::t('User', 'Unbind this account'),
          'data'=>[
            'action'=>'unbind',
          ],
        ]); ?>
      </div>
    <?php elseif ($this->isInWechat): ?>
      <div class="text-center">
        <p><?php echo CHtml::image($sessionWechatUser->avatar, $sessionWechatUser->nickname); ?></p>
        <p><?php echo CHtml::encode($sessionWechatUser->nickname); ?></p>
        <?php $this->widget('OneButtonForm', [
          'text'=>Yii::t('User', 'Bind this account'),
          'data'=>[
            'action'=>'bind',
          ],
        ]); ?>
      </div>
    <?php endif; ?>
  <?php else: ?>
    <p><?php echo Yii::t('User', 'Please open this page in Wechat. You can scan the following QR.'); ?></p>
    <p><?php echo CHtml::image('/qrCode/bind'); ?></p>
  <?php endif; ?>
</div>
