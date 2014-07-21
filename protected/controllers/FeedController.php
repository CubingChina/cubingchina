<?php
use \Suin\RSSWriter\Feed;
use \Suin\RSSWriter\Channel;
use \Suin\RSSWriter\Item;

class FeedController extends Controller {
	protected $logAction = false;

	public function accessRules() {
		return array(
			array(
				'allow',
				'users'=>array('*'),
			),
		);
	}

	public function actionIndex() {
		$competitions = Competition::getPublicCompetitions(100);
		$this->renderRss($competitions);
	}

	public function actionUpcoming() {
		$competitions = Competition::getUpcomingRegistrableCompetitions(1000);
		$this->renderRss(array_reverse($competitions));
	}

	private function renderRss($competitions) {
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
		$baseUrl = Yii::app()->request->getBaseUrl(true);
		$feed = new Feed();
		$channel = new Channel();
		$channel
			->title(Yii::t('common', 'Cubing China'))
			->description(Yii::t('common', Yii::app()->params->description))
			->url($baseUrl)
			->language(Yii::app()->language)
			->copyright('Copyright ' . date('Y') . ', Cubing China')
			->pubDate(isset($competitions[0]) ? $competitions[0]->date : time())
			->lastBuildDate(time())
			->ttl(300)
			->appendTo($feed);
		foreach ($competitions as $competition) {
			$item = new Item();
			$item
				->title($competition->getAttributeValue('name'))
				->description($competition->getAttributeValue('information'))
				->url($baseUrl . CHtml::normalizeUrl($competition->getUrl('detail')))
				->pubDate($competition->date)
				->guid(CHtml::normalizeUrl($competition->getUrl('detail')), true)
				->appendTo($channel);
		}
		header('Content-Type:application/xml');
		echo $feed;
	}
}