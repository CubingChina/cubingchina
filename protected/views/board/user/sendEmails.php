<div class="row">
  <div class="col-lg-12">
    <div class="page-title">
      <h1>给用户群发邮件</h1>
    </div>
  </div>
  <div class="col-lg-12">
    <div class="portlet portlet-default">
      <div class="portlet-heading">
        <div class="portlet-title">
          <h4>发送邮件通知给用户</h4>
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
              $model, 'user_type', array(
                'class'=>'col-lg-12',
              ),
              $form->labelEx($model, 'user_type'),
              $form->dropDownList($model, 'user_type', $model::getUserTypes(), [
                'prompt'=>'',
                'class'=>'form-control',
              ]),
              $form->error($model, 'user_type', array('class'=>'text-danger'))
            );?>
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
              <button type="button" class="btn btn-danger btn-square" id="send-to-myself">发给自己测试</button>
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
$previewUrl = $this->createUrl('/board/user/previewEmail');
$toMyselfUrl = $this->createUrl('/board/user/sendToMyself');
Yii::app()->clientScript->registerScript('sendEmails',
<<<EOT
  $(document).on('click', '#preview', function() {
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
  }).on('click', '#send-to-myself', function() {
    for (var id in editors) {
      editors[id].sync();
    }
    $.ajax({
      url: '$toMyselfUrl',
      data: $(this).parents('form').serialize(),
      type: 'post',
      dataType: 'json',
      success: function(data) {
        if (data.status === 0) {
          alert('发送成功！');
        }
      }
    });
  });
EOT
);
