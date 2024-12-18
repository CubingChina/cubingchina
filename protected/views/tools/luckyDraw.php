<div class="container">
  <div class="col-lg-8 col-lg-offset-2">
    <div class="pull-right">
      <a href="/" class="pull-right"><?php echo Yii::t('common', 'Cubing China'); ?></a>
      <div class="text-right">
        <button id="enter" class="btn btn-xs btn-success none">Enter</button>
        <button id="settings" class="btn btn-xs btn-primary" data-toggle="modal" data-target="#drawModal"><?php echo Yii::t('common', 'Settings'); ?></button>
      </div>
    </div>
    <h1 class="title">
      <img src="" alt="" id="logo">
      <span id="title"><?php echo $this->pageTitle; ?></span>
    </h1>
    <div class="clearfix"></div>
  </div>
</div>
<div class="modal fade" id="drawModal" tabindex="-1" role="dialog" aria-labelledby="drawModalTitle" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title" id="drawModalTitle"><?php echo Yii::t('common', 'Settings'); ?></h4>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label for="luckyDrawTitle"><?php echo Yii::t('common', 'Title'); ?></label>
          <input type="text" class="form-control" id="luckyDrawTitle" value="<?php echo $this->pageTitle; ?>">
        </div>
        <div class="form-group">
          <label for="luckyDrawLogo"><?php echo Yii::t('common', 'Logo Url'); ?></label>
          <input type="text" class="form-control" id="luckyDrawLogo" value="">
        </div>
        <div class="form-group">
          <label for="luckyDrawNames"><?php echo Yii::t('common', 'Names (One name per line)'); ?></label>
          <?php if (Yii::app()->user->checkRole(User::ROLE_ORGANIZER)): ?>
          <?php echo CHtml::dropDownList('luckyDrawCompetition', '', Competition::getRegistrationCompetitions(), array(
            'prompt'=>Yii::t('common', 'Choose a competition'),
            'class'=>'form-control',
            'data-url'=>$this->createUrl('/tools/competitors'),
          ));?>
          <?php endif; ?>
          <textarea name="names" id="luckyDrawNames" class="form-control" rows="10"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo Yii::t('common', 'Close'); ?></button>
        <button type="button" class="btn btn-primary" id="save"><?php echo Yii::t('common', 'Save'); ?></button>
      </div>
    </div>
  </div>
</div>

<div id="menu">
  <div id="lotteryBar" class="none">
    <div class="fixed-bar">
      <button id="lottery" class="fixed-btn">Start</button>
      <button id="reset" class="fixed-btn"><?php echo Yii::t('common', 'Reset'); ?></button>
    </div>
  </div>
</div>
<div id="luckydraw-container"></div>
<div id="drawn-container">
  <div class="title none">
    已抽出名单
  </div>
  <div class="list"></div>
</div>
<?php
Yii::app()->clientScript->registerScriptFile('/f/js/store+json2.min.js');
Yii::app()->clientScript->registerScriptFile('/f/js/three.min.js');
Yii::app()->clientScript->registerScriptFile('/f/js/tween.min.js');
Yii::app()->clientScript->registerScriptFile('/f/js/TrackballControls.js');
Yii::app()->clientScript->registerScriptFile('/f/js/CSS3DRenderer.js');
Yii::app()->clientScript->registerScriptFile('/f/js/luckyDraw.js?20241218');
Yii::app()->clientScript->registerCssFile('/f/css/animate.min.css');
Yii::app()->clientScript->registerCssFile('/f/css/luckyDraw.css?20240616');
