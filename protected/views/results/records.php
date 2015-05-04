<div class="col-lg-12">
  <div>
    <p><?php echo Yii::t('statistics', 'Regional records are displayed on the page, based on the {url}.', array(
      '{url}'=>CHtml::link(Yii::t('statistics', 'official WCA records'), 'https://www.worldcubeassociation.org/results/regions.php', array('target'=>'_blank')),
    )); ?></p>
  </div>
  <?php $form = $this->beginWidget('CActiveForm', array(
    'htmlOptions'=>array(
      'role'=>'form',
      'class'=>'form-inline',
    ),
    'method'=>'get',
    'action'=>array('/results/records'),
  )); ?>
    <div class="form-group">
      <label for="region"><?php echo Yii::t('common', 'Region'); ?></label>
      <?php echo CHtml::dropDownList('region', $region, Region::getWCARegions(), array(
        'class'=>'form-control',
      )); ?>
    </div>
    <?php if ($type === 'history'): ?>
    <div class="form-group">
      <label for="event"><?php echo Yii::t('common', 'Event'); ?></label>
      <?php echo CHtml::dropDownList('event', $event, Events::getNormalTranslatedEvents(), array(
        'class'=>'form-control',
      )); ?>
    </div>
    <?php endif; ?>
    <?php foreach (array('current', 'history') as $_type): ?>
    <?php echo CHtml::tag('button', array(
      'type'=>'submit',
      'name'=>'type',
      'value'=>$_type,
      'class'=>'btn btn-' . ($type == $_type ? 'warning' : 'theme'),
    ), Yii::t('Results', ucfirst($_type))); ?>
    <?php endforeach; ?>
  <?php $this->endWidget(); ?>
  <?php $this->widget('GroupGridView', array(
    'dataProvider'=>new CArrayDataProvider($records, array(
      'pagination'=>false,
    )),
    'enableSorting'=>false,
    'front'=>true,
    'groupKey'=>'eventId',
    'groupHeader'=>'CHtml::tag("span", array(
        "class"=>"event-icon event-icon event-icon-" . $data["eventId"],
        "title"=>Yii::t("event", Events::getFullEventName($data["eventId"])),
      ), Yii::t("event", Events::getFullEventName($group)))',
    'columns'=>array(
      array(
        'header'=>Yii::t('common', 'Records'),
        'value'=>'isset($data["record"]) ? $data["record"] : $data["regional" . ucfirst($data["type"]) . "Record"]',
      ),
      array(
        'header'=>Yii::t('common', 'Single'),
        'value'=>'$data["type"] === "single" ? Results::formatTime($data["best"], $data["eventId"]) : ""',
        'type'=>'raw',
      ),
      array(
        'header'=>Yii::t('common', 'Average'),
        'value'=>'$data["type"] === "average" ? Results::formatTime($data["average"], $data["eventId"]) : ""',
        'type'=>'raw',
      ),
      array(
        'header'=>Yii::t('statistics', 'Person'),
        'value'=>'Persons::getLinkByNameNId($data["personName"], $data["personId"])',
        'type'=>'raw',
      ),
      array(
        'header'=>Yii::t('common', 'Region'),
        'value'=>'Yii::t("common", Yii::t("Region", $data["countryName"]))',
        'type'=>'raw',
      ),
      array(
        'header'=>Yii::t('common', 'Competition'),
        'value'=>'CHtml::link(ActiveRecord::getModelAttributeValue($data, "name"), $data["url"])',
        'type'=>'raw',
      ),
      array(
        'header'=>Yii::t('Competition', 'Date'),
        'value'=>'date("Y-m-d", strtotime(sprintf("%s-%s-%s", $data["year"], $data["month"], $data["day"])))',
        'type'=>'raw',
      ),
      array(
        'header'=>Yii::t('common', 'Detail'),
        'value'=>'$data["type"] === "single" ? "" : implode("&nbsp;&nbsp;", array_map(function($i) use($data) {
          return Results::formatTime($data["value" . $i], $data["eventId"]);
        }, range(1, 5)))',
        'type'=>'raw',
      ),
    ),
  )); ?>
</div>