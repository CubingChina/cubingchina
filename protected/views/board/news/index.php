<div class="row">
  <div class="col-lg-12">
    <div class="portlet portlet-default">
      <div class="portlet-heading">
          <div class="portlet-title">
              <h4>新闻列表</h4>
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
              'id',
              array(
                'name'=>'user_id',
                'value'=>'$data->user->name_zh',
                'filter'=>News::getPublisherList(),
              ),
              'title_zh',
              array(
                'name'=>'weight',
                'type'=>'raw',
                'value'=>'$data->getWeightText()',
                'filter'=>News::getWeights(),
              ),
              array(
                'name'=>'date',
                'type'=>'raw',
                'value'=>'date("Y-m-d H:i:s", $data->date)',
                'filter'=>false,
              ),
              array(
                'name'=>'status',
                'type'=>'raw',
                'value'=>'$data->getStatusText()',
                'filter'=>News::getAllStatus(),
              ),
            ),
          )); ?>
        </div>
      </div>
    </div>
  </div>
</div>
