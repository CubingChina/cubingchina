<div class="container">
  <div class="col-lg-8 col-lg-offset-2">
    <div class="pull-right">
      <a href="/" class="pull-right"><?php echo Yii::t('common', 'Cubing China'); ?></a>
      <div class="text-right">
        <button id="reset" class="btn btn-xs btn-danger"><?php echo Yii::t('common', 'Reset'); ?></button>
        <button id="settings" class="btn btn-xs btn-primary" data-toggle="modal" data-target="#drawModal"><?php echo Yii::t('common', 'Settings'); ?></button>
      </div>
    </div>
    <h1 class="title">
      <img src="" alt="" id="logo">
      <span id="title"><?php echo $this->pageTitle; ?></span>
    </h1>
    <div class="clearfix"></div>
    <hr style="margin: 10px 0 0">
    <div class="row">
      <div class="col-sm-2 text-right">
        <button id="draw" class="btn btn-success"><?php echo Yii::t('common', 'Lucky Draw'); ?></button>
      </div>
      <div class="col-sm-8">
        <canvas id="canvas" width="600" height="600" class="center-block"></canvas>
      </div>
      <div class="col-sm-2">
        <ul id="drawn" style="height:600px;overflow-y:auto;font-size:16px" class="list-unstyled"></ul>
      </div>
    </div>
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
          <?php if (Yii::app()->user->checkAccess(User::ROLE_ORGANIZER)): ?>
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
<?php
Yii::app()->clientScript->registerScriptFile('/f/plugins/tagcanvas/tagcanvas.min.js');
Yii::app()->clientScript->registerScriptFile('/f/js/store+json2.min.js');
Yii::app()->clientScript->registerScriptFile('/f/js/luckyDraw.js');