<div class="row">
  <div class="col-lg-12">
    <div class="portlet portlet-default">
      <div class="portlet-heading">
          <div class="portlet-title">
              <h4>FAQ列表</h4>
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
                'filter'=>false,
              ),
              array(
                'name'=>'category_id',
                'filter'=>FaqCategory::getCategories(),
                'value'=>'$data->category->name_zh',
              ),
              'title_zh',
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
                'filter'=>Faq::getAllStatus(),
              ),
            ),
          )); ?>
        </div>
      </div>
    </div>
  </div>
</div>