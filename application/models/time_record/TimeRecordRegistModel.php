<?php

/**
 * TimeRecordRegistModel
 * @author takanori_gozu
 *
 */
class TimeRecordRegistModel extends TimeRecordBaseModel {
	
	/**
	 * 詳細
	 */
	public function get_info($date, $scene, $employee_id) {
		
		$this->set_table(TimeRecordDao::TABLE_NAME, self::DB_TRAN);
		
		$this->add_select(TimeRecordDao::COL_WORK_DATE);
		$this->add_select(TimeRecordDao::COL_EMPLOYEE_ID);
		$this->add_select(TimeRecordDao::COL_SCENE);
		$this->add_select(TimeRecordDao::COL_CLASSIFICATION);
		$this->add_select(TimeRecordDao::COL_START_TIME);
		$this->add_select(TimeRecordDao::COL_END_TIME);
		$this->add_select(TimeRecordDao::COL_BREAK_TIME);
		$this->add_select(TimeRecordDao::COL_MIDNIGHT_BREAK_TIME);
		$this->add_select(TimeRecordDao::COL_PRESCRIBED_TIME);
		$this->add_select(TimeRecordDao::COL_REMARK);
		
		$this->add_where(TimeRecordDao::COL_WORK_DATE, $date);
		$this->add_where(TimeRecordDao::COL_EMPLOYEE_ID, $employee_id);
		$this->add_where_in(TimeRecordDao::COL_SCENE, $scene. ',3');
		
		$info = $this->do_select_info();
		
		//変換
		if ($info != null) {
			foreach ($info as $key => $value) {
				if (strpos($key, 'time') !== false) {
					$info[$key] = $this->int_to_time($value);
				}
			}
		}
		
		return $info;
	}
	
	/**
	 * 設定値取得
	 */
	public function get_config_info() {
		
		$this->set_table(TimeRecordConfigDao::TABLE_NAME, self::DB_TRAN);
		
		$this->add_select(TimeRecordConfigDao::COL_START_TIME);
		$this->add_select(TimeRecordConfigDao::COL_END_TIME);
		$this->add_select(TimeRecordConfigDao::COL_BREAK_TIME);
		$this->add_select(TimeRecordConfigDao::COL_MIDNIGHT_BREAK_TIME);
		$this->add_select(TimeRecordConfigDao::COL_PRESCRIBED_TIME);
		
		$this->add_where(TimeRecordConfigDao::COL_EMPLOYEE_ID, $this->get_session('user_id'));
		
		$info = $this->do_select_info();
		
		//変換
		if ($info != null) {
			foreach ($info as $key => $value) {
				if (strpos($key, 'time') !== false) {
					$info[$key] = $this->int_to_time($value);
				}
			}
		}
		
		return $info;
	}
	
	/**
	 * 入力チェック
	 */
	public function validation($input) {
		
		$action = $input['action'];
		$work_date = $input['work_date'];
		$employee_id = $input['employee_id'];
		$scene = $input['scene'];
		$classification = $input['classification'];
		
		$msgs = array();
		
		switch ($classification) {
			case 1:
			case 2:
				//出勤系
				$this->work_class_check($msgs, $input);
				break;
			case 3:
			case 4:
			case 5:
			case 6:
			case 7:
			case 8:
				//休暇系
				$this->holiday_class_check($msgs, $input);
				break;
		}
		
		if ($action == 'regist') $this->data_found_check($msgs, $scene, $work_date, $employee_id);
		
		return $msgs;
	}
	
