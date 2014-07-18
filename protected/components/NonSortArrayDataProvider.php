<?php
class NonSortArrayDataProvider extends CArrayDataProvider {
	protected function fetchData() {
		if (($pagination = $this->getPagination()) !== false) {
			$pagination->setItemCount($this->getTotalItemCount());
			return array_slice($this->rawData, $pagination->getOffset() , $pagination->getLimit());
		} else {
			return $this->rawData;
		}
	}
}
