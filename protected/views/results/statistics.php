<div class="col-lg-12">
  <div class="row">
    <?php foreach ($statistics as $name=>$statistic): ?>
    <div class="<?php echo $statistic['class']; ?>">
      <h3><?php echo Yii::t('common', $name); ?></h3>
      <?php $this->widget('GridView', array(
        'dataProvider'=>new CArrayDataProvider($statistic['rows'], array(
          'pagination'=>false,
        )),
        'enableSorting'=>false,
        'front'=>true,
        'columns'=>$statistic['columns'],
      ));
      ?>
    </div>
    <?php endforeach; ?>
  </div>
</div>