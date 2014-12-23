<?php
class NonSortArrayDataProvider extends CArrayDataProvider {
	public $sliceData = true;

	protected function fetchData() {
		if (($pagination = $this->getPagination()) !== false) {
			$pagination->setItemCount($this->getTotalItemCount());
			if ($this->sliceData) {
				return array_slice($this->rawData, $pagination->getOffset() , $pagination->getLimit());
			} else {
				return $this->rawData;
			}
		} else {
			return $this->rawData;
		}
	}
}
