<div class="row">
  <div class="col-lg-12">
    <div class="portlet portlet-default">
      <div class="portlet-heading">
        <div class="portlet-title">
          <h4>入场券购买记录</h4>
        </div>
        <div class="clearfix"></div>
      </div>
      <div class="panel-collapse collapse in">
        <div class="portlet-body">
          <?php $form = $this->beginWidget('ActiveForm', array(
            'action'=>array('/board/pay/ticket'),
            'method'=>'get',
            'id'=>'ticket-form',
            'htmlOptions'=>array(),
          )); ?>
          <?php echo Html::formGroup(
            $model, 'competition_id', array(),
            $form->dropDownList($model, 'competition_id', Competition::getRegistrationCompetitions(), array(
              'prompt'=>'',
            ))
          ); ?>
          <?php $this->endWidget(); ?>

          <?php
          $dataProvider = $model->search();
          $criteria = $dataProvider->getCriteria();
          $competition = null;
          if ($model->competition_id) {
            $competition = Competition::model()->findByPk($model->competition_id);
          }
          $sum = function($criteria, $status) {
            $c = clone $criteria;
            $c->select = 'SUM(t.paid_amount) AS paid_amount';
            $c->limit = -1;
            $c->offset = -1;
            $c->compare('t.status', $status);
            $model = UserTicket::model()->find($c);
            return $model && $model->paid_amount ? number_format($model->paid_amount / 100, 2, '.', '') : '0.00';
          };
          $paidTotal = $sum($criteria, UserTicket::STATUS_PAID);
          $feeTotal = $paidTotal * 0.012;
          $unpaidTotal = $sum($criteria, UserTicket::STATUS_UNPAID);
          $summaryTitle = '入场券总计';
          if ($competition !== null) {
            $summaryTitle .= '（' . $competition->name_zh . '）';
          }
          ?>
          <pre><?php echo $summaryTitle; ?>：
总收入：<span class="text-success">+<?php echo $paidTotal; ?></span>
手续费：<span class="text-danger">-<?php echo $feeTotal; ?></span>
未付款：<span class="text-warning">+<?php echo $unpaidTotal; ?></span>
实收入：<span class="text-success">+<?php echo $paidTotal - $feeTotal; ?></span>
</pre>
          <?php $this->widget('GridView', array(
            'dataProvider'=>$dataProvider,
            'filter'=>$model,
            'template'=>'{summary}{pager}{items}{pager}',
            'columns'=>array(
              array(
                'name'=>'id',
                'htmlOptions'=>array('style'=>'width:80px;'),
              ),
              array(
                'name'=>'competition_id',
                'header'=>'比赛',
                'value'=>'$data->ticket && $data->ticket->competition ? $data->ticket->competition->name_zh : ""',
                'filter'=>false,
              ),
              array(
                'name'=>'ticket_name',
                'header'=>'入场券',
                'value'=>'$data->ticket ? $data->ticket->name_zh : ""',
                // 'filter'=>CHtml::activeTextField($model, "ticket_name", array("class"=>"form-control")),
              ),
              array(
                'name'=>'buyer_name',
                'header'=>'购买人',
                'value'=>'$data->user ? $data->user->getCompetitionName() : ""',
                'filter'=>false,
              ),
              array(
                'name'=>'name',
                'header'=>'入场人',
              ),
              array(
                'name'=>'passport_number',
                'header'=>'证件号码',
              ),
              array(
                'name'=>'paid_amount',
                'header'=>'支付金额',
                'value'=>'$data->paid_amount ? number_format($data->paid_amount / 100, 2) : ""',
                'filter'=>false,
              ),
              array(
                'name'=>'paid_time',
                'header'=>'支付时间',
                'value'=>'$data->paid_time ? date("Y-m-d H:i:s", $data->paid_time) : ""',
                'filter'=>false,
              ),
              array(
                'name'=>'status',
                'header'=>'状态',
                'value'=>'$data->isPaid() ? Yii::t("common","Paid") : Yii::t("common","Unpaid")',
                'filter'=>array(
                  UserTicket::STATUS_UNPAID=>Yii::t("common","Unpaid"),
                  UserTicket::STATUS_PAID=>Yii::t("common","Paid"),
                ),
              ),
            ),
          )); ?>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
Yii::app()->clientScript->registerScript('ticket-filter',
<<<EOT
  $(document).on('change', '#UserTicket_competition_id', function() {
    $('#ticket-form').submit();
  });
EOT
);
