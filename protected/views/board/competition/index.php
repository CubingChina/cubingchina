<div class="row">
  <div class="col-lg-12">
    <div class="portlet portlet-default">
      <div class="portlet-heading">
          <div class="portlet-title">
              <h4>比赛列表</h4>
          </div>
          <div class="clearfix"></div>
      </div>
      <div class="panel-collapse collapse in">
        <div class="portlet-body">
          <?php $this->widget('GridView', array(
            'dataProvider'=>$model->search(true),
            'filter'=>$model,
            'columns'=>array(
              array(
                'header'=>'操作',
                'type'=>'raw',
                'value'=>'$data->operationButton',
              ),
              array(
                'name'=>'date',
                'type'=>'raw',
                'value'=>'$data->getDisplayDate()',
                'filter'=>false,
              ),
              array(
                'name'=>'type',
                'type'=>'raw',
                'value'=>'$data->getTypeText()',
                'filter'=>Competition::getTypes(),
              ),
              'name_zh',
              array(
                'header'=>'运营费',
                'type'=>'raw',
                'value'=>'$data->operationFeeButton . ($data->operationFee * $data->days)',
                'sortable'=>false,
                'filter'=>false,
              ),
              array(
                'name'=>'province_id',
                'type'=>'raw',
                'value'=>'$data->getLocationInfo("province")',
                'sortable'=>false,
                'filter'=>false,
              ),
              array(
                'name'=>'city_id',
                'type'=>'raw',
                'value'=>'$data->getLocationInfo("city")',
                'sortable'=>false,
                'filter'=>false,
              ),
              array(
                'name'=>'venue',
                'type'=>'raw',
                'value'=>'$data->getLocationInfo("venue")',
                'sortable'=>false,
                'filter'=>false,
              ),
              array(
                'name'=>'status',
                'type'=>'raw',
                'value'=>'$data->getStatusText()',
                'filter'=>Competition::getAllStatus(),
              ),
            ),
          )); ?>
        </div>
      </div>
    </div>
  </div>
</div>