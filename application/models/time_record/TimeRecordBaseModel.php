<?php

/**
 * TimeRecordBaseModel
 * 勤怠の共通モデル
 * @author takanori_gozu
 */
class TimeRecordBaseModel extends MY_Model {
	
	const WEEKDAY_JP = array('日','月','火','水','木','金','土');
	
	/**
	 * 区分のMap
	 * 1…出勤
	 * 2…休日出勤
	 * 3…有休
	 * 4…振休
	 * 5…欠勤
	 * 6…公休
	 * 7…年末年始休暇
	 * 8…夏季休暇
	 */
	public function get_classification_map() {
		
		$map = array();
		
		$map[1] = '出勤';
		$map[2] = '休日出勤';
		$map[3] = '有休';
		$map[4] = '振休';
		$map[5] = '欠勤';
		$map[6] = '公休';
		$map[7] = '年末年始休暇';
		$map[8] = '夏季休暇';
		
		return $map;
	}
	
	/**
	 * 入力タイプ(本社用、現場用)
	 */
	public function get_scene_map($common_set = false) {
		
		$map = array();
		$map[1] = '本社用';
		$map[2] = '現場用';
		if ($common_set) $map[3] = '本社・現場共通';
		
		return $map;
	}
	
	/**
	 * 社員のマッピング
	 */
	public function get_employee_map($no_select_show = true) {
		
		$this->set_table(EmployeeDao::TABLE_NAME, self::DB_MASTER);
		
		$this->add_select(EmployeeDao::COL_ID);
		$this->add_select(EmployeeDao::COL_NAME);
		
		//Adminは除外
		$this->add_where(EmployeeDao::COL_USER_LEVEL, self::LEVEL_ADMINISTRATOR, self::COMP_NOT_EQUAL);
		$this->add_where(EmployeeDao::COL_RETIREMENT, '0'); //退職者は除く
		
		$list = $this->do_select();
		
		$map = array();
		
		if ($no_select_show) {
			$map[''] = '名前を選択';
		}
		$map += $this->key_value_map($list);
		
		return $map;
	}
	
	/**
	 * 年月のマッピング
	 */
	public function get_month_map() {
		
		$this->set_table(TimeRecordDao::TABLE_NAME, self::DB_TRAN);
		
		$this->set_distinct();
		$this->add_select_as('left(work_date, 7)', 'month');
		$this->add_order('month', self::ORDER_DESC);
		
		$list = $this->do_select();
		
		$map = array();
		
		$map[date('Y-m')] = date('Y年n月', strtotime("now"));
		
		for($i = 0; $i < count($list); $i++) {
			$month = $list[$i]['month'];
			$map[$month] = date('Y年n月', strtotime($month));
		}
		
		return $map;
	}
	
	/**
	 * HH:MM→h.mm
	 */
	protected function time_to_hmm($time) {
		
		//まず数値に変換
		$int = $this->time_to_int($time);
		
		if ($int == null) return null;
		
		$abs = false;
		
		if ($int < 0) {
			$abs = true;
			$int = abs($int);
		}
		
		$m = $int % 60;
		$h = ($int - $m) / 60;
		
		//15分単位未満切り捨て
		if ($m >= 0 && $m < 15) {
			$m = 0;
		} elseif ($m >= 15 && $m < 30) {
			$m = 0.25;
		} elseif ($m >= 30 && $m < 45) {
			$m = 0.5;
		} else {
			$m = 0.75;
		}
		
		$hmm = $h + $m;
		
		if ($abs) {
			return (sprintf('-%.2f', $hmm). 'h');
		}
		
		return (sprintf('%.2f', $hmm). 'h');
	}
	
	/**
	 * 数値→HH:MM
	 */
	protected function int_to_time($int) {
		
		if ($int == null) return null;
		
		$m = $int % 60;
		$h = ($int - $m) / 60;
		
		return $h. ':'. sprintf('%02d', $m);
	}
	
	/**
	 * HH:MM→数値
	 */
	protected function time_to_int($time) {
		
		if ($time == null) return null;
		
		$arr = explode(':', $time);
		$h = $arr[0] * 60;
		$m = $arr[1];
		
		return $h + $m;
	}
	
	/**
	 * 時間のフォーマットチェック
	 */
	protected function time_format_check($time, $start = 0) {
		
		if (strpos($time, ':') === false) {
			return false;
		}
		
		$arr = explode(':', $time);
		$h = $arr[0];
		$m = $arr[1];
		
		if (!is_numeric($h) || !is_numeric($m)) {
			return false;
		}
		
		if (!($h >= 0 && $h < 48)) {
			return false;
		}
		
		if (strlen($m) != 2) {
			return false;
		}
		
		if (!($m >= 0 && $m <= 59)) {
			return false;
		}
		
		//出勤時間25時以降はありえないのでエラー
		if ($start == 1) {
			if (!($h >= 0 && $h < 24)) {
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * 24時間を超えた入力になっていないかチェックする
	 */
	protected function check_24_over($start_time, $end_time) {
		
		$start_arr = explode(':', $start_time);
		$end_arr = explode(':', $end_time);
		
		$start_h = $start_arr[0];
		$end_h = $end_arr[0];
		
		if (($start_h + 24) <= $end_h) {
			return false;
		}
		
		return true;
	}
}
?>