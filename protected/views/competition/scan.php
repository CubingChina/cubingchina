<div class="col-lg-12 competition-<?php echo strtolower($competition->type); ?>" id="scan-container" v-cloak>
  <?php echo CHtml::link(Yii::t('Competition', 'Signin List'), ['/board/registration/signin', 'Registration'=>['competition_id'=>$competition->id]], ['class'=>'btn btn-theme']); ?>
  <div class="text-center" v-if="mode == 'wx'">
    <button type="button" :disabled="loading" class="btn btn-theme btn-lg" @click="scan"><?php echo Yii::t('common', 'Scan'); ?></button>
  </div>
  <div class="text-center" v-if="loading">
    Loading...<br>
    <?php echo CHtml::image('https://i.cubingchina.com/animatedcube.gif'); ?>
  </div>
  <div class="pc-scan"
    v-if="mode = 'pc'"
  >
    <div v-if="scanning">请使用扫码枪扫码。</div>
    <div class="scanning-wrapper" :class="{'scanning': scanning}"></div>
    <input type="text" class="form-control"
      v-model="url"
      v-el:url-input
      @change="check"
      @focus="startScan"
      @blur="endScan"
    >
  </div>
  <dl class="dl-horizontal" v-if="registration.id">
    <dt>No.</dt>
    <dd>{{registration.number}}</dd>
    <dt><?php echo Yii::t('Registration', 'Name'); ?></dt>
    <dd>{{registration.user.name}}</dd>
    <dt v-if="registration.passport"><?php echo Yii::t('Registration', 'Identity Number'); ?></dt>
    <dd v-if="registration.passport">{{registration.passport}}</dd>
    <dt><?php echo Yii::t('Competition', 'Entry Fee'); ?></dt>
    <dd>{{registration.fee}}
      (<span v-if="registration.paid">
        <?php echo Yii::t('common', 'Paid'); ?>
      </span>
      <span v-else>
        <?php echo Yii::t('common', 'Unpaid'); ?>
      </span>)
    </dd>
    <dt v-if="registration.signed_in"><?php echo Yii::t('common', 'Signed in Date'); ?></dt>
    <dd v-if="registration.signed_in">{{registration.signed_date}}
    </dd>
  </dl>
  <dl class="dl-horizontal" v-if="registration.has_entourage">
    <dt v-if="registration.has_entourage"><?php echo Yii::t('Registration', 'Guest Name'); ?></dt>
    <dd v-if="registration.has_entourage">{{registration.entourage_name}}</dd>
    <dt v-if="registration.has_entourage"><?php echo Yii::t('Registration', 'Type of Identity'); ?></dt>
    <dd v-if="registration.has_entourage">{{registration.entourage_passport_type_text}}</dd>
    <dt v-if="registration.has_entourage"><?php echo Yii::t('Registration', 'Identity Number'); ?></dt>
    <dd v-if="registration.has_entourage">{{registration.entourage_passport_number}}</dd>
  </dl>
  <dl class="dl-horizontal" v-if="registration.id">
    <dt v-if="!registration.paid"></dt>
    <dd v-if="!registration.paid">
      <button class="btn btn-info" type="button" @click="doAction('pay')">
        <?php echo Yii::t('common', 'Pay'); ?>
      </button>
    </dd>
    <dt v-if="!registration.signed_in"></dt>
    <dd v-if="!registration.signed_in">
      <button class="btn btn-success" type="button" @click="doAction('signin')">
        <?php echo Yii::t('common', 'Sign in'); ?>
      </button>
    </dd>
  </dl>
</div>
<?php
Yii::app()->clientScript->registerScript('scan',
<<<EOT
  wx.config({$config})
EOT
);

