<div class="row">
  <div class="col-lg-12">
    <div class="page-title">
      <h1><?php echo $competition->name_zh; ?></h1>
    </div>
  </div>
  <div class="col-lg-12">
    <div class="portlet portlet-default">
      <div class="portlet-heading">
        <div class="portlet-title">
          <h4>导出直播成绩到EXCEL</h4>
        </div>
        <div class="clearfix"></div>
      </div>
      <div class="panel-collapse collapse in">
        <div class="portlet-body">
          <?php $form = $this->beginWidget('ActiveForm', array(
            'htmlOptions'=>array(
              'class'=>'form-horizontal',
            ),
            'enableClientValidation'=>true,
          )); ?>
          <div class="form-group">
            <div class="col-lg-10 col-lg-offset-2">
              <div class="checkbox">
                <label>
                  <input type="checkbox" value="1" name="xlsx" checked> 2010格式（导出更快）
                </label>
              </div>
            </div>
          </div>
          <div class="form-group">
            <div class="col-lg-10 col-lg-offset-2">
              <button type="submit" class="btn btn-default btn-square"><?php echo Yii::t('common', 'Submit'); ?></button>
            </div>
          </div>
          <?php $this->endWidget(); ?>
          <div class="clearfix"></div>
        </div>
      </div>
    </div>
  </div>
</div>