<div class="col-lg-12">
  <?php $form = $this->beginWidget('ActiveForm', array(
    'htmlOptions'=>array(
      'role'=>'form',
      'class'=>'form-inline',
    ),
    'method'=>'get',
    'action'=>array('/results/person'),
  )); ?>
    <div class="form-group">
      <label for="region"><?php echo Yii::t('common', 'Region'); ?></label>
      <?php echo CHtml::dropDownList('region', $region, Region::getWCARegions(), array(
        'class'=>'form-control',
      )); ?>
    </div>
    <div class="form-group">
      <label for="gender"><?php echo Yii::t('common', 'Gender'); ?></label>
      <?php echo CHtml::dropDownList('gender', $gender, Persons::getGenders(), array(
        'class'=>'form-control',
      )); ?>
    </div>
    <div class="form-group">
      <label for="name"><?php echo Yii::t('Results', 'Name, parts or WCA ID'); ?></label>
      <?php echo CHtml::textField('name', $name, array(
        'class'=>'form-control',
      )); ?>
    </div>
    <button type="submit" class="btn btn-theme"><?php echo Yii::t('common', 'Submit'); ?></button>
  <?php $this->endWidget(); ?>
  <?php
  $this->widget('GridView', array(
    'dataProvider'=>new NonSortArrayDataProvider($persons['rows'], array(
      'pagination'=>array(
        'pageSize'=>100,
        'pageVar'=>'page',
      ),
      'sliceData'=>false,
      'totalItemCount'=>$persons['count'],
    )),
    'template'=>'{summary}{items}{pager}',
    'enableSorting'=>false,
    'front'=>true,
    'columns'=>array(
      array(
        'headerHtmlOptions'=>array(
          'class'=>'battle-checkbox',
        ),
        'header'=>Yii::t('common', 'Battle'),
        'value'=>'Persons::getBattleCheckBox($data["name"], $data["wca_id"])',
        'type'=>'raw',
      ),
      array(
        'name'=>Yii::t('Results', 'Name'),
        'value'=>'Persons::getLinkByNameNId($data["name"], $data["wca_id"])',
        'type'=>'raw',
      ),
      array(
        'name'=>Yii::t('common', 'WCA ID'),
        'value'=>'Persons::getWCAIconLinkByNameNId($data["name"], $data["wca_id"])',
        'type'=>'raw',
        'htmlOptions'=>array('class'=>'region'),
      ),
      array(
        'name'=>Yii::t('common', 'Region'),
        'value'=>'Region::getIconName($data["countryName"], $data["iso2"])',
        'type'=>'raw',
        'htmlOptions'=>array('class'=>'region'),
      ),
      array(
        'name'=>Yii::t('common', 'Gender'),
        'value'=>'$data["gender"] && strtolower($data["gender"]) !== "o" ? (Yii::t("common", strtolower($data["gender"]) == "f" ? "Female" : "Male")) : ""',
        'type'=>'raw',
      ),
    ),
  )); ?>
</div>
