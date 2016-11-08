<div class="col-lg-12 competition-<?php echo strtolower($competition->type); ?>" id="scan-container" v-cloak>
  <div class="text-center">
    <button type="button" :disabled="loading" class="btn btn-theme btn-lg" @click="scan"><?php echo Yii::t('common', 'Scan'); ?></button>
  </div>
  <div class="text-center" v-if="loading">
    <?php echo CHtml::image('https://i.cubingchina.com/animatedcube.gif'); ?>
  </div>
  <dl class="dl-horizontal" v-if="registration.id">
    <dt>No.</dt>
    <dd>{{registration.number}}</dd>
    <dt><?php echo Yii::t('common', 'Name'); ?></dt>
    <dd>{{registration.user.name}}</dd>
    <dt><?php echo Yii::t('common', 'Passport'); ?></dt>
    <dd>{{registration.passport}}</dd>
    <dt><?php echo Yii::t('Competition', 'Entry Fee'); ?></dt>
    <dd>{{registration.fee}}
      (<span v-if="registration.paid">
        <?php echo Yii::t('common', 'Paid'); ?>
      </span>
      <span v-else>
        <?php echo Yii::t('common', 'Unpaid'); ?>
      </span>)
    </dd>
    <dt v-if="!registration.paid"></dt>
    <dd v-if="!registration.paid">
      <button class="btn btn-info" type="button" @click="do('pay')">
        <?php echo Yii::t('common', 'Pay'); ?>
      </button>
    </dd>
    <dt v-if="!registration.signed_in"></dt>
    <dd v-if="!registration.signed_in">
      <button class="btn btn-success" type="button" @click="do('signin')">
        <?php echo Yii::t('common', 'Sign in'); ?>
      </button>
    </dd>
    <dt v-if="registration.signed_in"><?php echo Yii::t('common', 'Signed in Date'); ?></dt>
    <dd v-if="registration.signed_in">{{registration.signed_date}}
    </dd>
  </dl>
</div>
<?php
Yii::app()->clientScript->registerScript('scan',
<<<EOT
  wx.config({$config})
EOT
);

