<div class="row">
  <div class="col-lg-12">
    <div class="portlet portlet-default">
      <div class="portlet-heading">
          <div class="portlet-title">
              <h4>支付流水</h4>
          </div>
          <div class="clearfix"></div>
      </div>
      <div class="panel-collapse collapse in">
        <div class="portlet-body">
          <?php $dataProvider = $model->search(); ?>
          <?php $amount = $model->getTotal(Pay::STATUS_PAID, false, 'amount'); ?>
          <?php $paid = $model->getTotal(Pay::STATUS_PAID); ?>
          <?php $refund = $model->getTotal(Pay::STATUS_PAID, false, 'refund_amount'); ?>
          <?php $wait = $model->getTotal(Pay::STATUS_WAIT_CONFIRM); ?>
          <?php $fee = $model->getTotalFee(); ?>
          <?php $total = number_format($paid + $wait - $refund - $fee, 2, '.', ''); ?>
          <?php
          $wcaDues = 0;
          $cubingFee = 0;
          $transformFee = 9;
          $organizerIncome = '';
          $organizerIncomeText = '';
          $competition = $model->competition;

          if ($competition !== null && $model->type_id > 0) {
            if ($competition->isWCACompetition() && $competition->date >= Competition::WCA_DUES_START) {
              $wcaDuesPerPerson = $competition->getEventFee(Competition::EVENT_FEE_WCA_DUES);
              $wcaDues = number_format($wcaDuesPerPerson * $competition->registeredCompetitors, 2, '.', '');
            }

            if ($competition->id >= 382) {
              $dailyRate = 3;
              if ($competition->date < Competition::CUBING_FEE_BEFORE_202101) {
                $dailyRate = 1;
              } elseif ($competition->date < Competition::CUBING_FEE_BEFORE_202507) {
                $dailyRate = 2;
              }
              $cubingFee = number_format($competition->registeredCompetitors * $competition->days * $dailyRate, 2, '.', '');
            }
            $transformFee = number_format($transformFee, 2, '.', '');
            $organizerIncome = number_format(floatval($total) - floatval($wcaDues) - floatval($cubingFee) - floatval($transformFee), 2, '.', '');
            $organizerIncomeText = "\n------------------------\nWCA 会费：{$wcaDues}\n粗饼运营费：{$cubingFee}\n转账手续费：  {$transformFee}\n   实收：{$organizerIncome}";
          }
          ?>
          <?php $length = max(strlen($paid), strlen($wait)); ?>
          <?php $paid = str_pad($paid, $length, ' ', STR_PAD_LEFT); ?>
          <?php $refund = str_pad($refund, $length, ' ', STR_PAD_LEFT); ?>
          <?php $wait = str_pad($wait, $length, ' ', STR_PAD_LEFT); ?>
          <?php $fee = str_pad($fee, $length, ' ', STR_PAD_LEFT); ?>
          <?php $total = str_pad($total, $length, ' ', STR_PAD_LEFT); ?>
          <?php $wcaDues = str_pad($wcaDues, $length, ' ', STR_PAD_LEFT); ?>
          <?php $cubingFee = str_pad($cubingFee, $length, ' ', STR_PAD_LEFT); ?>
          <?php $transformFee = str_pad($transformFee, $length, ' ', STR_PAD_LEFT); ?>
          <?php $organizerIncome = str_pad($organizerIncome, $length, ' ', STR_PAD_LEFT); ?>
          <?php $this->widget('GridView', array(
            'dataProvider'=>$model->search(),
            'template'=>'{summary}{pager}{items}{pager}',
            'summaryText'=>"<pre>比赛总计：
总　额：<span class=\"text-info\">+{$amount}</span>
实付款：<span class=\"text-success\">+{$paid}</span>
待收货：<span class=\"text-success\">+{$wait}</span>
退　款：<span class=\"text-danger\">-{$refund}</span>
手续费：<span class=\"text-danger\">-{$fee}</span>
------------------------
　合计： {$total}{$organizerIncomeText}
</pre><div class=\"text-info\">此处显示的是实际支付金额，可能和订单金额有出入，请查看列表中高亮订单。</div>",
            'filter'=>$model,
            'rowCssClassExpression'=>'$data->amountMismatch() ? "danger" : ""',
            'columns'=>$model->getColumns(),
          )); ?>
        </div>
      </div>
    </div>
  </div>
</div>
