<div class="row">
  <div class="col-lg-12">
    <div class="portlet portlet-default">
      <div class="portlet-heading">
          <div class="portlet-title">
              <h4>用户列表</h4>
          </div>
          <div class="clearfix"></div>
      </div>
      <div class="panel-collapse collapse in">
        <div class="portlet-body">
          <?php $this->widget('GridView', array(
            'dataProvider'=>$model->search(),
            'template'=>'{pager}{items}{pager}',
            'afterAjaxUpdate'=>'js:function(){$(".tips").tooltip()}',
            'filter'=>$model,
            'columns'=>array(
              array(
                'header'=>'操作',
                'headerHtmlOptions'=>array(
                  'class'=>'header-operation-3',
                ),
                'type'=>'raw',
                'value'=>'$data->operationButton',
              ),
              array(
                'headerHtmlOptions'=>array(
                  'class'=>'header-id',
                ),
                'name'=>'id',
                'filter'=>false,
              ),
              array(
                'headerHtmlOptions'=>array(
                  'class'=>'header-name',
                ),
                'name'=>'name',
              ),
              array(
                'headerHtmlOptions'=>array(
                  'class'=>'header-name-cn',
                ),
                'name'=>'name_zh',
              ),
              array(
                'headerHtmlOptions'=>array(
                  'class'=>'header-email',
                ),
                'name'=>'email',
                'type'=>'raw',
                'value'=>'$data->getEmailLink()',
              ),
              array(
                'headerHtmlOptions'=>array(
                  'class'=>'header-wcaid',
                ),
                'name'=>'wcaid',
                'type'=>'raw',
                'value'=>'$data->getWcaLink($data->wcaid)',
              ),
              array(
                'headerHtmlOptions'=>array(
                  'class'=>'header-gender',
                ),
                'filter'=>User::getGenders(),
                'name'=>'gender',
                'value'=>'$data->getGenderText()',
              ),
              array(
                'name'=>'birthday',
                'headerHtmlOptions'=>array(
                  'class'=>'header-birthday',
                ),
                'filter'=>false,
                'type'=>'raw', 
                'value'=>'date("Y-m-d", $data->birthday)', 
              ),
              array(
                'headerHtmlOptions'=>array(
                  'class'=>'header-country',
                ),
                'filter'=>false,
                'name'=>'country_id',
                'type'=>'raw',
                'value'=>'$data->getRegionName($data->country)',
              ),
              // array(
              //   'name'=>'province_id',
              //   'type'=>'raw',
              //   'value'=>'$data->getRegionName($data->province)',
              // ),
              // array(
              //   'name'=>'city_id',
              //   'type'=>'raw',
              //   'value'=>'$data->getRegionName($data->city)',
              // ),
              array(
                'headerHtmlOptions'=>array(
                  'class'=>'header-time',
                ),
                'filter'=>false,
                'name'=>'reg_time',
                'type'=>'raw',
                'value'=>'date("Y-m-d H:i:s", $data->reg_time)',
              ),
              array(
                'headerHtmlOptions'=>array(
                  'class'=>'header-ip',
                ),
                'name'=>'reg_ip',
                'type'=>'raw',
                'value'=>'$data->getRegIpDisplay()',
              ),
              array(
                'headerHtmlOptions'=>array(
                  'class'=>'header-role',
                ),
                'filter'=>User::getRoles(),
                'name'=>'role',
                'type'=>'raw',
                'value'=>'$data->getRoleName()',
              ),
            ),
          )); ?>
        </div>
      </div>
    </div>
  </div>
</div>