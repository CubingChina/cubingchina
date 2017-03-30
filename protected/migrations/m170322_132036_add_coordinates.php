<?php

class m170322_132036_add_coordinates extends CDbMigration {
	public function up() {
		$this->addColumn('region', 'longitude', "DECIMAL(12,9) NOT NULL DEFAULT '0' AFTER `pid`");
		$this->addColumn('region', 'latitude', "DECIMAL(12,9) NOT NULL DEFAULT '0' AFTER `longitude`");

		$data = file_get_contents(APP_PATH . '/protected/data/coordinates.xml');
		$xml = new SimpleXMLElement($data);
		$cities = [];
		$regionIds = [];
		$condition = 'pid>=198';
		$matchedRegions = [];
		$unmatchedRegions = [];
		foreach ($xml->Province as $province) {
			foreach ($province->City as $city) {
				if (in_array($this->getAttribute($city), ['港澳台', '北京市', '天津市', '上海市', '重庆市'])) {
					foreach ($city->District as $key=>$district) {
						$name = $this->getAttribute($district);
						$region = Region::model()->findByAttributes([
							'name_zh'=>$name,
						], $condition) ?: Region::model()->findByAttributes([
							'name_zh'=>mb_substr($name, 0, -1, 'UTF-8'),
						], $condition);
						if ($region === null) {
							$unmatchedRegions[] = $district;
						} else {
							$regionIds[] = $region->id;
							$matchedRegions[] = [
								'region'=>$region,
								'xml'=>$district,
							];
						}
					}
				} else {
					$first = $city->District[0];
					$name = $this->getAttribute($first);
					if (strpos($name, '地区') !== false) {
						$name = mb_substr($name, 0, -2, 'UTF-8');
					} elseif (strpos($name, '自治') !== false) {
						$name = mb_substr($name, 0, -3, 'UTF-8');
					} else {
						$name = mb_substr($name, 0, -1, 'UTF-8');
					}
					$condition = $name == '朝阳' ? 'pid>217' : 'pid>=198';
					$region = Region::model()->findByAttributes([
						'name_zh'=>$name,
					], $condition);
					if ($region === null) {
						$unmatchedRegions[] = $first;
					} else {
						$regionIds[] = $region->id;
						$matchedRegions[] = [
							'region'=>$region,
							'xml'=>$first,
						];
					}
				}
			}
		}
		$criteria = new CDbCriteria();
		$criteria->compare('pid', '>=198');
		$criteria->addNotInCondition('id', $regionIds);
		$regions = Region::model()->findAll($criteria);
		foreach ($unmatchedRegions as $key=>$unmatchedRegion) {
			$name = $this->getAttribute($unmatchedRegion);
			foreach ($regions as $k=>$region) {
				if (strpos($name, $region->name_zh) === 0) {
					$matchedRegions[] = [
						'region'=>$region,
						'xml'=>$first,
					];
					unset($regions[$k]);
					break;
				}
			}
		}
		foreach ($matchedRegions as $matchedRegion) {
			extract($matchedRegion);
			$region->longitude = (float)$this->getAttribute($xml, 'Lon');
			$region->latitude = (float)$this->getAttribute($xml, 'Lat');
			$region->save();
		}
		return true;
	}

	public function down() {
		$this->dropColumn('region', 'longitude');
		$this->dropColumn('region', 'latitude');
		return true;
	}

	private function getAttribute($dom, $name = 'Name') {
		return $dom->attributes()[$name] . "";
	}
}
