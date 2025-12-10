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

          // 多地址收款统计
          $locationStats = [];
          $locationStatsText = '';
          if ($competition !== null && $model->type_id > 0 && $competition->isMultiLocation()) {
            $locationPaid = $model->getTotalByLocation(Pay::STATUS_PAID);
            $locationWait = $model->getTotalByLocation(Pay::STATUS_WAIT_CONFIRM);
            $locationRefund = $model->getTotalByLocation(Pay::STATUS_PAID, 'refund_amount');
            $locationFee = $model->getTotalFeeByLocation();

            // 计算各地址的 WCA 会费和粗饼运营费
            $wcaDuesPerPerson = 0;
            $cubingFeePerPerson = 0;
            if ($competition->isWCACompetition() && $competition->date >= Competition::WCA_DUES_START) {
              $wcaDuesPerPerson = $competition->getEventFee(Competition::EVENT_FEE_WCA_DUES);
            }
            if ($competition->id >= 382) {
              $dailyRate = 3;
              if ($competition->date < Competition::CUBING_FEE_BEFORE_202101) {
                $dailyRate = 1;
              } elseif ($competition->date < Competition::CUBING_FEE_BEFORE_202507) {
                $dailyRate = 2;
              }
              $cubingFeePerPerson = $competition->days * $dailyRate;
            }

            foreach ($competition->sortedLocations as $location) {
              $locId = $location->location_id;
              $locPaid = isset($locationPaid[$locId]) ? floatval($locationPaid[$locId]) : 0;
              $locWait = isset($locationWait[$locId]) ? floatval($locationWait[$locId]) : 0;
              $locRefund = isset($locationRefund[$locId]) ? floatval($locationRefund[$locId]) : 0;
              $locFee = isset($locationFee[$locId]) ? floatval($locationFee[$locId]) : 0;

              // 获取该地址的报名人数（已接受的）
              $locCompetitors = Registration::model()->countByAttributes([
                'competition_id' => $competition->id,
                'location_id' => $locId,
                'status' => Registration::STATUS_ACCEPTED,
              ]);

              // 计算该地址的 WCA 会费和粗饼运营费
              $locWcaDues = $wcaDuesPerPerson * $locCompetitors;
              $locCubingFee = $cubingFeePerPerson * $locCompetitors;

              // 实收 = 实付款 + 待收货 - 退款 - 手续费 - WCA会费 - 粗饼运营费
              $locIncome = $locPaid + $locWait - $locRefund - $locFee - $locWcaDues - $locCubingFee;
              $locTotal = number_format($locIncome, 2, '.', '');

              $locationStats[$locId] = [
                'name' => $location->getCityName(),
                'paid' => number_format($locPaid, 2, '.', ''),
                'wait' => number_format($locWait, 2, '.', ''),
                'refund' => number_format($locRefund, 2, '.', ''),
                'fee' => number_format($locFee, 2, '.', ''),
                'competitors' => $locCompetitors,
                'wcaDues' => number_format($locWcaDues, 2, '.', ''),
                'cubingFee' => number_format($locCubingFee, 2, '.', ''),
                'total' => $locTotal,
              ];
            }
          }

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

          // 生成多地址统计文本
          if (!empty($locationStats)) {
            $locationStatsText = "\n\n========================\n各地址实收（不包含转账手续费）：\n========================";
            foreach ($locationStats as $locId => $stat) {
              $locationStatsText .= "\n【{$stat['name']}】 {$stat['total']}";
            }
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
　合计： {$total}{$organizerIncomeText}{$locationStatsText}
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
