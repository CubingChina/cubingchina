<?php

class MultiLocations extends Widget {
	public $model;
	public $delegates = [];
	public $cities = array();
	public function run() {
		$model = $this->model;
		$cities = $this->cities;
		$locations = $model->locations;
		if (empty($locations)) {
			$location = new CompetitionLocation();
			$locations = array(
				$location->attributes,
			);
		}
		//tab
		echo CHtml::openTag('ul', array(
			'class'=>'nav nav-tabs',
			'role'=>'tablist',
		));
		foreach ($locations as $key=>$location) {
			$index = $key + 1;
			echo CHtml::tag('li', array(
				'class'=>$key == 0 ? 'active' : '',
			), CHtml::tag('a', array(
				'href'=>'#location-' . $index,
				'role'=>'tab',
				'data-toggle'=>'tab',
			), '地址' . $index . ($key == 0 ? '<span class="required">*</span></a>' : '')));
		}
		echo '<li><a id="addLocation"><i class="fa fa-plus"></i> 添加</a></li>';
		echo CHtml::closeTag('ul');
		echo '<div class="text-danger col-lg-12">地址1必填，除非多地点比赛，否则请只填写一个地址，留空即可删去多余地址。</div>';
		//tab content
		echo CHtml::openTag('div', array(
			'class'=>'tab-content locations',
		));
		foreach ($locations as $key=>$location) {
			$index = $key + 1;
			echo CHtml::openTag('div', array(
				'class'=>'tab-pane location' . ($key == 0 ? ' active' : ''),
				'id'=>'location-' . $index,
			));
			if ($model->multi_countries) {
				echo Html::formGroup(
					$model, 'locations[country_id][]', array(
						'class'=>'col-lg-12',
					),
					CHtml::label('国家/地区', false),
					CHtml::dropDownList(CHtml::activeName($model, 'locations[country_id][]'), $location['country_id'], Region::getCountries(), array(
						'class'=>'form-control country',
						// 'prompt'=>'',
					)),
					CHtml::error($model, 'locations.country_id.' . $key, array('class'=>'text-danger'))
				);
			}
			echo Html::formGroup(
				$model, 'locations[province_id][]', array(
					'class'=>'col-lg-6',
				),
				CHtml::label('省份', false),
				CHtml::dropDownList(CHtml::activeName($model, 'locations[province_id][]'), $location['province_id'], Region::getProvinces(false), array(
					'class'=>'form-control province',
					'prompt'=>'',
				)),
				CHtml::error($model, 'locations.province_id.' . $key, array('class'=>'text-danger'))
			);
			echo Html::formGroup(
				$model, 'locations[city_id][]', array(
					'class'=>'col-lg-6',
				),
				CHtml::label('城市', false),
				CHtml::dropDownList(CHtml::activeName($model, 'locations[city_id][]'), $location['city_id'], isset($cities[$location['province_id']]) ? $cities[$location['province_id']] : array(), array(
					'class'=>'form-control city',
					'prompt'=>'',
				)),
				CHtml::error($model, 'locations.city_id.' . $key, array('class'=>'text-danger'))
			);
			if ($model->multi_countries) {
				echo Html::formGroup(
					$model, 'locations[city_name][]', array(
						'class'=>'col-lg-6',
					),
					CHtml::label('英文城市', false),
					CHtml::textField(CHtml::activeName($model, 'locations[city_name][]'), $location['city_name'], array(
						'class'=>'form-control',
					)),
					CHtml::error($model, 'locations.city_name.' . $key, array('class'=>'text-danger'))
				);
				echo Html::formGroup(
					$model, 'locations[city_name_zh][]', array(
						'class'=>'col-lg-6',
					),
					CHtml::label('中文城市', false),
					CHtml::textField(CHtml::activeName($model, 'locations[city_name_zh][]'), $location['city_name_zh'], array(
						'class'=>'form-control',
					)),
					CHtml::error($model, 'locations.city_name_zh.' . $key, array('class'=>'text-danger'))
				);
			}
			echo Html::formGroup(
				$model, 'locations[venue_zh][]', array(
					'class'=>'col-lg-12',
				),
				CHtml::label('中文地址', false),
				CHtml::textField(CHtml::activeName($model, 'locations[venue_zh][]'), $location['venue_zh'], array(
					'class'=>'form-control',
				)),
				CHtml::error($model, 'locations.venue_zh.' . $key, array('class'=>'text-danger'))
			);
			echo Html::formGroup(
				$model, 'locations[venue][]', array(
					'class'=>'col-lg-12',
				),
				CHtml::label('英文地址', false),
				CHtml::textField(CHtml::activeName($model, 'locations[venue][]'), $location['venue'], array(
					'class'=>'form-control',
				)),
				CHtml::error($model, 'locations.venue.' . $key, array('class'=>'text-danger'))
			);
			if ($model->multi_countries) {
				echo Html::formGroup(
					$model, 'locations[delegate_id][]', array(
						'class'=>'col-lg-12',
					),
					CHtml::label('代表', false),
					CHtml::dropDownList(CHtml::activeName($model, 'locations[delegate_id][]'), $location['delegate_id'], $this->delegates, array(
						'class'=>'form-control delegate',
						'prompt'=>'',
					)),
					CHtml::error($model, 'locations.delegate_id.' . $key, array('class'=>'text-danger'))
				);
				echo Html::formGroup(
					$model, 'locations[delegate_text][]', array(
						'class'=>'col-lg-12',
					),
					CHtml::label('手写代表', false),
					CHtml::textField(CHtml::activeName($model, 'locations[delegate_text][]'), $location['delegate_text'], array(
						'class'=>'form-control',
					)),
					CHtml::error($model, 'locations.delegate_text.' . $key, array('class'=>'text-danger'))
				);
				echo Html::formGroup(
					$model, 'locations[fee][]', array(
						'class'=>'col-lg-12',
					),
					CHtml::label('费用', false),
					CHtml::textField(CHtml::activeName($model, 'locations[fee][]'), $location['fee'], array(
						'class'=>'form-control',
					)),
					CHtml::error($model, 'locations.fee.' . $key, array('class'=>'text-danger'))
				);
			}
			echo CHtml::closeTag('div');
		}
		echo CHtml::closeTag('div');
		Yii::app()->clientScript->registerScript('MultiLocations',
<<<EOT
  $(document).on('click', '#addLocation', function() {
    var location = $('.location:last').clone();
    var index = $('.location').length + 1;
    var tab = $('<a role="tab" data-toggle="tab">').attr('href', '#location-' + index).text('地址' + index);
    location.appendTo($('.locations'));
    location.find('.province').val('').trigger('change');
    location.find('input').val('');
    location.attr('id', 'location-' + index).removeClass('active');
    $('<li>').append(
      tab
    ).insertBefore($('#addLocation').parent());
    tab.tab('show');
  });
EOT
		);
	}
}