	/**
	 * 出勤、休日出勤の入力チェック
	 */
	private function work_class_check(&$msgs, $input) {
		
		$start_time = $input['start_time'];
		$end_time = $input['end_time'];
		$break_time = $input['break_time'];
		$midnight_break_time = $input['midnight_break_time'];
		$prescribed_time = $input['prescribed_time'];
		
		//未入力チェック
		if (trim($start_time) == '') $msgs[] = $this->lang->line('err_required', array($this->lang->line('start_time')));
		if (trim($end_time) == '') $msgs[] = $this->lang->line('err_required', array($this->lang->line('end_time')));
		if (trim($break_time) == '') $msgs[] = $this->lang->line('err_required', array($this->lang->line('break_time')));
		if (trim($midnight_break_time) == '') $msgs[] = $this->lang->line('err_required', array($this->lang->line('midnight_break_time')));
		if (trim($prescribed_time) == '') $msgs[] = $this->lang->line('err_required', array($this->lang->line('prescribed_time')));
		
		if ($msgs != null) return $msgs;
		
		//フォーマットチェック
		if (!$this->time_format_check($start_time, 1)) $msgs[] = $this->lang->line('err_regex_match', array($this->lang->line('start_time')));
		if (!$this->time_format_check($end_time)) $msgs[] = $this->lang->line('err_regex_match', array($this->lang->line('end_time')));
		if (!$this->time_format_check($break_time)) $msgs[] = $this->lang->line('err_regex_match', array($this->lang->line('break_time')));
		if (!$this->time_format_check($midnight_break_time)) $msgs[] = $this->lang->line('err_regex_match', array($this->lang->line('midnight_break_time')));
		if (!$this->time_format_check($prescribed_time)) $msgs[] = $this->lang->line('err_regex_match', array($this->lang->line('prescribed_time')));
		
		if ($msgs != null) return $msgs;
		
		//24時間を超えた入力は不可
		if (!$this->check_24_over($start_time, $end_time)) {
			$msgs[] = $this->lang->line('err_24hour_over');
			return $msgs;
		}
		
		//出勤≧退勤の入力は不可
		if ($this->time_to_int($start_time) >= $this->time_to_int($end_time)) {
			$msgs[] = $this->lang->line('err_bigger', array($this->lang->line('end_time'), $this->lang->line('start_time')));
			return $msgs;
		}
		
		//退勤-出勤-休憩≦0は不可
		$work_time = $this->time_to_int($end_time) - $this->time_to_int($start_time) - $this->time_to_int($break_time) - $this->time_to_int($midnight_break_time);
		if ($work_time <= 0) {
			$msgs[] = $this->lang->line('err_not_integrity', array($this->lang->line('work_time')));
			return $msgs;
		}
		
		//休憩≧所定の入力は不可
		if ($this->time_to_int($break_time) + $this->time_to_int($midnight_break_time) >= $this->time_to_int($prescribed_time)) {
			$msgs[] = $this->lang->line('err_bigger', array($this->lang->line('prescribed_time'), $this->lang->line('break_time'). 'の合計'));
			return $msgs;
		}
	}
	
	/**
	 * 休暇系のチェック
	 */
	private function holiday_class_check(&$msgs, $input) {
		
		if (trim($input['remark']) == '') $msgs[] = $this->lang->line('err_required', array($this->lang->line('remark')));
	}
	
	/**
	 * データ存在チェック
	 */
	private function data_found_check(&$msgs, $scene, $date, $employee_id) {
		
		$this->set_table(TimeRecordDao::TABLE_NAME, self::DB_TRAN);
		$this->add_where(TimeRecordDao::COL_WORK_DATE, $date);
		$this->add_where(TimeRecordDao::COL_EMPLOYEE_ID, $employee_id);
		
		switch ($scene) {
			case '1':
			case '2':
				$this->add_where(TimeRecordDao::COL_SCENE, $scene);
				$count = $this->do_count();
				if ($count > 0) $msgs[] = $this->lang->line('err_already_select_regist', array('提出先'));
				break;
			case '3':
				$this->add_where_in(TimeRecordDao::COL_SCENE, '1,2');
				$count = $this->do_count();
				if ($count > 0) $msgs[] = $this->lang->line('err_data_found', array('本社用または現場用'));
				break;
		}
	}
	
	/**
	 * フォーマット変換・計算を実施してDB登録用のMapを生成
	 */
	public function get_time_record_map($input) {
		
		$classification = $input['classification'];
		
		$map = array();
		
		$map[TimeRecordDao::COL_WORK_DATE] = $input['work_date'];
		$map[TimeRecordDao::COL_EMPLOYEE_ID] = $input['employee_id'];
		$map[TimeRecordDao::COL_SCENE] = $input['scene'];
		$map[TimeRecordDao::COL_CLASSIFICATION] = $classification;
		
		switch ($classification) {
			case 1:
			case 2:
				//出勤系(計算＆セット)
				$this->calc($map, $input);
				break;
			case 3:
			case 4:
			case 5:
			case 6:
			case 7:
			case 8:
				//休暇系(すべてnull)
				$map[TimeRecordDao::COL_START_TIME] = null;
				$map[TimeRecordDao::COL_END_TIME] = null;
				$map[TimeRecordDao::COL_BREAK_TIME] = null;
				$map[TimeRecordDao::COL_MIDNIGHT_BREAK_TIME] = null;
				$map[TimeRecordDao::COL_PRESCRIBED_TIME] = null;
				$map[TimeRecordDao::COL_WORK_TIME] = null;
				$map[TimeRecordDao::COL_OVER_WORK_TIME] = null;
				$map[TimeRecordDao::COL_MIDNIGHT_TIME] = null;
				$map[TimeRecordDao::COL_MIDNIGHT_OVER_WORK_TIME] = null;
				break;
		}
		
		$map[TimeRecordDao::COL_REMARK] = $input['remark'];
		
		return $map;
	}
	
