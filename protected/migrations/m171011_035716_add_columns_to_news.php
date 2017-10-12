<?php

class m171011_035716_add_columns_to_news extends CDbMigration {
	public function up() {
		$this->addColumn('news', 'description', "LONGTEXT NOT NULL");
		$this->addColumn('news', 'description_zh', "LONGTEXT NOT NULL");
		$this->addColumn('news', 'alias', "VARCHAR(128) NOT NULL DEFAULT ''");
		foreach (News::model()->findAll() as $news) {
			$news->formatDate();
			$news->save();
		}
		return true;
	}

	public function down() {
		$this->dropColumn('news', 'description');
		$this->dropColumn('news', 'description_zh');
		$this->dropColumn('news', 'alias');
		return true;
	}
}
