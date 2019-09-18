<?php

/**
 * TimeRecordListModel
 * @author takanori_gozu
 *
 */
class TimeRecordListModel extends TimeRecordBaseModel {
	
	/**
	 * 一覧
	 */
	public function get_list($month, $scene, $employee_id) {
		
		$list = array();
		
		$this->make_target_month_list($list, $month);
		
		$this->merge_db_list($list, $month, $scene, $employee_id);
		
		return $list;
	}
	
	/**
	 * 空のリストを生成する
	 */
	private function make_target_month_list(&$list, $month) {
		
		$start = new DateTime(date('Y-m-d', strtotime('first day of '. $month)));
		$end = new DateTime(date('Y-m-d', strtotime('first day of next month '. $month)));
		$interval = new DateInterval('P1D');
		$daterange = new DatePeriod($start, $interval, $end);
		
		//祝祭日マスタを取得
		$this->set_table(HolidayDao::TABLE_NAME, self::DB_MASTER);
		$this->add_select(HolidayDao::COL_HOLIDAY_DATE);
		$this->add_select(HolidayDao::COL_HOLIDAY_NAME);
		$this->add_where(HolidayDao::COL_MONTH, $month);
		
		$holiday_map = $this->key_value_map($this->do_select(), 'holiday_date', 'holiday_name');
		
		foreach ($daterange as $date) {
			$key = $date->format('Y-m-d');
			$weekday = self::WEEKDAY_JP[$date->format('w')];
			if (array_key_exists($key, $holiday_map) !== false) {
				$weekday = '祝'; //固定
			}
			$list[$key] = array('day' => $date->format('j'),
					'week' => $weekday,
					'classification' => '',
					'start_time' => '',
					'end_time' => '',
					'break_time' => '',
					'prescribed_time' => '',
					'over_work_time' => '',
					'midnight_time' => '',
					'midnight_break_time' => '',
					'midnight_over_work_time' => '',
					'work_time' => '',
					'remark' => ''
			);
		}
	}
	
	/**
	 * DB取得値をマージする
	 */
	private function merge_db_list(&$list, $month, $type, $employee_id) {
		
		$db_list = $this->get_db_list($month, $type, $employee_id);
		
		$db_map = $this->list_to_map($db_list, TimeRecordDao::COL_WORK_DATE);
		
		$class_map = $this->get_classification_map();
		
		foreach ($list as $key => $value) {
			if (array_key_exists($key, $db_map)) {
				$list[$key][TimeRecordDao::COL_CLASSIFICATION] = $class_map[$db_map[$key][TimeRecordDao::COL_CLASSIFICATION]];
				$list[$key][TimeRecordDao::COL_START_TIME] = $this->int_to_time($db_map[$key][TimeRecordDao::COL_START_TIME]);
				$list[$key][TimeRecordDao::COL_END_TIME] = $this->int_to_time($db_map[$key][TimeRecordDao::COL_END_TIME]);
				$list[$key][TimeRecordDao::COL_BREAK_TIME] = $this->int_to_time($db_map[$key][TimeRecordDao::COL_BREAK_TIME]);
				$list[$key][TimeRecordDao::COL_PRESCRIBED_TIME] = $this->int_to_time($db_map[$key][TimeRecordDao::COL_PRESCRIBED_TIME]);
				$list[$key][TimeRecordDao::COL_OVER_WORK_TIME] = $this->int_to_time($db_map[$key][TimeRecordDao::COL_OVER_WORK_TIME]);
				$list[$key][TimeRecordDao::COL_MIDNIGHT_TIME] = $this->int_to_time($db_map[$key][TimeRecordDao::COL_MIDNIGHT_TIME]);
				$list[$key][TimeRecordDao::COL_MIDNIGHT_BREAK_TIME] = $this->int_to_time($db_map[$key][TimeRecordDao::COL_MIDNIGHT_BREAK_TIME]);
				$list[$key][TimeRecordDao::COL_MIDNIGHT_OVER_WORK_TIME] = $this->int_to_time($db_map[$key][TimeRecordDao::COL_MIDNIGHT_OVER_WORK_TIME]);
				$list[$key][TimeRecordDao::COL_WORK_TIME] = $this->int_to_time($db_map[$key][TimeRecordDao::COL_WORK_TIME]);
				$list[$key][TimeRecordDao::COL_REMARK] = $db_map[$key][TimeRecordDao::COL_REMARK];
			}
		}
	}
	
