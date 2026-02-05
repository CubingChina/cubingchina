<?php

class m250301_000000_create_h2h_tables extends CDbMigration {
	public function up() {
		// 创建 live_h2h_round 表：存储 H2H 轮次信息
		$this->createTable('live_h2h_round', array(
			'id' => 'INT(10) UNSIGNED NOT NULL AUTO_INCREMENT',
			'competition_id' => 'INT(10) UNSIGNED NOT NULL',
			'event' => 'VARCHAR(6) NOT NULL DEFAULT ""',
			'round' => 'CHAR(1) NOT NULL DEFAULT ""',
			'places' => 'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 COMMENT "4, 8, 12, or 16"',
			'stage' => 'VARCHAR(20) NOT NULL DEFAULT "" COMMENT "Stage of 16, Stage of 12, Quarterfinal, Semifinal, Final"',
			'sets_to_win' => 'TINYINT(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT "X: number of sets to win (1, 2, or 3)"',
			'points_to_win_set' => 'TINYINT(1) UNSIGNED NOT NULL DEFAULT 3 COMMENT "Points to win a set"',
			'status' => 'TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT "0: not started, 1: in progress, 2: finished"',
			'operator_id' => 'INT(10) UNSIGNED NOT NULL DEFAULT 0',
			'create_time' => 'INT(10) UNSIGNED NOT NULL DEFAULT 0',
			'update_time' => 'INT(10) UNSIGNED NOT NULL DEFAULT 0',
			'PRIMARY KEY (`id`)',
			'KEY `competition_event_round` (`competition_id`, `event`, `round`)',
		), 'ENGINE=InnoDB DEFAULT CHARSET=utf8');

		// 创建 live_h2h_match 表：存储 match 信息
		$this->createTable('live_h2h_match', array(
			'id' => 'INT(10) UNSIGNED NOT NULL AUTO_INCREMENT',
			'h2h_round_id' => 'INT(10) UNSIGNED NOT NULL',
			'competition_id' => 'INT(10) UNSIGNED NOT NULL',
			'event' => 'VARCHAR(6) NOT NULL DEFAULT ""',
			'round' => 'CHAR(1) NOT NULL DEFAULT ""',
			'stage' => 'VARCHAR(20) NOT NULL DEFAULT ""',
			'sets_to_win' => 'TINYINT(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT "X: number of sets to win (1, 2, or 3)"',
			'match_number' => 'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0',
			'competitor1_id' => 'INT(10) UNSIGNED NOT NULL DEFAULT 0',
			'competitor1_seed' => 'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0',
			'competitor2_id' => 'INT(10) UNSIGNED NOT NULL DEFAULT 0',
			'competitor2_seed' => 'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0',
			'competitor1_sets_won' => 'TINYINT(1) UNSIGNED NOT NULL DEFAULT 0',
			'competitor2_sets_won' => 'TINYINT(1) UNSIGNED NOT NULL DEFAULT 0',
			'winner_id' => 'INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT "0: not finished, >0: winner user_id"',
			'status' => 'TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT "0: not started, 1: in progress, 2: finished"',
			'operator_id' => 'INT(10) UNSIGNED NOT NULL DEFAULT 0',
			'create_time' => 'INT(10) UNSIGNED NOT NULL DEFAULT 0',
			'update_time' => 'INT(10) UNSIGNED NOT NULL DEFAULT 0',
			'PRIMARY KEY (`id`)',
			'KEY `h2h_round_id` (`h2h_round_id`)',
			'KEY `competition_event_round` (`competition_id`, `event`, `round`)',
		), 'ENGINE=InnoDB DEFAULT CHARSET=utf8');

		// 创建 live_h2h_set 表：存储 set 信息
		$this->createTable('live_h2h_set', array(
			'id' => 'INT(10) UNSIGNED NOT NULL AUTO_INCREMENT',
			'match_id' => 'INT(10) UNSIGNED NOT NULL',
			'set_number' => 'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0',
			'competitor1_points' => 'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0',
			'competitor2_points' => 'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0',
			'winner_id' => 'INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT "0: not finished, >0: winner user_id"',
			'status' => 'TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT "0: not started, 1: in progress, 2: finished"',
			'operator_id' => 'INT(10) UNSIGNED NOT NULL DEFAULT 0',
			'create_time' => 'INT(10) UNSIGNED NOT NULL DEFAULT 0',
			'update_time' => 'INT(10) UNSIGNED NOT NULL DEFAULT 0',
			'PRIMARY KEY (`id`)',
			'KEY `match_id` (`match_id`)',
		), 'ENGINE=InnoDB DEFAULT CHARSET=utf8');

		// 创建 live_h2h_point 表：存储 point 信息（每次还原）
		$this->createTable('live_h2h_point', array(
			'id' => 'INT(10) UNSIGNED NOT NULL AUTO_INCREMENT',
			'set_id' => 'INT(10) UNSIGNED NOT NULL',
			'match_id' => 'INT(10) UNSIGNED NOT NULL',
			'point_number' => 'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0',
			'competitor1_id' => 'INT(10) UNSIGNED NOT NULL DEFAULT 0',
			'competitor2_id' => 'INT(10) UNSIGNED NOT NULL DEFAULT 0',
			'competitor1_result' => 'INT(11) NOT NULL DEFAULT 0 COMMENT "Time in centiseconds, -1 for DNF, -2 for DNS"',
			'competitor2_result' => 'INT(11) NOT NULL DEFAULT 0 COMMENT "Time in centiseconds, -1 for DNF, -2 for DNS"',
			'winner_id' => 'INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT "0: no winner (tie), >0: winner user_id"',
			'status' => 'TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT "0: not started, 1: inspection, 2: solving, 3: finished"',
			'operator_id' => 'INT(10) UNSIGNED NOT NULL DEFAULT 0',
			'create_time' => 'INT(10) UNSIGNED NOT NULL DEFAULT 0',
			'update_time' => 'INT(10) UNSIGNED NOT NULL DEFAULT 0',
			'PRIMARY KEY (`id`)',
			'KEY `set_id` (`set_id`)',
			'KEY `match_id` (`match_id`)',
		), 'ENGINE=InnoDB DEFAULT CHARSET=utf8');

		// 在 live_event_round 表中添加 is_h2h 字段
		$this->addColumn('live_event_round', 'is_h2h', 'TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT "1: Head to Head round"');

		return true;
	}

	public function down() {
		$this->dropTable('live_h2h_point');
		$this->dropTable('live_h2h_set');
		$this->dropTable('live_h2h_match');
		$this->dropTable('live_h2h_round');
		$this->dropColumn('live_event_round', 'is_h2h');
		return true;
	}
}
