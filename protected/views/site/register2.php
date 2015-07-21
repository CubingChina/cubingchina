<?php
$allCities = Region::getAllCities();
$this->renderPartial('registerSide', $_data_);
?>
<div class="content-wrapper col-md-10 col-sm-9">
  <?php echo CHtml::link(Html::fontAwesome('arrow-left', 'a') . Yii::t('common', 'Previous step'), array('/site/register', 'step'=>1), array('class'=>'btn btn-theme')); ?>
  <h3 class="has-divider text-highlight">
    <?php echo Yii::t('common', 'Step 2. Fill out the following information.'); ?>
  </h3>
  <div class="progress progress-striped active">
    <div class="progress-bar progress-bar-theme" style="width: 66%">
      <span class="sr-only"><?php echo Yii::t('common', 'Step Two'); ?></span>
    </div>
  </div>
  <?php $form = $this->beginWidget('CActiveForm', array(
    'id'=>'register-form',
    'htmlOptions'=>array(
      //'class'=>'form-login',
      'role'=>'form',
    ),
  )); ?>
  <?php echo Html::formGroup(
    $model, 'email', array(),
    $form->labelEx($model, 'email'),
    Html::activeTextField($model, 'email', array('type'=>'email')),
    Yii::app()->language === 'zh_cn' ? '<div class="text-danger">请检查所填写的邮箱正确并有效。QQ邮箱请注意不要带“<strong>www.</strong>”。</div>' : '',
    $form->error($model, 'email', array('class'=>'text-danger'))
  );?>
  <?php echo Html::formGroup(
    $model, 'password', array(),
    $form->labelEx($model, 'password'),
    Html::activeTextField($model, 'password', array('type'=>'password')),
    $form->error($model, 'password', array('class'=>'text-danger'))
  );?>
  <?php echo Html::formGroup(
    $model, 'repeatPassword', array(),
    $form->labelEx($model, 'repeatPassword'),
    Html::activeTextField($model, 'repeatPassword', array('type'=>'password')),
    $form->error($model, 'repeatPassword', array('class'=>'text-danger'))
  );?>
  <?php echo Html::formGroup(
    $model, 'country_id', array(),
    $form->labelEx($model, 'country_id'),
    $form->dropDownList($model, 'country_id', Region::getCountries(), array(
      'class'=>'form-control',
      'readonly'=>$model->wcaid != '',
      'prompt'=>'',
    )),
    $form->error($model, 'country_id', array('class'=>'text-danger'))
  );?>
  <?php echo Html::formGroup(
    $model, 'name', array('id'=>'name'),
    $form->labelEx($model, 'name'),
    Html::activeTextField($model, 'name', array('readonly'=>$model->wcaid != '')),
    Html::tag('div', array(
      'class'=>'hide clearfix',
      'id'=>'name-help',
    ), Html::tag('div', array(
      'class'=>'text-info',
    ), Yii::t('common', 'Please choose the correct English name below'))),
    $form->error($model, 'name', array('class'=>'text-danger'))
  );?>
  <?php echo Html::formGroup(
    $model, 'local_name', array('id'=>'local_name'),
    $form->labelEx($model, 'local_name'),
    Html::activeTextField($model, 'local_name', array('readonly'=>$model->wcaid != '' && $model->local_name != '')),
    Yii::app()->language === 'zh_cn' && ($model->wcaid == '' || $model->local_name == '') ? '<div class="text-danger">请使用真实姓名注册粗饼网并报名比赛。</div>' : '',
    $form->error($model, 'local_name', array('class'=>'text-danger'))
  );?>
  <?php echo Html::formGroup(
    $model, 'gender', array(),
    $form->labelEx($model, 'gender'),
    $form->dropDownList($model, 'gender', User::getGenders(), array(
      'class'=>'form-control',
      'prompt'=>'',
    )),
    $form->error($model, 'gender', array('class'=>'text-danger'))
  );?>
  <?php echo Html::formGroup(
    $model, 'birthday', array(),
    $form->labelEx($model, 'birthday'),
    Html::activeTextField($model, 'birthday', array(
      'class'=>'date-picker',
      'data-date-format'=>'yyyy-mm-dd',
      'placeholder'=>Yii::t('common', 'The format is YYYY-MM-DD'),
    )),
    $form->error($model, 'birthday', array('class'=>'text-danger'))
  );?>
  <?php echo Html::formGroup(
    $model, 'province_id', array(
      'class'=>'hide',
      'id'=>'province',
    ),
    $form->labelEx($model, 'province_id'),
    $form->dropDownList($model, 'province_id', Region::getProvinces(), array(
      'class'=>'form-control',
      'prompt'=>'',
    )),
    $form->error($model, 'province_id', array('class'=>'text-danger'))
  );?>
  <?php echo Html::formGroup(
    $model, 'city_id', array(
      'class'=>'hide',
      'id'=>'city',
    ),
    $form->labelEx($model, 'city_id'),
    $form->dropDownList($model, 'city_id', isset($allCities[$model->province_id]) ? $allCities[$model->province_id] : array(), array(
      'class'=>'form-control',
    )),
    $form->error($model, 'city_id', array('class'=>'text-danger'))
  );?>
  <?php echo Html::formGroup(
    $model, 'mobile', array(),
    $form->labelEx($model, 'mobile'),
    Html::activeTextField($model, 'mobile'),
    $form->error($model, 'mobile', array('class'=>'text-danger'))
  );?>
  <?php echo Html::formGroup(
    $model, 'verifyCode', array(),
    $form->labelEx($model, 'verifyCode'),
    Html::activeTextField($model, 'verifyCode'),
    $this->widget('CCaptcha', array(
      'clickableImage'=>true,
      'showRefreshButton'=>false,
    ), true),
    $form->error($model, 'verifyCode', array('class'=>'text-danger'))
  );?>
  <button type="submit" class="btn btn-theme btn-lg"><?php echo Yii::t('common', 'Register'); ?></button>
  <?php $this->endWidget(); ?>
