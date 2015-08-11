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
          <h4>发送邮件通知给选手</h4>
        </div>
        <div class="clearfix"></div>
      </div>
      <div class="panel-collapse collapse in">
        <div class="portlet-body">
          <?php $form = $this->beginWidget('ActiveForm', array(
            'htmlOptions'=>array(
              'class'=>'clearfix row',
            ),
            'enableClientValidation'=>true,
            'clientOptions'=>array(
              'validateOnSubmit'=>true,
            ),
          )); ?>
            <?php echo Html::formGroup(
              $model, 'title', array(
                'class'=>'col-lg-12',
              ),
              $form->labelEx($model, 'title'),
              Html::activeTextField($model, 'title'),
              $form->error($model, 'title', array('class'=>'text-danger'))
            );?>
            <?php echo Html::formGroup(
              $model, 'content_zh', array(
                'class'=>'col-lg-6',
              ),
              $form->labelEx($model, 'content_zh'),
              $form->textArea($model, 'content_zh', array(
                'class'=>'editor form-control'
              )),
              $form->error($model, 'content_zh', array('class'=>'text-danger'))
            );?>
            <?php echo Html::formGroup(
              $model, 'content', array(
                'class'=>'col-lg-6',
              ),
              $form->labelEx($model, 'content'),
              $form->textArea($model, 'content', array(
                'class'=>'editor form-control'
              )),
              $form->error($model, 'content', array('class'=>'text-danger'))
            );?>
            <div class="col-lg-12">
              <button type="submit" class="btn btn-default btn-square">发送</button>
              <button type="button" class="btn btn-purple btn-square" id="preview">预览</button>
            </div>
            <div class="col-lg-12">
              <h4>选择要发送的选手（默认为已审核）</h4>
            </div>
            <div class="col-lg-12">
              <button type="button" class="btn btn-green btn-square select" data-type="all">全选</button>
              <button type="button" class="btn btn-red btn-square select" data-type="none">全不选</button>
              <button type="button" class="btn btn-blue btn-square select" data-type="reverse">反选</button>
              <button type="button" class="btn btn-orange btn-square select" data-type="accepted">已审核</button>
              <button type="button" class="btn btn-purple btn-square select" data-type="unaccepted">未审核</button>
            </div>
            <div class="col-lg-12">
              <?php $columns = $registration->getNoticeColumns($model); ?>
              <?php $this->widget('RepeatHeaderGridView', array(
                'dataProvider'=>$registration->search($columns),
                'columns'=>$columns,
              )); ?>
            </div>
          <?php $this->endWidget(); ?>
          <div class="clearfix"></div>
        </div>
      </div>
    </div>
  </div>
</div>
<div tabindex="-1" id="preview-modal" class="modal fade">
  <div class="modal-dialog" style="width:660px">
    <div class="modal-content">
      <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
          <h4 class="modal-title"></h4>
      </div>
      <div class="modal-body">
      </div>
      <div class="modal-footer">
        <button data-dismiss="modal" class="btn btn-default" type="button">关闭</button>
      </div>
    </div>
  </div>
</div>
<?php
$this->widget('Editor');
$previewUrl = $this->createUrl('/board/registration/previewNotice', array('id'=>$competition->id));
Yii::app()->clientScript->registerScript('sendNotice',
<<<EOT
  $(document).on('click', 'button.select', function() {
    var type = $(this).data('type');
    switch (type) {
      case 'all':
      case 'none':
        $('.competitor').prop('checked', type === 'all');
        break;
      case 'reverse':
        $('.competitor').each(function() {
          this.checked = !this.checked;
        });
        break;
      case 'accepted':
      case 'unaccepted':
        $('.competitor[data-accepted="' + (+(type === 'accepted')) + '"]').prop('checked', true);
        $('.competitor[data-accepted="' + (+(type !== 'accepted')) + '"]').prop('checked', false);
        break;
    }
    updateCount();
  }).on('click', '#preview', function() {
    for (var id in editors) {
      editors[id].sync();
    }
    $.ajax({
      url: '$previewUrl',
      data: $(this).parents('form').serialize(),
      type: 'post',
      dataType: 'json',
      success: function(data) {
        $('#preview-modal .modal-title').html(data.subject);
        $('#preview-modal .modal-body').html(data.message);
        $('#preview-modal').modal('show');
      }
    });
  }).on('change', '.competitor', function() {
    updateCount();
  });
  updateCount();
  function updateCount() {
    var count = $('.competitor:checked').length;
    $('table tbody tr:first-child td:first-child, table tfoot tr:first-child td:first-child').text('已选择' + count + '人');
  }
EOT
);