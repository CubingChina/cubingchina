<?php

class Widget extends CWidget {
	public function getViewPath($checkTheme = false) {
		if(($module = $this->getController()->getModule()) === null) {
			$module = Yii::app();
		}
		return $module->getViewPath() . DIRECTORY_SEPARATOR . 'widgets';
	}
}