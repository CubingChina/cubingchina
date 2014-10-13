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
            'dataProvider'=>$model->adminSearch(),
            'columns'=>array(
              array(
                'header'=>'操作',
                'type'=>'raw',
                'value'=>'$data->operationButton',
              ),
              'id',
              array(
                'name'=>'date',
                'type'=>'raw',
                'value'=>'$data->getDisplayDate()',
              ),
              array(
                'name'=>'type',
                'type'=>'raw',
                'value'=>'$data->getTypeText()',
              ),
              'name_zh',
              array(
                'name'=>'province_id',
                'type'=>'raw',
                'value'=>'isset($data->location[1]) ? "多地点" : $data->location[0]->province->name_zh',
              ),
              array(
                'name'=>'city_id',
                'type'=>'raw',
                'value'=>'isset($data->location[1]) ? "多地点" : $data->location[0]->city->name_zh',
              ),
              'venue_zh',
              array(
                'name'=>'status',
                'type'=>'raw',
                'value'=>'$data->getStatusText()',
              ),
            ),
          )); ?>
        </div>
      </div>
    </div>
  </div>
</div>