</div>
<?php
$emailMsg = Yii::t('common', 'Please confirm your email:\\n{email}');
Yii::app()->clientScript->registerCssFile('/f/plugins/bootstrap-datepicker/css/datepicker.css');
Yii::app()->clientScript->registerScriptFile('/f/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js');
Yii::app()->clientScript->registerScriptFile('/f/js/pinyin.min.js');
$allCities = json_encode($allCities);
Yii::app()->clientScript->registerScript('register2',
<<<EOT
  $('.date-picker').datepicker();
  $('[readonly]').prop('disabled', true);
  var allCities = {$allCities};
  $(document)
    .on('change', '#RegisterForm_country_id', toggleCountry)
    .on('change', '#RegisterForm_province_id', function() {
      var city = $('#RegisterForm_city_id'),
        cities = allCities[$(this).val()] || [];
      city.empty();
      $.each(cities, function(id, name) {
        $('<option>').val(id).text(name).appendTo(city);
      });
    })
    .on('submit', 'form', function(e) {
      var email = $('#RegisterForm_email').val();
      var msg = '{$emailMsg}';
      if (!confirm(msg.replace('{email}', email))) {
        e.preventDefault();
        return false;
      }
      $('[readonly]').prop('disabled', false);
    });
  $('label[for="RegisterForm_mobile"]').append('<span class="required">*</span>');
  $('label[for="RegisterForm_local_name"]').append('<span class="required">*</span>');
  $('label[for="RegisterForm_province_id"]').append('<span class="required hide">*</span>');
  $('label[for="RegisterForm_city_id"]').append('<span class="required hide">*</span>');
  //$('#RegisterForm_province_id').trigger('change');
  var compoundSurname = /^(欧阳|太史|端木|上官|司马|东方|独孤|南宫|万俟|闻人|夏侯|诸葛|尉迟|公羊|赫连|澹台|皇甫|宗政|濮阳|公冶|太叔|申屠|公孙|慕容|仲孙|钟离|长孙|宇文|司徒|鲜于|司空|闾丘|子车|亓官|司寇|巫马|公西|颛孙|壤驷|公良|漆雕|乐正|宰父|谷梁|拓跋|夹谷|轩辕|令狐|段干|百里|呼延|东郭|南门|羊舌|微生|公户|公玉|公仪|梁丘|公仲|公上|公门|公山|公坚|左丘|公伯|西门|公祖|第五|公乘|贯丘|公皙|南荣|东里|东宫|仲长|子书|子桑|即墨|达奚|褚师)/;
  var surnamePinyin = {
    '区': ['ou'],
    '仇': ['qiu'],
    '解': ['xie'],
    '折': ['she'],
    '单': ['shan'],
    '朴': ['piao'],
    '翟': ['zhai'],
    '查': ['zha'],
    '盖': ['ge'],
    '卜': ['bu'],
    '曾': ['zeng'],
    '缪': ['miao'],
    '丁': ['ding'],
    '尉迟': ['yu', 'chi'],
    '万俟': ['mo', 'qi']
  }
  String.prototype.ucfirst = function() {
    return this.charAt(0).toUpperCase() + this.substr(1);
  }
  toggleCountry();
  function toggleCountry() {
    var countryId = $('#RegisterForm_country_id').val();
    if (countryId == 1) {
      setPinyin(true);
      $('#province, #city, label[for="RegisterForm_mobile"] span, label[for="RegisterForm_local_name"] span, label[for="RegisterForm_province_id"] span, label[for="RegisterForm_city_id"] span').removeClass('hide');
    } else {
      setPinyin(false);
      $('#province, #city, label[for="RegisterForm_mobile"] span, label[for="RegisterForm_local_name"] span, label[for="RegisterForm_province_id"] span, label[for="RegisterForm_city_id"] span').addClass('hide');
    }
  }
  function setPinyin(enable) {
    var nameDom = $('#RegisterForm_name'),
      localNameDom = $('#RegisterForm_local_name'),
      nameHelpDom = $('#name-help');
    if (!enable) {;
      nameDom.prop('readonly', false);
      localNameDom.off('change keyup');
      $('#name').insertBefore($('#local_name'))
      nameHelpDom.addClass('hide');
    } else {
      $('#name').insertAfter($('#local_name'));
      if (!nameDom.prop('readonly')) {
        nameDom.prop('readonly', true);
        localNameDom.on('change keyup', generatePinyin).trigger('change');
      }
    }
    nameHelpDom.on('change', 'input', function() {
      nameDom.val($(this).val());
    })
    function generatePinyin(event) {
      var localName = localNameDom.val();
      if (event.type === 'change' && !localName.match(/^[\\u4e00-\\u9fc0]+$/)) {
        //todo alert
        localNameDom.val(localName = localName.replace(/[^\\u4e00-\\u9fc0]+/g, ''));
      }
      var pinyin = pinyinUtil.toPinyin(localName);
      $.each(surnamePinyin, function(surname, py) {
        if (localName.substr(0, 1) === surname) {
          pinyin[0] = [py[0]];
          return false;
        }
      });
      var enNames = [];
      if (localName.match(compoundSurname)) {
        if (localName.match(/^(万俟|尉迟)/)) {
          enNames = enNames.concat(getEnNames(
            getNames(2, pinyin.length),
            [surnamePinyin[localName.substr(0, 2)][0].ucfirst() + surnamePinyin[localName.substr(0, 2)][1]]
          ));
        } else {
          enNames = enNames.concat(getEnNames(
            getNames(2, pinyin.length),
            getNames(0, 1)
          ));
        }
      }
      enNames = enNames.concat(getEnNames(
        getNames(1, pinyin.length),
        pinyin[0]
      ));
      // enNames = enNames.concat(getEnNames(getNames(0, pinyin.length), []));
      if (enNames.length > 0) {
        nameHelpDom.removeClass('hide').find('label').remove();
        for (var i = 0; i < enNames.length; i++) {
          $('<label />').attr({
            'for': 'english_name_' + i,
            'class': 'col-md-2 col-sm-4 col-xs-6',
          }).append(
            $('<input type="radio" name="english_name" />').val(enNames[i]).attr({
              id: 'english_name_' + i,
            }),
            enNames[i]
          ).appendTo(nameHelpDom);
        }
        if (nameHelpDom.find('input[value="' + nameDom.val() + '"]').length > 0) {
          nameHelpDom.find('input[value="' + nameDom.val() + '"]').prop('checked', true);
        } else {
          nameHelpDom.find('input:first').trigger('change').prop('checked', true);
        }
      } else {
        nameHelpDom.addClass('hide');
      }
      function getNames(level, max) {
        if (level === pinyin.length || level > max) {
          return [];
        }
        var a = pinyin[level];
        var b = getNames(level + 1, max);
        if (b.length == 0) {
          return a;
        }
        var names = [];
        for (var i = 0; i < a.length; i++) {
          for (var j = 0; j < b.length; j++) {
            names.push(a[i] + b[j]);
          }
        }
        return names;
      }
      function getEnNames(names, surnames) {
        var enNames = [];
        for (var i = 0; i < names.length; i++) {
          if (surnames.length === 0) {
            enNames.push(names[i].ucfirst());
            continue;
          }
          for (var j = 0; j < surnames.length; j++) {
            enNames.push(names[i].ucfirst() + ' ' + surnames[j].ucfirst());
          }
        }
        return enNames;
      }
    }
  }
EOT
);