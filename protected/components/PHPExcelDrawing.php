<?php

class PHPExcelDrawing extends \PhpOffice\PhpSpreadsheet\Worksheet\Drawing {
	private $_replacedImageIndex;

	public function getImageIndex() {
		if ($this->_replacedImageIndex !== null) {
			return $this->_replacedImageIndex;
		}
		return parent::getImageIndex();
	}

	public function setImageIndex($imageIndex) {
		$this->_replacedImageIndex = $imageIndex;
	}
}