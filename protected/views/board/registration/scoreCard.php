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
          <h4>导出初赛成绩单</h4>
        </div>
        <div class="clearfix"></div>
      </div>
      <div class="panel-collapse collapse in">
        <div class="portlet-body">
          <?php $form = $this->beginWidget('CActiveForm', array(
            'htmlOptions'=>array(
              'class'=>'form-horizontal',
            ),
            'enableClientValidation'=>true,
          )); ?>
          <div class="form-group">
            <div class="col-lg-10 col-lg-offset-2">
              <div class="text-danger">导出可能会持续2分钟甚至更长时间，请耐心等待，不要反复刷新！</div>
            </div>
          </div>
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
                  <input type="checkbox" value="1" name="all"> 包括未审核用户
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