<?php $this->setPageTitle(['Contact']); ?>
<?php $this->setTitle('Contact'); ?>
<?php $this->breadcrumbs = array(
  'Contact'
); ?>
<?php $this->renderPartial('aboutSide', $_data_); ?>
<div class="content-wrapper col-md-10 col-sm-9">
  <?php if (substr(Yii::app()->language, 0, 2) == 'zh'): ?>
  <?php echo Yii::app()->controller->translateTWInNeed(
<<<EOT
  <p>
    如果你想采用我们的比赛管理系统并公示一场魔方竞速比赛，或者给我们网站以更多更好的意见和建议，欢迎通过以下邮件联系我们：
    <br><i class="fa fa-envelope"></i> <a href="mailto:admin@cubingchina.com">admin@cubingchina.com</a>。
  </p>
  <p>
    如果你想举办一场WCA官方魔方比赛或举办相关活动，可以联系中国的世界魔方协会代表(WCA Delegates)：
    <br><i class="fa fa-envelope"></i> <a href="mailto:wcadaibiao@gmail.com">wcadaibiao@gmail.com</a>。
  </p>
  <p>
    如果你的所在地位于或者邻近以下城市列表中，你也可以直接与该地的世界魔方协会代表取得联系：
  </p>
  <p>
  </p>
EOT
); ?>
  <?php else: ?>
  <p>
    If you would like to use our website to announce a speedcubing competition and generate a competition webpage, or have any suggestions, feel free to contact the <u>Cubing China Administrator</u> via <br><i class="fa fa-envelope"></i> <a href="mailto:admin@cubingchina.com">admin@cubingchina.com</a>.
  </p>
  <p>
    If you would like to hold a WCA official speedcubing competition or organize a relevant acitivity, please contact the <u>Chinese WCA Delegates</u> via <br><i class="fa fa-envelope"></i> <a href="mailto:wcadaibiao@gmail.com">wcadaibiao@gmail.com</a>.
  </p>
  <p>
    If you are located in or near one of the following cities, you can also get in touch with the local WCA Delegate directly.
  </p>
  <p>
  </p>
  <?php endif; ?>
  <?php $this->widget('GridView', [
    'dataProvider'=>new CArrayDataProvider(User::getShowDelegates()),
    'enableSorting'=>false,
    'hideHeader'=>true,
    'front'=>true,
    'emptyText'=>'',
    'columns'=>[
      [
        'type'=>'raw',
        'value'=>'Html::fontAwesome("user", "a") . $data->getAttributeValue("name")',
      ],
      [
        'type'=>'raw',
        'value'=>'Html::fontAwesome("building", "a") . ($data->city ? (in_array($data->province_id, [215, 525, 567, 642]) ? $data->province : $data->city)->getAttributeValue("name") : $data->country->getAttributeValue("name"))',
      ],
      [
        'type'=>'raw',
        'value'=>'Html::fontAwesome("envelope", "a") . CHtml::mailTo($data->email)',
      ],
    ],
  ]); ?>
</div>
