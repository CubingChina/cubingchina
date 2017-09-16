<div class="row">
  <div class="col-lg-12">
  <div class="portlet portlet-default">
    <div class="portlet-heading">
      <div class="portlet-title">
        <h4>签到管理</h4>
      </div>
      <div class="clearfix"></div>
    </div>
    <div class="panel-collapse collapse in">
    <div class="portlet-body">
      <?php if ($model->competition !== null): ?>
      <?php if ($model->competition->live && $model->competition->liveResults != array()): ?>
      <?php echo CHtml::link('导出直播成绩表', array('/board/registration/exportLiveData', 'id'=>$model->competition_id), array('class'=>'btn btn-square btn-large btn-orange')); ?>
      <?php endif; ?>
      <?php echo CHtml::link('导出成绩表及名单', array('/board/registration/export', 'id'=>$model->competition_id), array('class'=>'btn btn-square btn-large btn-purple')); ?>
      <?php echo CHtml::link('导出初赛成绩单', array('/board/registration/scoreCard', 'id'=>$model->competition_id), array('class'=>'btn btn-square btn-large btn-green')); ?>
      <?php echo CHtml::link('发邮件给选手', array('/board/registration/sendNotice', 'id'=>$model->competition_id), array('class'=>'btn btn-square btn-large btn-blue')); ?>
      <?php echo CHtml::link('报名管理', array('/board/registration/index', 'Registration'=>['competition_id'=>$model->competition_id]), array('class'=>'btn btn-square btn-large btn-red')); ?>
      <?php endif; ?>
      <div class="well">
        <p>请使用微信扫描如下二维码进入签到页面，<b class="text-danger">切勿外传</b>！</p>
        <p><?php echo CHtml::image($scanAuth->getQRCodeUrl()); ?></p>
        <p>扫码枪请<?php echo CHtml::link('点击这里', $model->competition->getUrl('scan'), ['target'=>'_blank']); ?>进行签到。</p>
      </div>
      <?php $columns = $model->getAdminColumns(); ?>
      <?php $this->widget('RepeatHeaderGridView', array(
        'dataProvider'=>$model->search($columns, false, true),
        'template'=>'{pager}{items}{pager}',
        // 'filter'=>$model,
        'columns'=>[
          [
            'header'=>'操作',
            'headerHtmlOptions'=>array(
              'class'=>'header-operation',
            ),
            'type'=>'raw',
            'value'=>'$data->signinOperationButton',
          ],
          [
            'name'=>'number',
            'header'=>'No.',
            'value'=>'$data->number',
          ],
          [
            'name'=>'name',
            'header'=>Yii::t('Results', 'Name'),
            'headerHtmlOptions'=>array(
              'class'=>'header-username',
            ),
            'type'=>'raw',
            'value'=>'$data->user->getWcaLink()',
          ],
          [
            'name'=>'signed_in',
            'header'=>'签到',
            'value'=>'$data->getSigninStatusText()',
          ],
          [
            'name'=>'signed_date',
            'header'=>'签到时间',
            'value'=>'$data->signed_date ? date("Y-m-d H:i:s", $data->signed_date) : "-"',
          ],
        ],
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
