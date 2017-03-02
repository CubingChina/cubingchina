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
						'rowCssClassExpression'=>'$data->isBanned() ? "danger" : ""',
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
								// 'filter'=>false,
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
								'filter'=>CHtml::activeTextField($model, 'birthday[0]', [
										'class'=>'datetime-picker',
										'data-date-format'=>'yyyy-mm-dd',
										'data-min-view'=>'2',
									])
									. '<br>~<br>'
									. CHtml::activeTextField($model, 'birthday[1]', [
										'class'=>'datetime-picker',
										'data-date-format'=>'yyyy-mm-dd',
										'data-min-view'=>'2',
									]),
								'type'=>'raw',
								'value'=>'date("Y-m-d", $data->birthday)',
							),
							array(
								'headerHtmlOptions'=>array(
									'class'=>'header-country',
								),
								'filter'=>Region::getCountries(),
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
								'value'=>'$data->getRegIpDisplay("reg_ip")',
							),
							array(
								'headerHtmlOptions'=>array(
									'class'=>'header-avatar',
								),
								'filter'=>User::getHasAvatars(),
								'name'=>'avatar_id',
								'value'=>'$data->avatar ? $data->avatar->img : ""',
								'type'=>'raw',
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
							array(
								'headerHtmlOptions'=>array(
									'class'=>'header-role',
								),
								'filter'=>User::getIdentities(),
								'name'=>'identity',
								'type'=>'raw',
								'value'=>'$data->getIdentityName()',
							),
						),
					)); ?>
				</div>
			</div>
		</div>
	</div>
</div>
<div tabindex="-1" id="modal" class="modal fade">
	<div class="modal-dialog" style="width: 800px; max-width: 100%">
		<div class="modal-content">
			<div class="modal-body">
			</div>
			<div class="modal-footer">
				<button data-dismiss="modal" class="btn btn-default" type="button">关闭</button>
			</div>
		</div>
	</div>
</div>
<?php
$url = $this->createUrl('/board/user/registration');
$historyUrl = $this->createUrl('/board/user/loginHistory');
Yii::app()->clientScript->registerPackage('datetimepicker');
Yii::app()->clientScript->registerScript('user',
<<<EOT
	var modal = $('#modal');
	var modalBody = $('#modal .modal-body');
	$(document).on('click', '.js-user-registration', function() {
		$.ajax({
			url: '{$url}',
			data: {
				Registration: {
					user_id: $(this).data('id')
				}
			},
			success: function(data) {
				modalBody.html(data);
				modal.modal('show');
			}
		})
	}).on('click', '.js-user-login-history', function() {
		$.ajax({
			url: '{$historyUrl}',
			data: {
				LoginHistory: {
					user_id: $(this).data('id')
				}
			},
			success: function(data) {
				modalBody.html(data);
				modal.modal('show');
				$('.tips').tooltip();
			}
		})
	}).on('mousedown touchstart', '.datetime-picker', function() {
		$(this).datetimepicker({
			autoclose: true
		});
	});
	if ('ontouchstart' in window) {
		$('.modal.fade').removeClass('fade');
	}
EOT
);