	/**
	 * 時間の計算
	 */
	public function calc(&$map, $input) {
		
		$classification = $input['classification'];
		$start_time = $this->time_to_int($input['start_time']);
		$end_time = $this->time_to_int($input['end_time']);
		$break_time = $this->time_to_int($input['break_time']);
		$midnight_break_time = $this->time_to_int($input['midnight_break_time']);
		$prescribed_time = $this->time_to_int($input['prescribed_time']);
		
		//労働時間
		$work_time = $end_time - $start_time - $break_time - $midnight_break_time;
		
		//残業時間
		if ($classification == 2) {
			$over_work_time = $work_time;
		} else {
			$over_work_time = $work_time - $prescribed_time;
		}
		
		//深夜時間、深夜残業時間
		$midnight_time = 0;
		$midnight_over_work_time = 0;
		$ordinary_time = 0;
		$over_ordinary = false;
		
		for ($i = $start_time; $i < $end_time; $i++) {
			if ($i < 300 || ($i >= 1320 && $i < 1740) || $i >= 2760){
				$midnight_time++;
				if ($over_ordinary) $midnight_over_work_time++;
			}
			
			//定時時間を越えたか判定
			if (!$over_ordinary) {
				$ordinary_time++;
				if ($ordinary_time >= ($prescribed_time + $break_time + $midnight_break_time)) {
					$over_ordinary = true;
				}
			}
		}
		
		$midnight_time -= $midnight_break_time;
		
		if ($classification == 2) {
			$midnight_over_work_time = $midnight_time;
		} else {
			$midnight_over_work_time = $midnight_over_work_time > $midnight_time ? $midnight_time : $midnight_over_work_time;
		}
		
		$map[TimeRecordDao::COL_START_TIME] = $start_time;
		$map[TimeRecordDao::COL_END_TIME] = $end_time;
		$map[TimeRecordDao::COL_BREAK_TIME] = $break_time;
		$map[TimeRecordDao::COL_MIDNIGHT_BREAK_TIME] = $midnight_break_time;
		$map[TimeRecordDao::COL_PRESCRIBED_TIME] = $prescribed_time;
		$map[TimeRecordDao::COL_WORK_TIME] = $work_time;
		$map[TimeRecordDao::COL_OVER_WORK_TIME] = $over_work_time <= 0 ? 0 : $over_work_time;
		$map[TimeRecordDao::COL_MIDNIGHT_TIME] = $midnight_time <= 0 ? 0 : $midnight_time;
		$map[TimeRecordDao::COL_MIDNIGHT_OVER_WORK_TIME] = $midnight_over_work_time <= 0 ? 0 : $midnight_over_work_time;
	}
	
	/**
	 * 新規登録
	 */
	public function db_regist($calc_map) {
		
		$this->set_table(TimeRecordDao::TABLE_NAME, self::DB_TRAN);
		
		$this->add_col_val(TimeRecordDao::COL_EMPLOYEE_ID, $calc_map[TimeRecordDao::COL_EMPLOYEE_ID]);
		$this->add_col_val(TimeRecordDao::COL_WORK_DATE, $calc_map[TimeRecordDao::COL_WORK_DATE]);
		$this->add_col_val(TimeRecordDao::COL_SCENE, $calc_map[TimeRecordDao::COL_SCENE]);
		$this->add_col_val(TimeRecordDao::COL_CLASSIFICATION, $calc_map[TimeRecordDao::COL_CLASSIFICATION]);
		$this->add_col_val(TimeRecordDao::COL_START_TIME, $calc_map[TimeRecordDao::COL_START_TIME]);
		$this->add_col_val(TimeRecordDao::COL_END_TIME, $calc_map[TimeRecordDao::COL_END_TIME]);
		$this->add_col_val(TimeRecordDao::COL_BREAK_TIME, $calc_map[TimeRecordDao::COL_BREAK_TIME]);
		$this->add_col_val(TimeRecordDao::COL_PRESCRIBED_TIME, $calc_map[TimeRecordDao::COL_PRESCRIBED_TIME]);
		$this->add_col_val(TimeRecordDao::COL_OVER_WORK_TIME, $calc_map[TimeRecordDao::COL_OVER_WORK_TIME]);
		$this->add_col_val(TimeRecordDao::COL_MIDNIGHT_TIME, $calc_map[TimeRecordDao::COL_MIDNIGHT_TIME]);
		$this->add_col_val(TimeRecordDao::COL_MIDNIGHT_BREAK_TIME, $calc_map[TimeRecordDao::COL_MIDNIGHT_BREAK_TIME]);
		$this->add_col_val(TimeRecordDao::COL_MIDNIGHT_OVER_WORK_TIME, $calc_map[TimeRecordDao::COL_MIDNIGHT_OVER_WORK_TIME]);
		$this->add_col_val(TimeRecordDao::COL_WORK_TIME, $calc_map[TimeRecordDao::COL_WORK_TIME]);
		$this->add_col_val(TimeRecordDao::COL_REMARK, $calc_map[TimeRecordDao::COL_REMARK]);
		
		$this->do_insert();
	}
	
