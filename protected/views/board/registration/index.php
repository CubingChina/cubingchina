<div class="row">
  <div class="col-lg-12">
  <div class="portlet portlet-default">
    <div class="portlet-heading">
      <div class="portlet-title">
        <h4>报名管理</h4>
      </div>
      <div class="clearfix"></div>
    </div>
    <div class="panel-collapse collapse in">
    <div class="portlet-body">
      <?php $form = $this->beginWidget('CActiveForm', array(
        'action'=>array('/board/registration/index'),
        'method'=>'get',
        'id'=>'registration-form',
          'htmlOptions'=>array(
        ),
      )); ?>
      <?php echo Html::formGroup(
        $model, 'competition_id', array(),
        $form->dropDownList($model, 'competition_id', CHtml::listData(Competition::getAllCompetitions(), 'id', 'name_zh'), array(
        'prompt'=>'',
        ))
      ); ?>
      <?php $this->endWidget(); ?>
      <?php if ($model->competition !== null): ?>
      <?php echo CHtml::link('导出到EXCEL', array('/board/registration/export', 'id'=>$model->competition_id), array('class'=>'btn btn-square btn-large btn-purple')); ?>
      <?php endif; ?>
      <?php $columns = $model->getAdminColumns(); ?>
      <?php $this->widget('RepeatHeaderGridView', array(
        'dataProvider'=>$model->search($columns),
        // 'filter'=>$model,
        'columns'=>$columns,
      )); ?>
    </div>
    </div>
  </div>
  </div>
</div>
<div tabindex="-1" id="comments-modal" class="modal fade">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-body">
      </div>
      <div class="modal-footer">
        <button data-dismiss="modal" class="btn btn-default" type="button">关闭</button>
      </div>
    </div>
    <!-- /.modal-content -->
  </div>
  <!-- /.modal-dialog -->
</div>
<?php
Yii::app()->clientScript->registerScript('registration',
<<<EOT
  var modalBody = $('#comments-modal').find('.modal-body');
  $(document).on('change', '#Registration_competition_id', function() {
    $('#registration-form').submit();
  }).on('click', '.view-comments', function() {
    modalBody.text($(this).data('comments'));
  });
  if ('ontouchstart' in window) {
    $('.modal.fade').removeClass('fade');
  }
EOT
);