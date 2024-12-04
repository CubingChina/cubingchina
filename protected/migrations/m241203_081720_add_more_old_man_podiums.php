<?php

class m241203_081720_add_more_old_man_podiums extends CDbMigration
{
	public $old_man_ages = [
		25, 30, 35, 45
	];
	public function up()
	{
		// freely define a old old old man podium age numbers.
		$this->addColumn('competition', 'podiums_o', "TEXT");
		foreach ($this->old_man_ages as $age) {
			$this->addColumn('competition', 'podiums_o' . $age, 'TINYINT(1) UNSIGNED NOT NULL DEFAULT "0"');
		}
		$this->refreshTableSchema('competition');
	}

	public function down()
	{
		foreach ($this->old_man_ages as $age) {
			$this->dropColumn('competition', 'podiums_o' . $age);
		}
		$this->refreshTableSchema('competition');
		return true;
	}

	/*
	// Use safeUp/safeDown to do migration with transaction
	public function safeUp()
	{
	}

	public function safeDown()
	{
	}
	*/
}
