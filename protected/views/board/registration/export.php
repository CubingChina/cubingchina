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
          <h4>导出到EXCEL</h4>
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
          <?php foreach ($competition->associatedEvents as $event=>$data): ?>
          <div class="form-group">
            <?php echo $form->labelEx($model, 'name_zh', array(
              'class'=>'col-lg-2 control-label',
              'label'=>Events::getFullEventName($event),
            )); ?>
            <div class="col-lg-10 checkbox">
              <?php for ($i = 0; $i < $data['round']; $i++): ?>
              <div class="export-round">
                第<?php echo $i + 1; ?>轮：<?php echo CHtml::dropDownList("event[{$event}][{$i}]", Events::getDefaultExportFormat($event), $exportFormsts, array()); ?>
              </div>
              <?php endfor; ?>
            </div>
          </div>
          <?php endforeach; ?>
          <div class="form-group">
            <div class="col-lg-10 col-lg-offset-2">
              <div class="radio">
                <label>
                  <input type="radio" value="date" name="order" checked> 按报名顺序排序
                </label>
              </div>
              <div class="radio">
                <label>
                  <input type="radio" value="user.name" name="order"> 按姓名首字母排序
                </label>
              </div>
              <div class="radio">
                <label>
                  <input type="radio" value="random" name="order" disabled> 随机排序
                </label>
              </div>
            </div>
          </div>
          <div class="form-group">
            <div class="col-lg-10 col-lg-offset-2">
              <div class="checkbox">
                <label>
                  <input type="checkbox" value="1" name="extra"> 导出报名费、手机、邮箱及备注等额外信息
                </label>
              </div>
            </div>
          </div>
          <div class="form-group">
            <div class="col-lg-10 col-lg-offset-2">
              <div class="checkbox">
                <label>
                  <input type="checkbox" value="1" name="all"> 包括未审核用户
                </label>
              </div>
            </div>
          </div>
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
