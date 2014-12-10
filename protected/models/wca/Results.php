<?php

/**
 * This is the model class for table "Results".
 *
 * The followings are the available columns in table 'Results':
 * @property string $id
 * @property string $competitionId
 * @property string $eventId
 * @property string $roundId
 * @property integer $pos
 * @property integer $best
 * @property integer $average
 * @property string $personName
 * @property string $personId
 * @property string $countryCountryId
 * @property string $formatId
 * @property integer $value1
 * @property integer $value2
 * @property integer $value3
 * @property integer $value4
 * @property integer $value5
 * @property string $regionalSingleRecord
 * @property string $regionalAverageRecord
 */
class Results extends ActiveRecord {

	public static $statisticList = array(
		'Sum of all single ranks'=>array(
			'type'=>'single',
			'method'=>'getSumOfRanks',
		),
		'Sum of all average ranks'=>array(
			'type'=>'average',
			'method'=>'getSumOfRanks',
		),
		'Sum of 2x2 to 5x5 single ranks'=>array(
			'type'=>'single',
			'method'=>'getSumOfRanks',
			'eventIds'=>array('222', '333', '444', '555'),
			'class'=>'col-md-6',
		),
		'Sum of 2x2 to 5x5 average ranks'=>array(
			'type'=>'average',
			'method'=>'getSumOfRanks',
			'eventIds'=>array('222', '333', '444', '555'),
			'class'=>'col-md-6',
		),
		'Best "medal collection" of all events'=>array(
			'type'=>'single',
			'method'=>'',
		),
		'Best "medal collection" in each event'=>array(
			'type'=>'single',
			'method'=>'',
		),
		'Appearances in top 100 Chinese competitors\' single results of Rubik\'s Cube'=>array(
			'type'=>'single',
			'method'=>'',
		),
		'Appearances in top 100 Chinese competitors\' average results of Rubik\'s Cube'=>array(
			'type'=>'single',
			'method'=>'',
		),
		'Best Podiums in Rubik\'s Cube event'=>array(
			'type'=>'single',
			'method'=>'',
		),
		'Records set by Chinese competitors'=>array(
			'type'=>'single',
			'method'=>'',
		),
		'Records set in Chinese competitions'=>array(
			'type'=>'single',
			'method'=>'',
		),
		'Oldest Standing of current Chinese records in all events'=>array(
			'type'=>'single',
			'method'=>'',
		),
		'Most Persons in one competition'=>array(
			'type'=>'single',
			'method'=>'',
		),
		'Most competitions by one person'=>array(
			'type'=>'single',
			'method'=>'',
		),
		'Most solves in one competition'=>array(
			'type'=>'single',
			'method'=>'',
		),
		'Most solves per year'=>array(
			'type'=>'single',
			'method'=>'',
		),
	);

	private static $_ranks = array();

	public static function getStatistics() {
		$statistics = array();
		foreach (self::$statisticList as $name=>$statistic) {
			if (is_callable('self::' . $statistic['method'])) {
				$statistics[$name] = self::$statistic['method']($statistic);
			}
		}
		return $statistics;
	}

	public static function getSumOfRanks($statistic) {
		$ranks = self::getRanks($statistic['type']);
		$eventIds = isset($statistic['eventIds']) ? $statistic['eventIds'] : array_keys(Events::getNormalEvents());
		$limit = isset($statistic['limit']) ? $statistic['limit'] : 10;
		$columns = array(
			array(
				'header'=>Yii::t('common', 'Person'),
				'value'=>'Persons::getLinkById($data["personId"])',
				'type'=>'raw',
			),
			array(
				'header'=>Yii::t('common', 'Sum'),
				'name'=>'sum',
			),
		);
		//计算未参赛的项目应该排第几
		$allPenalties = 0;
		foreach ($eventIds as $key=>$eventId) {
			if (!isset($ranks[$eventId])) {
				unset($eventIds[$key]);
				continue;
			}
			$allPenalties += $penalty[$eventId] = count($ranks[$eventId]) + 1;
		}
		//计算每个人的排名
		foreach ($eventIds as $eventId) {
			foreach ($ranks[$eventId] as $personId=>$rank) {
				if(!isset($rankSum[$personId])) {
					$rankSum[$personId] = $allPenalties;
				}
				$rankSum[$personId] += $rank - $penalty[$eventId];
			}
			$columns[] = array(
				'header'=>Yii::t('event', Events::getFullEventName($eventId)),
				'name'=>$eventId,
				'type'=>'raw',
			);
		}
		asort($rankSum);
		$rows = array();
		foreach (array_slice($rankSum, 0, $limit ) as $personId=>$sum) {
			$row = array(
				'personId'=>$personId,
				'sum'=>$sum,
			);
			foreach ($eventIds as $eventId) {
				$row[$eventId] = isset($ranks[$eventId][$personId])
								 ? $ranks[$eventId][$personId]
								 : '<span class="penalty">' . $penalty[$eventId] . '</span>';
			}
			$rows[] = $row;
		}
		return array(
			'columns'=>$columns,
			'rows'=>$rows,
			'class'=>isset($statistic['class']) ? $statistic['class'] : 'col-md-12',
		);
	}

