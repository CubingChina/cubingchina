<?php


class CertAc2016 extends ResultCert {

	public $hasParticipations = true;
	public $paddingTop = 0;

	public function getData($type = 'results') {
		if ($type !== 'results') {
			return [];
		}
		$results = Results::model()->with('event', 'round')->findAllByAttributes([
			'competitionId'=>$this->competition->wca_competition_id,
			'personId'=>$this->user->wcaid,
		], [
			'order'=>'event.rank, round.rank DESC',
		]);
		$lastEvent = '';
		$normalRound = $lastRound = 0;
		foreach ($results as $result) {
			if ($result->eventId != $lastEvent) {
				$lastEvent = $result->eventId;
				$lastRound++;
			} else {
				$normalRound++;
			}
		}
		$height = $lastRound * 66 + $normalRound * 34;
		$hasDetail = $height <= 768;
		if (!$hasDetail) {
			$results = $this->splitResults($results, $height);
		} else {
			$results = [$results];
			$this->paddingTop = min(200, (692 - $height) / 2);
		}
		return [
			'results'=>$results,
			'lastRound'=>$lastRound,
			'normalRound'=>$normalRound,
			'hasDetail'=>$hasDetail,
			'height'=>$height,
		];
	}

	public function splitResults($results, $totalHeight) {
		$height = 0;
		$heights = [];
		$maxHeight = min($totalHeight / 2, 768);
		$lastEvent = '';
		$normalRound = $lastRound = 0;
		$temp = [];
		$count = count($results);
		foreach ($results as $i=>$result) {
			if ($result->eventId != $lastEvent) {
				$lastEvent = $result->eventId;
				$heights[] = 66;
				$height += 66;
			} else {
				$heights[] = 34;
				$height += 34;
			}
			if ($height >= $maxHeight || $i >= $count / 2) {
				break;
			}
		}
		while (array_pop($heights) != 66) {
			$i--;
		}
		while ($results[$i]->eventId == $lastEvent) {
			$i++;
			$heights[] = 34;
		}
		$this->paddingTop = (692 - array_sum($heights)) / 2;
		return [
			array_slice($results, 0, $i),
			array_slice($results, $i),
		];
	}

	public function getEventImage($event) {
		static $lastEvent;
		if ($event != $lastEvent) {
			$lastEvent = $event;
		} else {
			return '';
		}
		return CHtml::image('../../images/events/' . $event . '.png', '', ['class'=>'event-image']);
	}

	public function getRoundClass($result) {
		static $lastEvent;
		if ($result->eventId != $lastEvent) {
			$lastEvent = $result->eventId;
		} else {
			return 'normal-round';
		}
		return 'last-round';
	}

	public function getRoundName($round) {
		$name = Rounds::getFullRoundName($round);
		return strtr($name, $this->getRoundTranslations()) . '<br>' . $name;
	}

	public function getRoundTranslations() {
		static $translations;
		if ($translations === null) {
			$translations = include Yii::getPathOfAlias('application.messages.zh_cn') . '/Rounds.php';
		}
		return $translations;
	}
}
