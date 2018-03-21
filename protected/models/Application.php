<?php
use Ramsey\Uuid\Uuid;

/**
 * This is the model class for table "application".
 *
 * The followings are the available columns in table 'application':
 * @property integer $id
 * @property string $name
 * @property string $name_zh
 * @property string $scopes
 * @property string $key
 * @property string $secret
 * @property integer $status
 * @property integer $create_time
 * @property integer $update_time
 */
class Application extends ActiveRecord {
	const STATUS_DISABLED = 0;
	const STATUS_ENABLED = 1;

	const MAX_TIMESTAMP_RANGE = 3000000;

	public static function getAllStatus() {
		return [
			self::STATUS_DISABLED=>'已禁用',
			self::STATUS_ENABLED=>'已启用',
		];
	}

	public static function getByKey($key) {
		return self::model()->findByAttributes(['key'=>$key]);
	}

	public function getStatusText() {
		$status = self::getAllStatus();
		return isset($status[$this->status]) ? $status[$this->status] : $this->status;
	}

	public function getOperationButton() {
		$buttons = [];
		$buttons[] = CHtml::link('编辑', ['/board/application/edit', 'id'=>$this->id], ['class'=>'btn btn-xs btn-blue btn-square']);
		if (Yii::app()->user->checkRole(User::ROLE_DELEGATE)) {
			switch ($this->status) {
				case self::STATUS_DISABLED:
					$buttons[] = CHtml::link('启用', ['/board/application/enable', 'id'=>$this->id], ['class'=>'btn btn-xs btn-green btn-square']);
					break;
				case self::STATUS_ENABLED:
					$buttons[] = CHtml::link('禁用', ['/board/application/disable', 'id'=>$this->id], ['class'=>'btn btn-xs btn-red btn-square']);
					break;
			}
		}
		return implode(' ',  $buttons);
	}

	public function isDisabled() {
		return $this->status == self::STATUS_DISABLED;
	}

	public function isEnabled() {
		return $this->status == self::STATUS_ENABLED;
	}

	public function hasScope($scope) {
		return in_array($scope, explode(',', $this->scopes));
	}

	public function checkSignature($params) {
		$sign = $params['sign'] ?? '';
		unset($params['sign']);
		ksort($params);
		$str = implode('=', array_map(function($key, $value) {
			return $key . '=' . $value;
		}, array_keys($params), $params));
		$str .= $this->secret;
		$paramsSign = hash('sha256', $str);
		$result =  strtolower($sign) === $paramsSign;
		return $result;
	}

	public function getOrderNo($orderNo) {
		return substr($this->key, 0, 16) . substr(md5($orderNo), 0, 16);
	}

	protected function beforeValidate() {
		if ($this->isNewRecord) {
			$this->key = str_replace('-', '', Uuid::uuid4()->toString());
			$this->secret = str_replace('-', '', Uuid::uuid4()->toString());
		}
		return parent::beforeValidate();
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'application';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		return [
			['name, name_zh, key, secret', 'required'],
			['status, create_time, update_time', 'numerical', 'integerOnly'=>true],
			['name, name_zh', 'length', 'max'=>128],
			['scopes', 'length', 'max'=>1024],
			['key, secret', 'length', 'max'=>32],
			['id, name, name_zh, scopes, key, secret, status, create_time, update_time', 'safe', 'on'=>'search'],
		];
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return [
		];
	}

	/**
	 * @return [customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return [
			'id'=>'ID',
			'name'=>'Name',
			'name_zh'=>'Name Zh',
			'scopes'=>'Scopes',
			'key'=>'Key',
			'secret'=>'Secret',
			'status'=>'Status',
			'create_time'=>'Create Time',
			'update_time'=>'Update Time',
		];
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

		$criteria=new CDbCriteria;

		$criteria->compare('id', $this->id);
		$criteria->compare('name', $this->name, true);
		$criteria->compare('name_zh', $this->name_zh, true);
		$criteria->compare('scopes', $this->scopes, true);
		$criteria->compare('key', $this->key, true);
		$criteria->compare('secret', $this->secret, true);
		$criteria->compare('status', $this->status);
		$criteria->compare('create_time', $this->create_time);
		$criteria->compare('update_time', $this->update_time);

		return new CActiveDataProvider($this, [
			'criteria'=>$criteria,
		]);
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Application the static model class
	 */
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
}