	public static function getRanks($type, $region = 'China') {
		if (isset(self::$_ranks[$type][$region])) {
			return self::$_ranks[$type][$region];
		}
		$command = Yii::app()->wcaDb->createCommand();
		$command->select('eventId, personId, countryRank')->from('Ranks' . ucfirst($type) . ' r');
		if ($region !== '') {
			$command->leftJoin('Persons p', 'r.personId=p.id')->where("p.countryId='{$region}'");
		}
		$ranks = array();
		foreach ($command->queryAll() as $row) {
			$ranks[$row['eventId']][$row['personId']] = $row['countryRank'];
		}
		return self::$_ranks[$type][$region] = $ranks;
	}

	public static function formatTime($result, $eventId, $encode = true) {
		if ($result == -1) {
			return 'DNF';
		}
		if ($result == -2) {
			return 'DNS';
		}
		if ($result == 0) {
			return '';
		}
		if($eventId == '333fm') {
			if ($result > 1000) {
				$time = sprintf('%.2f', $result / 100);
			} else {
				$time = $result;
			}
		} elseif($eventId == '333mbf' || ($eventId == '333mbo' && strlen($result) == 9)) {
			$difference = 99 - substr($result, 0, 2);
			$missed = intval(substr($result, -2));
			$time = self::formatGMTime(substr($result, 3, -2), true);
			$solved = $difference + $missed;
			$attempted = $solved + $missed;
			$time = $solved . '/' . $attempted . ' ' . $time;
		} elseif($eventId == '333mbo') {
			$solved = 99 - substr($result, 1, 2);
			$attempted = intval(substr($result, 3, 2));
			$time = self::formatGMTime(substr($result, -5), true);
			$time = $solved . '/' . $attempted . ' ' . $time;
		} else {
			$msecond = substr($result, -2);
			$second = substr($result, 0, -2);
			$time = self::formatGMTime(intval($second)) . '.' . $msecond;
		}
		if ($encode) {
			$time = CHtml::encode($time);
		}
		return $time;
	}
	
	/**
	 * 
	 * @param int $time 要被格式化的时间
	 * @param boolean $multi 是否是多盲
	 */
	private static function formatGMTime($time, $multi = false) {
		if ($multi && $time == '99999') {
			return 'unknown';
		} else if ($time == 0) {
			return '0';
		}
		return ltrim(gmdate('G:i:s', $time), '0:');
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'Results';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('pos, best, average, value1, value2, value3, value4, value5', 'numerical', 'integerOnly'=>true),
			array('competitionId', 'length', 'max'=>32),
			array('eventId', 'length', 'max'=>6),
			array('roundId, formatId', 'length', 'max'=>1),
			array('personName', 'length', 'max'=>80),
			array('personId', 'length', 'max'=>10),
			array('countryCountryId', 'length', 'max'=>50),
			array('regionalSingleRecord, regionalAverageRecord', 'length', 'max'=>3),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, competitionId, eventId, roundId, pos, best, average, personName, personId, countryCountryId, formatId, value1, value2, value3, value4, value5, regionalSingleRecord, regionalAverageRecord', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return array(
			'id' => Yii::t('Results', 'ID'),
			'competitionId' => Yii::t('Results', 'Competition'),
			'eventId' => Yii::t('Results', 'Event'),
			'roundId' => Yii::t('Results', 'Round'),
			'pos' => Yii::t('Results', 'Pos'),
			'best' => Yii::t('Results', 'Best'),
			'average' => Yii::t('Results', 'Average'),
			'personName' => Yii::t('Results', 'Person Name'),
			'personId' => Yii::t('Results', 'Person'),
			'countryCountryId' => Yii::t('Results', 'Country'),
			'formatId' => Yii::t('Results', 'Format'),
			'value1' => Yii::t('Results', 'Value1'),
			'value2' => Yii::t('Results', 'Value2'),
			'value3' => Yii::t('Results', 'Value3'),
			'value4' => Yii::t('Results', 'Value4'),
			'value5' => Yii::t('Results', 'Value5'),
			'regionalSingleRecord' => Yii::t('Results', 'Regional Single Record'),
			'regionalAverageRecord' => Yii::t('Results', 'Regional Average Record'),
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * Typical usecase:
	 * - Initialize the model fields with values from filter form.
	 * - Execute this method to get CActiveDataProvider instance which will filter
	 * models according to data in model fields.
	 * - Pass data provider to CGridView, CListView or any similar widget.
	 *
	 * @return CActiveDataProvider the data provider that can return the models
	 * based on the search/filter conditions.
	 */
	public function search() {
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria = new CDbCriteria;

		$criteria->compare('id',$this->id,true);
		$criteria->compare('competitionId',$this->competitionId,true);
		$criteria->compare('eventId',$this->eventId,true);
		$criteria->compare('roundId',$this->roundId,true);
		$criteria->compare('pos',$this->pos);
		$criteria->compare('best',$this->best);
		$criteria->compare('average',$this->average);
		$criteria->compare('personName',$this->personName,true);
		$criteria->compare('personId',$this->personId,true);
		$criteria->compare('countryCountryId',$this->countryCountryId,true);
		$criteria->compare('formatId',$this->formatId,true);
		$criteria->compare('value1',$this->value1);
		$criteria->compare('value2',$this->value2);
		$criteria->compare('value3',$this->value3);
		$criteria->compare('value4',$this->value4);
		$criteria->compare('value5',$this->value5);
		$criteria->compare('regionalSingleRecord',$this->regionalSingleRecord,true);
		$criteria->compare('regionalAverageRecord',$this->regionalAverageRecord,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * @return CDbConnection the database connection used for this class
	 */
	public function getDbConnection() {
		return Yii::app()->wcaDb;
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Results the static model class
	 */
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
}
