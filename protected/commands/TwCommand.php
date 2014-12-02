<?php

class TwCommand extends CConsoleCommand {
	public function actionIndex() {
		$files = CFileHelper::findFiles(Yii::getPathOfAlias('application.messages.zh_cn'));
		include Yii::getPathOfAlias('application.data') . '/ZhConversion.php';
		$path = Yii::getPathOfAlias('application.messages.zh_tw');
		foreach ($files as $file) {
			if (basename($file) === 'event.php') {
				continue;
			}
			$content = file_get_contents($file);
			$content = strtr($content, $zh2Hant);
			file_put_contents($path . '/'. basename($file), $content);
		}
	}
}