	/**
	 * 更新
	 */
	public function db_modify($calc_map) {
		
		$this->set_table(TimeRecordDao::TABLE_NAME, self::DB_TRAN);
		
		$this->add_col_val(TimeRecordDao::COL_CLASSIFICATION, $calc_map[TimeRecordDao::COL_CLASSIFICATION]);
		$this->add_col_val(TimeRecordDao::COL_START_TIME, $calc_map[TimeRecordDao::COL_START_TIME]);
		$this->add_col_val(TimeRecordDao::COL_END_TIME, $calc_map[TimeRecordDao::COL_END_TIME]);
		$this->add_col_val(TimeRecordDao::COL_BREAK_TIME, $calc_map[TimeRecordDao::COL_BREAK_TIME]);
		$this->add_col_val(TimeRecordDao::COL_PRESCRIBED_TIME, $calc_map[TimeRecordDao::COL_PRESCRIBED_TIME]);
		$this->add_col_val(TimeRecordDao::COL_OVER_WORK_TIME, $calc_map[TimeRecordDao::COL_OVER_WORK_TIME]);
		$this->add_col_val(TimeRecordDao::COL_MIDNIGHT_TIME, $calc_map[TimeRecordDao::COL_MIDNIGHT_TIME]);
		$this->add_col_val(TimeRecordDao::COL_MIDNIGHT_BREAK_TIME, $calc_map[TimeRecordDao::COL_MIDNIGHT_BREAK_TIME]);
		$this->add_col_val(TimeRecordDao::COL_MIDNIGHT_OVER_WORK_TIME, $calc_map[TimeRecordDao::COL_MIDNIGHT_OVER_WORK_TIME]);
		$this->add_col_val(TimeRecordDao::COL_WORK_TIME, $calc_map[TimeRecordDao::COL_WORK_TIME]);
		$this->add_col_val(TimeRecordDao::COL_REMARK, $calc_map[TimeRecordDao::COL_REMARK]);
		
		$this->add_where(TimeRecordDao::COL_EMPLOYEE_ID, $calc_map[TimeRecordDao::COL_EMPLOYEE_ID]);
		$this->add_where(TimeRecordDao::COL_WORK_DATE, $calc_map[TimeRecordDao::COL_WORK_DATE]);
		$this->add_where(TimeRecordDao::COL_SCENE, $calc_map[TimeRecordDao::COL_SCENE]);
		
		$this->do_update();
	}
	
	/**
	 * 削除
	 */
	public function db_delete($input) {
		
		$this->set_table(TimeRecordDao::TABLE_NAME, self::DB_TRAN);
		
		$this->add_where(TimeRecordDao::COL_EMPLOYEE_ID, $input[TimeRecordDao::COL_EMPLOYEE_ID]);
		$this->add_where(TimeRecordDao::COL_WORK_DATE, $input[TimeRecordDao::COL_WORK_DATE]);
		$this->add_where(TimeRecordDao::COL_SCENE, $input[TimeRecordDao::COL_SCENE]);
		
		$this->do_delete();
	}
	
	/**
	 * 社員名(管理者用)
	 */
	public function get_employee_name($id) {
		
		$this->set_table(EmployeeDao::TABLE_NAME, self::DB_MASTER);
		
		$this->add_select(EmployeeDao::COL_NAME);
		$this->add_where(EmployeeDao::COL_ID, $id);
		
		$info = $this->do_select_info();
		
		return $info[EmployeeDao::COL_NAME];
	}
}
?>