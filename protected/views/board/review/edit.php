<div class="row">
  <div class="col-lg-12">
    <div class="page-title">
      <h1><?php echo $model->isNewRecord ? '新增' : '编辑'; ?>新闻</h1>
    </div>
  </div>
  <!-- /.col-lg-12 -->
</div>
<div class="row">
  <div class="col-lg-12">
    <div class="portlet portlet-default">
      <div class="portlet-heading">
          <div class="portlet-title">
              <h4>新闻信息</h4>
          </div>
          <div class="clearfix"></div>
      </div>
      <div class="panel-collapse collapse in">
          <div class="portlet-body">
            <?php $form = $this->beginWidget('ActiveForm', array(
              'htmlOptions'=>array(
                'class'=>'clearfix row',
              ),
              'enableClientValidation'=>true,
            )); ?>
            <?php echo Html::formGroup(
              $model, 'date', array(
                'class'=>'col-lg-6',
              ),
              $form->labelEx($model, 'date', array(
                'label'=>'时间',
              )),
              Html::activeTextField($model, 'date', array(
                'class'=>'datetime-picker',
                'data-date-format'=>'yyyy-mm-dd hh:ii:ss',
              )),
              $form->error($model, 'date', array('class'=>'text-danger'))
            );?>
            <?php echo Html::formGroup(
              $model, 'competition_id', array(
                'class'=>'col-lg-6',
              ),
              $form->labelEx($model, 'competition_id', array(
                'label'=>'比赛',
              )),
              $form->dropDownList($model, 'competition_id', Competition::getRegistrationCompetitions(), array(
                'class'=>'form-control',
                'prompt'=>'',
              )),
              $form->error($model, 'competition_id', array('class'=>'text-danger'))
            );?>
            <div class="clearfix"></div>
            <?php echo Html::formGroup(
              $model, 'organizer_id', array(
                'class'=>'col-lg-6',
              ),
              $form->labelEx($model, 'organizer_id', array(
                'label'=>'主办方',
              )),
              $form->hiddenField($model, 'organizer_id'),
              CHtml::textField('', '', array(
                'class'=>'form-control tokenfield',
                'placeholder'=>'输入名字或拼音',
              )),
              $form->error($model, 'organizer_id', array('class'=>'text-danger'))
            ); ?>
            <?php echo Html::formGroup(
              $model, 'rank', array(
                'class'=>'col-lg-6',
              ),
              $form->labelEx($model, 'rank', array(
                'label'=>'评级',
              )),
              $form->dropDownList($model, 'rank', Review::getRanks(), array(
                'class'=>'form-control',
                'prompt'=>'',
              )),
              $form->error($model, 'rank', array('class'=>'text-danger'))
            );?>
            <div class="clearfix"></div>
            <?php echo Html::formGroup(
              $model, 'comments', array(
                'class'=>'col-lg-12',
              ),
              $form->labelEx($model, 'comments', array(
                'label'=>'备注',
              )),
              $form->textArea($model, 'comments', array(
                'class'=>'editor form-control',
                'rows'=>6,
              )),
              $form->error($model, 'comments', array('class'=>'text-danger'))
            );?>
            <div class="col-lg-12">
              <button type="submit" class="btn btn-default btn-square"><?php echo Yii::t('common', 'Submit'); ?></button>
            </div>
            <?php $this->endWidget(); ?>
          </div>
        </div>
    </div>
  </div>
</div>
<?php
Yii::app()->clientScript->registerCssFile('/b/css/plugins/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css');
Yii::app()->clientScript->registerCssFile('/b/css/plugins/bootstrap-tokenfield/tokenfield-typeahead.min.css');
Yii::app()->clientScript->registerCssFile('/b/css/plugins/bootstrap-tokenfield/bootstrap-tokenfield.min.css');
Yii::app()->clientScript->registerScriptFile('/b/js/plugins/bootstrap-datetimepicker/bootstrap-datetimepicker.min.js');
Yii::app()->clientScript->registerScriptFile('/b/js/plugins/bootstrap-tokenfield/bootstrap-tokenfield.min.js');
Yii::app()->clientScript->registerScriptFile('/b/js/plugins/bootstrap-tokenfield/typeahead.bundle.min.js');
$tokens = json_encode(array_map(function($organizer) {
  return array(
    'value'=>$organizer->id . '-' . $organizer->name_zh,
    'label'=>$organizer->name_zh,
  );
}, $model->organizer ? array($model->organizer) : array()));
Yii::app()->clientScript->registerScript('review',
<<<EOT
  $('.datetime-picker').datetimepicker({
    autoclose: true
  });
  $(document).on('keydown', '.token-input', function(e) {
    if (e.which == 13) {
      e.preventDefault();
    }
  });
  $.ajax({
    url: '/board/review/users',
    dataType: 'json',
    success: function(data) {
      var users = data.data.users;
      var datum = data.data.datum;
      var engine = new Bloodhound({
        local: datum,
        datumTokenizer: function(d) {
          return d.full.split('');
        },
        queryTokenizer: function(d) {
          return d.split('');
        }
      });
      engine.initialize();
      $('.tokenfield').tokenfield({
        tokens: {$tokens},
        typeahead: [
          null,
          {
            source: engine.ttAdapter()
          }
        ]
      }).on('tokenfield:createtoken', function(e) {
        var id = e.attrs.value.split('-')[0];
        if (!users[id] || users[id] != e.attrs.value.split('-')[1]) {
          e.preventDefault();
        }
        //防止重复的
        if ($(this).tokenfield('getTokens').length > 0) {
          e.preventDefault();
        }
        if (e.attrs.value == e.attrs.label) {
          e.attrs.label = e.attrs.value.split('-')[1];
        }
      }).on('tokenfield:createdtoken', function(e) {
        $('#Review_organizer_id').val(e.attrs.value.split('-')[0]);
      }).on('tokenfield:removedtoken', function(e) {
        $('#Review_organizer_id').val(0);
      }).on('tokenfield:edittoken', function(e) {
        e.preventDefault();
      });
    }
  });
EOT
);