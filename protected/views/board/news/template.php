<div class="row">
  <div class="col-lg-12">
    <div class="portlet portlet-default">
      <div class="portlet-heading">
          <div class="portlet-title">
              <h4>新闻模板列表</h4>
          </div>
          <div class="clearfix"></div>
      </div>
      <div class="panel-collapse collapse in">
        <div class="portlet-body">
          <?php $this->widget('GridView', array(
            'dataProvider'=>$model->search(),
            'columns'=>array(
              array(
                'header'=>'操作',
                'type'=>'raw',
                'value'=>'$data->operationButton',
              ),
              'id',
              'name',
            ),
          )); ?>
        </div>
      </div>
    </div>
  </div>
</div>