<div class="col-lg-12">
  <div class="row">
    <div class="col-lg-12">
      <p><?php echo Yii::t('statistics', 'We generate several WCA statistics about Chinese competitions and competitors, based on {url}.', array(
        '{url}'=>CHtml::link(Yii::t('statistics', 'official WCA statistics'), 'https://www.worldcubeassociation.org/results/statistics.php', array('target'=>'_blank')),
      )); ?></p>
      <p class="text-muted"><small><?php echo Yii::t('statistics', 'Generated on {time}.', array(
        '{time}'=>date('Y-m-d H:i:s', $time),
      )); ?></small></p>
    </div>
    <div class="col-lg-3 col-md-4 col-sm-6">
      <?php echo CHtml::dropdownList('statistics', '', array_combine(array_map(function($statistic) {
        return $statistic['id'];
      }, $statistics), array_map(function($name) {
        return Yii::t('statistics', $name);
      }, array_keys($statistics))), array(
        'class'=>'form-control',
      )); ?>
    </div>
    <div class="clearfix"></div>
    <?php $i = 0; ?>
    <?php foreach ($statistics as $name=>$statistic): ?>
    <div class="<?php echo $statistic['class']; ?>" id= "<?php echo $statistic['id']; ?>">
    <?php if (isset($statistic['columns'])): ?>
      <h3>
        <?php echo Yii::t('statistics', $name); ?>
        <?php if (isset($statistic['more'])): ?>
        <small><?php echo CHtml::link(Yii::t('common', 'more') . Html::fontAwesome('angle-double-right', 'b'), $statistic['more'], array('class'=>'more')); ?></small>
        <?php endif; ?>
      </h3>
      <?php $this->renderPartial('statistic', array(
        'statistic'=>$statistic,
        'id'=>$statistic['id'],
        'class'=>' ' . $statistic['id'],
      )); ?>
    <?php else: ?>
      <h3 class="pull-left">
        <?php echo Yii::t('statistics', $name); ?>
      </h3>
      <div class="pull-left stat-select">
        <?php if (isset($statistic['selectHandler'])) {
          $statistic['select'] = array_map(function($name) use($statistic) {
            return $this->evaluateExpression($statistic['selectHandler'], compact('name'));
          }, $statistic['select']);
        }?>
        <?php echo CHtml::dropdownList($statistic['id'], '', $statistic['select'], array(
          'data-key'=>isset($statistic['selectKey']) ? $statistic['selectKey'] : '',
        )); ?>
        <?php if (isset($statistic['more'])): ?>
        <small><?php echo CHtml::link(Yii::t('common', 'more') . Html::fontAwesome('angle-double-right', 'b'), $statistic['more'], array('class'=>'more')); ?></small>
        <?php endif; ?>
      </div>
      <div class="clearfix"></div>
      <?php $keys = array_keys($statistic['statistic']); ?>
      <?php foreach ($statistic['statistic'] as $key=>$stat): ?>
      <?php $this->renderPartial('statistic', array(
        'statistic'=>$stat,
        'id'=>$statistic['id'] . '_' . $key,
        'class'=>($key === $keys[0] ? ' ' : ' hide ') . $statistic['id'],
      )); ?>
      <?php endforeach; ?>
    <?php endif; ?>
    </div>
    <?php if ($statistic['class'] === 'col-md-12'): ?>
    <?php $i = 0; ?>
    <div class="clearfix"></div>
    <?php elseif (++$i % 2 == 0): ?>
    <div class="clearfix hidden-sm"></div>
    <?php endif; ?>
    <?php endforeach; ?>
  </div>
</div>
<?php
Yii::app()->clientScript->registerScript('statistics',
<<<EOT
  $('.stat-select select').on('change', function() {
    var that = $(this),
      value = that.val(),
      name = that.attr('name'),
      key = that.data('key'),
      more = that.parent().find('.more');
    $('.' + name).addClass('hide').filter('#statistic_' + name + '_' + value).removeClass('hide');
    if (key && more.length > 0) {
      var params = {};
      params[key] = value;
      more.attr('href', addParams(more.attr('href'), params));
    }
  }).trigger('change');
  $('select[name="statistics"]').on('change', function() {
    location.href = '#' + $(this).val();
  });
  function addParams(url, params) {
    var oldParams = {};
    url = url.split('?');
    if (url[1]) {
      $.each(url[1].split('&'), function(key, value) {
        value = value.split('=');
        oldParams[value[0]] = value[1] || '';
      });
    }
    $.extend(oldParams, params);
    return url[0] + '?' + $.param(oldParams);
  }
EOT
);
