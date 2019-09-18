<?php

/**
 * TimeRecordConfigModel
 * @author takanori_gozu
 *
 */
class TimeRecordConfigModel extends TimeRecordBaseModel {
	
	/**
	 * 情報取得
	 */
	public function get_info() {
		
		$this->set_table(TimeRecordConfigDao::TABLE_NAME, self::DB_TRAN);
		
		$this->add_select(TimeRecordConfigDao::COL_EMPLOYEE_ID);
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
	 * バリデーション
	 */
	public function validation($input) {
		
		$start_time = $input['start_time'];
		$end_time = $input['end_time'];
		$break_time = $input['break_time'];
		$midnight_break_time = $input['midnight_break_time'];
		$prescribed_time = $input['prescribed_time'];
		
		$msgs = array();
		
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
	 * 新規登録
	 */
	public function db_regist($input) {
		
		$this->set_table(TimeRecordConfigDao::TABLE_NAME, self::DB_TRAN);
		
		$this->add_col_val(TimeRecordConfigDao::COL_EMPLOYEE_ID, $input['employee_id']);
		$this->add_col_val(TimeRecordConfigDao::COL_START_TIME, $this->time_to_int($input['start_time']));
		$this->add_col_val(TimeRecordConfigDao::COL_END_TIME, $this->time_to_int($input['end_time']));
		$this->add_col_val(TimeRecordConfigDao::COL_BREAK_TIME, $this->time_to_int($input['break_time']));
		$this->add_col_val(TimeRecordConfigDao::COL_MIDNIGHT_BREAK_TIME, $this->time_to_int($input['midnight_break_time']));
		$this->add_col_val(TimeRecordConfigDao::COL_PRESCRIBED_TIME, $this->time_to_int($input['prescribed_time']));
		
		$this->do_insert();
	}
	
	/**
	 * 更新
	 */
	public function db_modify($input) {
		
		$this->set_table(TimeRecordConfigDao::TABLE_NAME, self::DB_TRAN);
		
		$this->add_col_val(TimeRecordConfigDao::COL_START_TIME, $this->time_to_int($input['start_time']));
		$this->add_col_val(TimeRecordConfigDao::COL_END_TIME, $this->time_to_int($input['end_time']));
		$this->add_col_val(TimeRecordConfigDao::COL_BREAK_TIME, $this->time_to_int($input['break_time']));
		$this->add_col_val(TimeRecordConfigDao::COL_MIDNIGHT_BREAK_TIME, $this->time_to_int($input['midnight_break_time']));
		$this->add_col_val(TimeRecordConfigDao::COL_PRESCRIBED_TIME, $this->time_to_int($input['prescribed_time']));
		
		$this->add_where(TimeRecordConfigDao::COL_EMPLOYEE_ID, $input['employee_id']);
		
		$this->do_update();
	}
}
?>