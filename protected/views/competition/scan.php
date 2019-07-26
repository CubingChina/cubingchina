<div class="col-lg-12 competition-<?php echo strtolower($competition->type); ?>" id="scan-container" data-competition-id="<?php echo $competition->id; ?>" v-cloak>
  <?php echo CHtml::link(Yii::t('Competition', 'Signin List'), ['/board/registration/signin', 'Registration'=>['competition_id'=>$competition->id]], ['class'=>'btn btn-theme']); ?>
  <div>
    已签到：<?php echo Registration::model()->countByAttributes(['competition_id'=>$competition->id, 'signed_in'=>Registration::YES]) ?><br>
    未签到：<?php echo Registration::model()->countByAttributes(['competition_id'=>$competition->id, 'signed_in'=>Registration::NO]) ?>
  </div>
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
    <dt><?php echo Yii::t('common', 'Type'); ?></dt>
    <dd>{{registration.title}}</dd>
    <dt v-if="registration.number">No.</dt>
    <dd v-if="registration.number">{{registration.number}}</dd>
    <dt><?php echo Yii::t('Registration', 'Name'); ?></dt>
    <dd>{{registration.user.name}}</dd>
    <dt v-if="registration.passport"><?php echo Yii::t('Registration', 'Identity Number'); ?></dt>
    <dd v-if="registration.passport">{{registration.passport}}</dd>
    <dt><?php echo Yii::t('common', 'Fee'); ?></dt>
    <dd>{{registration.fee}}
      (<span v-if="registration.paid">
        <?php echo Yii::t('common', 'Paid'); ?>
      </span>
      <span v-else>
        <?php echo Yii::t('common', 'Unpaid'); ?>
      </span>)
    </dd>
    <dt><?php echo Yii::t('Competition', 'Status'); ?></dt>
    <dd>{{registration.signed_in ? "<?php echo Yii::t('common', 'Has signed in'); ?>" : "<?php echo Yii::t('common', 'Hasn\'t signed in'); ?>"}}</dd>
    <dt v-if="registration.signed_in"><?php echo Yii::t('common', 'Signed in Date'); ?></dt>
    <dd v-if="registration.signed_in">{{registration.signed_date}}
    </dd>
  </dl>
  <dl class="dl-horizontal" v-if="registration.t_shirt_size">
    <dt><?php echo Yii::t('Registration', 'T-shirt Size'); ?></dt>
    <dd>{{registration.t_shirt_size}}</dd>
  </dl>
  <dl class="dl-horizontal" v-if="registration.staff_type">
    <dt><?php echo Yii::t('Registration', 'Staff Type'); ?></dt>
    <dd>{{registration.staff_type}}</dd>
  </dl>
  <dl class="dl-horizontal" v-if="registration.has_entourage">
    <dt><?php echo Yii::t('Registration', 'Guest Name'); ?></dt>
    <dd>{{registration.entourage_name}}</dd>
    <dt><?php echo Yii::t('Registration', 'Type of Identity'); ?></dt>
    <dd>{{registration.entourage_passport_type_text}}</dd>
    <dt><?php echo Yii::t('Registration', 'Identity Number'); ?></dt>
    <dd>{{registration.entourage_passport_number}}</dd>
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
