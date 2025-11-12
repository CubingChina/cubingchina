<div class="col-lg-12 competition-<?php echo strtolower($competition->type); ?>" id="scan-container" data-competition-id="<?php echo $competition->id; ?>" v-cloak>
  <?php echo CHtml::link(Yii::t('Competition', 'Signin List'), ['/board/registration/signin', 'Registration'=>['competition_id'=>$competition->id]], ['class'=>'btn btn-theme']); ?>
  <div>
    已签到：<?php echo Registration::model()->countByAttributes(['competition_id'=>$competition->id, 'signed_in'=>Registration::YES, 'status'=>Registration::STATUS_ACCEPTED]) ?><br>
    未签到：<?php echo Registration::model()->countByAttributes(['competition_id'=>$competition->id, 'signed_in'=>Registration::NO, 'status'=>Registration::STATUS_ACCEPTED]) ?>
  </div>
  <div class="text-center" v-if="mode == 'wx'">
    <button type="button" :disabled="loading" class="btn btn-theme btn-lg" @click="scan"><?php echo Yii::t('common', 'Scan'); ?></button>
  </div>
  <div class="text-center" v-if="loading">
    Loading...<br>
    <?php echo CHtml::image('https://i.cubing.com/animatedcube.gif'); ?>
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
  <div class="dl-horizontal" v-if="registration.id">
    <div><strong><?php echo Yii::t('common', 'Type'); ?></strong>：{{registration.title}}</div>
    <div v-if="registration.number"><strong>No.</strong>：{{registration.number}}</div>
    <div><strong><?php echo Yii::t('Registration', 'Name'); ?></strong>：{{registration.user.name}}</div>
    <div v-if="registration.passport"><strong><?php echo Yii::t('Registration', 'Identity Number'); ?></strong>：{{registration.passport}}</div>
    <div v-if="registration.user.gender"><strong><?php echo Yii::t('common', 'Gender'); ?></strong>：{{registration.user.gender}}</div>
    <div v-if="registration.user.country"><strong><?php echo Yii::t('common', 'Regions'); ?></strong>：{{registration.user.country}}</div>
    <div v-if="registration.user.birthday"><strong><?php echo Yii::t('common', 'Birthday'); ?></strong>：{{registration.user.birthday}}</div>
    <div><strong><?php echo Yii::t('common', 'Fee'); ?></strong>：{{registration.fee}}
      (<span v-if="registration.paid">
        <?php echo Yii::t('common', 'Paid'); ?>
      </span>
      <span v-else>
        <?php echo Yii::t('common', 'Unpaid'); ?>
      </span>)
    </div>
    <div><strong><?php echo Yii::t('Competition', 'Status'); ?></strong>：<span :style="{color: registration.signed_in ? 'black' : 'red'}">{{registration.signed_in ? "<?php echo Yii::t('common', 'Has signed in'); ?>" : "<?php echo Yii::t('common', 'Hasn\'t signed in'); ?>"}}</span></div>
    <div v-if="registration.signed_in"><strong><?php echo Yii::t('common', 'Signed in Date'); ?></strong>：{{registration.signed_date}}</div>
    <div v-if="registration.events"><strong><?php echo Yii::t('common', 'Event'); ?></strong>：{{registration.events}}</div>
  </div>
  <div v-if="registration.t_shirt_size">
    <div><strong><?php echo Yii::t('Registration', 'T-shirt Size'); ?></strong>：{{registration.t_shirt_size}}</div>
  </div>
  <div v-if="registration.staff_type">
    <div><strong><?php echo Yii::t('Registration', 'Staff Type'); ?></strong>：{{registration.staff_type}}</div>
  </div>
  <div v-if="registration.has_entourage">
    <div><strong><?php echo Yii::t('Registration', 'Guest Name'); ?></strong>：{{registration.entourage_name}}</div>
    <div><strong><?php echo Yii::t('Registration', 'Type of Identity'); ?></strong>：{{registration.entourage_passport_type_text}}</div>
    <div><strong><?php echo Yii::t('Registration', 'Identity Number'); ?></strong>：{{registration.entourage_passport_number}}</div>
  </div>
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
