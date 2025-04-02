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
    如果你想给我们网站以更多更好的意见和建议，欢迎通过以下邮件联系我们：
    <br><i class="fa fa-envelope"></i> <a href="mailto:contact@cubing.com">contact@cubing.com</a>。
  </p>
  <p>
    如果你想在粗饼平台申请并公示一场魔方比赛，或提交与比赛相关的材料（如主办方申请文件、团队介绍等），请联系粗饼赛事审核组：
    <br><i class="fa fa-envelope"></i> <a href="mailto:competitions@cubing.com">competitions@cubing.com</a>。
  </p>
  <p>
    如果你想要咨询世界魔方协会相关的事宜，请联系世界魔方协会通讯组中国团队：
    <br><i class="fa fa-envelope"></i> <a href="mailto:wct-china@worldcubeassociation.org">wct-china@worldcubeassociation.org</a>。
  </p>
  <p>
    如果你对于比赛成绩或比赛规则有疑问，可以联系中国的世界魔方协会代表(WCA Delegates)的公共邮箱：
    <br><i class="fa fa-envelope"></i> <a href="mailto:delegates@cubing.com">delegates@cubing.com</a>。
    <br>或者你想直接具体和某位世界魔方协会代表取得联系，可以直接通过下面的联系方式：
  </p>

  <p>
  </p>
EOT
); ?>
  <?php else: ?>
  <p>
    If you want to provide feedback and suggestions, feel free to contact the Cubing Administrator via contact@cubing.com. 
  </p>
  <p>
    If you would like to use our website to announce a speedcubing competition and generate a competition webpage or submit your organization team applications, please contact the Cubing Competition Team via competitions@cubing.com. 
  </p>
  <p>
    If you have questions regarding the World Cube Association, please contact the WCA Communication China team via wct-china@worldcubeassociation.org  
  </p>
  <p>
    If you have questions regarding specific competitions or regulations, please contact the Chinese WCA Delegates via delegates@cubing.com. Alternatively, one can get in touch with one of the delegates directly via the contact info below:
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
        'value'=>'Html::fontAwesome("envelope", "a") . $data->getEmailLink()',
      ],
    ],
  ]); ?>
</div>
