<div class="row">
  <div class="col-lg-12">
    <div class="portlet portlet-default">
      <div class="portlet-heading">
          <div class="portlet-title">
              <h4>对账</h4>
          </div>
          <div class="clearfix"></div>
      </div>
      <div class="panel-collapse collapse in">
        <div class="portlet-body">
          <?php $form = $this->beginWidget('ActiveForm', array(
            'action'=>array('/board/pay/bill'),
            'method'=>'get',
            'id'=>'bill-form',
            'htmlOptions'=>array(
              'class'=>'row',
            ),
          )); ?>
          <?php echo Html::formGroup(
            $model, 'paid_time[0]', array(
              'class'=>'col-xs-6 col-lg-3',
            ),
            Html::activeTextField($model, 'paid_time[0]', array(
              'class'=>'datetime-picker',
              'data-date-format'=>'yyyy-mm-dd hh:ii:00',
            ))
          ); ?>
          <?php echo Html::formGroup(
            $model, 'paid_time[1]', array(
              'class'=>'col-xs-6 col-lg-3',
            ),
            Html::activeTextField($model, 'paid_time[1]', array(
              'class'=>'datetime-picker',
              'data-date-format'=>'yyyy-mm-dd hh:ii:00',
            ))
          ); ?>
          <div class="clearfix"></div>
          <div class="col-lg-12">
            <button class="btn btn-sm btn-primary btn-square">提交</button>
          </div>
          <?php $this->endWidget(); ?>
          <?php $dataProvider = $model->searchBill(); ?>
          <?php $paid = $model->getTotal(Pay::STATUS_PAID, true); ?>
          <?php $refund = $model->getTotal(Pay::STATUS_PAID, false, 'refund_amount', false); ?>
          <?php $fee = $model->getBillTotalFee(); ?>
          <?php $total = number_format($paid - $fee, 2, '.', ''); ?>
          <?php $length = strlen($paid); ?>
          <?php $paid = str_pad($paid, $length, ' ', STR_PAD_LEFT); ?>
          <?php $refund = str_pad($refund, $length, ' ', STR_PAD_LEFT); ?>
          <?php $fee = str_pad($fee, $length, ' ', STR_PAD_LEFT); ?>
          <?php $total = str_pad($total, $length, ' ', STR_PAD_LEFT); ?>
          <?php $this->widget('GridView', array(
            'dataProvider'=>$dataProvider,
            'template'=>'{summary}{items}{pager}',
            'summaryText'=>"<pre>总计：
　已付款：<span class=\"text-success\">+{$paid}</span>
　手续费：<span class=\"text-danger\">-{$fee}</span>
------------------------
　　合计： {$total}
累计退款：<span class=\"text-info\"> {$refund}</span>
</pre>",
            // 'filter'=>$model,
            'columns'=>array(
              'id',
              array(
                'name'=>'type',
                'value'=>'$data->getTypeText()',
                'filter'=>Pay::getTypes(),
              ),
              'order_name',
              array(
                'name'=>'paid_amount',
                'header'=>'支付金额',
                'value'=>'number_format($data->paid_amount / 100, 2)',
                'footer'=>$paid,
              ),
              array(
                'name'=>'refund_amount',
                'header'=>'退款金额',
                'value'=>'number_format($data->refund_amount / 100, 2)',
                'footer'=>$paid,
              ),
              array(
                'name'=>'billFee',
                'footer'=>$fee,
                'filter'=>false,
                'header'=>'手续费',
              ),
              array(
                'footer'=>floatval($paid) - floatval($fee),
                'filter'=>false,
                'header'=>'实收',
                'value'=>'number_format(($data->paid_amount - $data->refund_amount) / 100 - $data->billFee, 2)',
              ),
              'order_no',
              'trade_no',
              array(
                'name'=>'create_time',
                'type'=>'raw',
                'value'=>'date("Y-m-d H:i:s", $data->create_time)',
                'filter'=>false,
              ),
              array(
                'name'=>'paid_time',
                'type'=>'raw',
                'value'=>'date("Y-m-d H:i:s", $data->paid_time)',
                'filter'=>false,
              ),
              array(
                'name'=>'status',
                'type'=>'raw',
                'value'=>'$data->getStatusText()',
                'filter'=>Pay::getAllStatus(),
              ),
            ),
          )); ?>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
Yii::app()->clientScript->registerPackage('datetimepicker');
Yii::app()->clientScript->registerScript('bill',
<<<EOT
  $('.datetime-picker').on('mousedown touchstart', function() {
    $(this).datetimepicker({
      autoclose: true
    });
  });
EOT
);
