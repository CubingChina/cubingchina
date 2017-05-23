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
          <?php $columns = [
            [
              'header'=>'操作',
              'type'=>'raw',
              'value'=>'$data->operationButton',
            ],
            [
              'name'=>'date',
              'type'=>'raw',
              'value'=>'$data->getDisplayDate()',
              'filter'=>false,
            ],
            [
              'name'=>'type',
              'type'=>'raw',
              'value'=>'$data->getTypeText()',
              'filter'=>Competition::getTypes()
            ],
            'name_zh',
            [
              'header'=>'运营费',
              'type'=>'raw',
              'value'=>'$data->operationFeeButton',
              'sortable'=>false,
              'filter'=>false,
            ],
            [
              'name'=>'province_id',
              'type'=>'raw',
              'value'=>'$data->getLocationInfo("province")',
              'sortable'=>false,
              'filter'=>false,
            ],
            [
              'name'=>'city_id',
              'type'=>'raw',
              'value'=>'$data->getLocationInfo("city")',
              'sortable'=>false,
              'filter'=>false,
            ],
            [
              'name'=>'venue',
              'type'=>'raw',
              'value'=>'$data->getLocationInfo("venue")',
              'sortable'=>false,
              'filter'=>false,
            ],
            [
              'name'=>'status',
              'type'=>'raw',
              'value'=>'$data->getStatusText()',
              'filter'=>Competition::getAllStatus($this->action->id),
            ],
          ];
          if ($model->scenario === 'application') {
            array_splice($columns, 4, 1, [
              [
                'header'=>'申请人',
                'value'=>'$data->organizer[0]->user->name_zh ?: $data->organizer[0]->user->name',
              ],
            ]);
            $columns[] = [
              'name'=>'create_time',
              'header'=>'创建时间',
              'value'=>'date("Y-m-d H:i:s", $data->create_time)',
            ];
            $columns[] = [
              'name'=>'confirm_time',
              'header'=>'确认时间',
              'value'=>'$data->isConfirmed() || $data->isRejected() ? date("Y-m-d H:i:s", $data->confirm_time) : "-"',
            ];
          }
          $this->widget('GridView', [
            'dataProvider'=>$model->search(true),
            'afterAjaxUpdate'=>'js:function(){$(".tips").tooltip()}',
            'filter'=>$model,
            'columns'=>$columns,
          ]); ?>
        </div>
      </div>
    </div>
  </div>
</div>
