<div class="row">
  <div class="col-lg-12">
    <div class="portlet portlet-default">
      <div class="portlet-heading">
          <div class="portlet-title">
              <h4>评价列表</h4>
          </div>
          <div class="clearfix"></div>
      </div>
      <div class="panel-collapse collapse in">
        <div class="portlet-body">
          <?php $this->widget('GridView', array(
            'dataProvider'=>$model->search(),
            'filter'=>$model,
            'columns'=>array(
              array(
                'header'=>'操作',
                'type'=>'raw',
                'value'=>'$data->operationButton',
              ),
              // 'id',
              array(
                'name'=>'organizer_id',
                'value'=>'$data->organizer ? $data->organizer->name_zh : ""',
              ),
              array(
                'name'=>'competition_id',
                'value'=>'$data->competition ? $data->competition->name_zh : "无"',
              ),
              array(
                'name'=>'rank',
                'type'=>'raw',
                'value'=>'$data->getRankText()',
                'filter'=>Review::getRanks(),
              ),
              array(
                'name'=>'date',
                'type'=>'raw',
                'value'=>'date("Y-m-d H:i:s", $data->date)',
                'filter'=>false,
              ),
              array(
                'name'=>'comments',
                'type'=>'raw',
                'value'=>'$data->getCommentsButton()',
              ),
              array(
                'name'=>'user_id',
                'value'=>'$data->user->name_zh',
              ),
            ),
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
Yii::app()->clientScript->registerScript('review',
<<<EOT
  var modalBody = $('#comments-modal').find('.modal-body');
  $(document).on('click', '.view-comments', function() {
    modalBody.text($(this).data('comments'));
  });
  if ('ontouchstart' in window) {
    $('.modal.fade').removeClass('fade');
  }
EOT
);