	/**
	 * DB値取得
	 */
	private function get_db_list($month, $type, $employee_id) {
		
		$this->set_table(TimeRecordDao::TABLE_NAME, self::DB_TRAN);
		
		$this->add_select(TimeRecordDao::COL_WORK_DATE);
		$this->add_select(TimeRecordDao::COL_CLASSIFICATION);
		$this->add_select(TimeRecordDao::COL_START_TIME);
		$this->add_select(TimeRecordDao::COL_END_TIME);
		$this->add_select(TimeRecordDao::COL_BREAK_TIME);
		$this->add_select(TimeRecordDao::COL_PRESCRIBED_TIME);
		$this->add_select(TimeRecordDao::COL_OVER_WORK_TIME);
		$this->add_select(TimeRecordDao::COL_MIDNIGHT_TIME);
		$this->add_select(TimeRecordDao::COL_MIDNIGHT_BREAK_TIME);
		$this->add_select(TimeRecordDao::COL_MIDNIGHT_OVER_WORK_TIME);
		$this->add_select(TimeRecordDao::COL_WORK_TIME);
		$this->add_select(TimeRecordDao::COL_REMARK);
		
		$this->add_where(TimeRecordDao::COL_EMPLOYEE_ID, $employee_id);
		$this->add_where_like(TimeRecordDao::COL_WORK_DATE, $month, self::WILD_CARD_AFTER);
		$this->add_where_in(TimeRecordDao::COL_SCENE, $type. ',3'); //共通は固定で取ってくる
		
		return $this->do_select();
	}
	
	/**
	 * 一覧項目
	 */
	public function get_list_col() {
		
		$list_col = array();
		
		$list_col[] = array('width' => 50, 'value' => '編集'); //編集
		$list_col[] = array('width' => 70, 'value' => '日付');
		$list_col[] = array('width' => 70, 'value' => '曜日');
		$list_col[] = array('width' => 150, 'value' => '区分');
		$list_col[] = array('width' => 100, 'value' => '出勤');
		$list_col[] = array('width' => 100, 'value' => '退勤');
		$list_col[] = array('width' => 100, 'value' => '休憩');
		$list_col[] = array('width' => 100, 'value' => '所定');
		$list_col[] = array('width' => 100, 'value' => '残業');
		$list_col[] = array('width' => 100, 'value' => '深夜');
		$list_col[] = array('width' => 100, 'value' => '深夜休憩');
		$list_col[] = array('width' => 100, 'value' => '深夜残業');
		$list_col[] = array('width' => 100, 'value' => '労働');
		$list_col[] = array('width' => 300, 'value' => '備考');
		
		return $list_col;
	}
	
	/**
	 * 合計
	 */
	public function get_total_list($month, $type, $employee_id) {
		
		$total_list = array();
		
		$db_list = $this->get_db_list($month, $type, $employee_id);
		
		$total_list['break_time'] = 0;
		$total_list['prescribed_time'] = 0;
		$total_list['over_work_time'] = 0;
		$total_list['midnight_time'] = 0;
		$total_list['midnight_break_time'] = 0;
		$total_list['midnight_over_work_time'] = 0;
		$total_list['work_time'] = 0;
		
		foreach ($db_list as $row) {
			$total_list['break_time'] += $row[TimeRecordDao::COL_BREAK_TIME];
			$total_list['prescribed_time'] += $row[TimeRecordDao::COL_PRESCRIBED_TIME];
			$total_list['over_work_time'] += $row[TimeRecordDao::COL_OVER_WORK_TIME];
			$total_list['midnight_time'] += $row[TimeRecordDao::COL_MIDNIGHT_TIME];
			$total_list['midnight_break_time'] += $row[TimeRecordDao::COL_MIDNIGHT_BREAK_TIME];
			$total_list['midnight_over_work_time'] += $row[TimeRecordDao::COL_MIDNIGHT_OVER_WORK_TIME];
			$total_list['work_time'] += $row[TimeRecordDao::COL_WORK_TIME];
		}
		
		$total_list['break_time'] = $this->int_to_time($total_list['break_time']);
		$total_list['prescribed_time'] = $this->int_to_time($total_list['prescribed_time']);
		$total_list['over_work_time'] = $this->int_to_time($total_list['over_work_time']);
		$total_list['midnight_time'] = $this->int_to_time($total_list['midnight_time']);
		$total_list['midnight_break_time'] = $this->int_to_time($total_list['midnight_break_time']);
		$total_list['midnight_over_work_time'] = $this->int_to_time($total_list['midnight_over_work_time']);
		$total_list['work_time'] = $this->int_to_time($total_list['work_time']);
		
		return $total_list;
	}
	
	/**
	 * リンク
	 */
	public function get_link() {
		
		$link_list = array();
		
		$link_list[] = array('url' => 'time_record/TimeRecordOutput', 'class' => 'far fa-file-alt', 'value' => '出力');
		if ($this->get_session('user_level') > self::LEVEL_ADMINISTRATOR) {
			$link_list[] = array('url' => 'time_record/TimeRecordConfig', 'class' => 'fas fa-user-cog', 'value' => '設定');
		}
		
		return $link_list;
	}
}
?>