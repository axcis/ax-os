<?php

/**
 * TimeRecordOutputModel
 * @author takanori_gozu
 *
 */
class TimeRecordOutputModel extends TimeRecordBaseModel {
	
	/**
	 * 出力対象勤怠一覧を取得
	 */
	public function get_list($input) {
		
		$month = $input['month'];
		$scene = $input['scene'];
		$employee_id = $input['employee_id'];
		
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
			} else {
				$weekday = self::WEEKDAY_JP[$date->format('w')];
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
	 * 合計
	 */
	public function get_total_list($input) {
		
		$month = $input['month'];
		$scene = $input['scene'];
		$employee_id = $input['employee_id'];
		
		$total_list = array();
		
		$db_list = $this->get_db_list($month, $scene, $employee_id);
		
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
	 * 社員名(出力用)
	 */
	public function get_employee_name($id) {
		
		$this->set_table(EmployeeDao::TABLE_NAME, self::DB_MASTER);
		
		$this->add_select(EmployeeDao::COL_NAME);
		$this->add_where(EmployeeDao::COL_ID, $id);
		
		$info = $this->do_select_info();
		
		return str_replace(array(" ", "　"), "", $info[EmployeeDao::COL_NAME]);
	}
	
	/**
	 * 出力
	 */
	public function excel_output($input, $list, $total, $employee_name) {
		
		$this->load->model('common/PHPExcelModel', 'excel');
		
		$this->excel->init();
		
		//ページ設定
		$this->page_set();
		
		//メイン処理
		$row_idx = $this->set_list($input, $list, $employee_name);
		
		//合計
		$this->set_total($total, $row_idx);
		$row_idx++;
		
		//体裁
		$this->format_arrange($row_idx);
		
		$this->excel->set_page_reap();
		
		switch ($input['scene']) {
			case '1':
				//本社用
				$file_name = '本社提出用_';
				break;
			case '2':
				//現場用
				$file_name = '現場提出用_';
				break;
		}
		
		$file_name .= date('Y年n月', strtotime($input['month'])). '勤怠表_'. $employee_name. '.xlsx';
		
		//ダウンロード
		$this->excel->save($file_name);
	}
	
	/**
	 * ページ設定
	 */
	private function page_set() {
		
		$this->excel->set_sheet();
		$this->excel->set_pagesize_A4();
		$this->excel->set_title($this->lang->line('time_sheet_name'));
	}
	
	/**
	 * Excel作成
	 */
	private function set_list($input, $list, $employee_name) {
		
		$this->excel->set_cell_value_A1('A1', $this->lang->line('time_sheet_name'));
		$this->excel->set_cell_value_A1('K3', '所属');
		$this->excel->set_cell_value_A1('A4', date('Y年n月', strtotime($input['month'])));
		$this->excel->set_cell_value_A1('K4', '氏名');
		$this->excel->set_cell_value_A1('L4', $employee_name);
		
		$col = 1;
		
		//一覧の項目名
		$this->excel->set_cell_value_R1C1($col++, 5, '日付');
		$this->excel->set_cell_value_R1C1($col++, 5, '曜日');
		$this->excel->set_cell_value_R1C1($col++, 5, '区分');
		$this->excel->set_cell_value_R1C1($col++, 5, '出勤');
		$this->excel->set_cell_value_R1C1($col++, 5, '退勤');
		$this->excel->set_cell_value_R1C1($col++, 5, '休憩');
		$this->excel->set_cell_value_R1C1($col++, 5, '所定');
		$this->excel->set_cell_value_R1C1($col++, 5, '残業');
		$this->excel->set_cell_value_R1C1($col++, 5, '深夜');
		$this->excel->set_cell_value_R1C1($col++, 5, '深夜休憩');
		$this->excel->set_cell_value_R1C1($col++, 5, '深夜残業');
		$this->excel->set_cell_value_R1C1($col++, 5, '労働');
		$this->excel->set_cell_value_R1C1($col++, 5, '備考');
		
		$row = 6;
		
		//List
		foreach ($list as $date) {
			
			$col = 1;
			
			foreach ($date as $value) {
				$this->excel->set_cell_value_R1C1($col++, $row, $value);
			}
			$row++;
		}
		
		return $row;
	}
	
	/**
	 * 合計
	 */
	private function set_total($total, $row_idx) {
		
		$col = 6;
		$row_idx2 = $row_idx + 1;
		
		$this->excel->set_cell_value_R1C1(1, $row_idx, '合計');
		$this->excel->set_cell_value_R1C1(1, $row_idx2, '総時間');
		
		foreach ($total as $value) {
			$this->excel->set_cell_value_R1C1($col, $row_idx, $value);
			$this->excel->set_cell_value_R1C1($col, $row_idx2, $this->time_to_hmm($value));
			$col++;
		}
	}
	
	/**
	 * 体裁
	 */
	private function format_arrange($row_idx) {
		
		$this->excel->set_margin(0.5, 0.5, 0, 0, 0.5, 0.5);
		
		$this->excel->set_wrap_text('A5:M'. $row_idx);
		
		//横幅
		$this->excel->set_column_width('A', 5);
		$this->excel->set_column_width('B', 5);
		$this->excel->set_column_width('C', 15);
		$this->excel->set_column_width('D', 6);
		$this->excel->set_column_width('E', 6);
		$this->excel->set_column_width('F', 7);
		$this->excel->set_column_width('G', 8);
		$this->excel->set_column_width('H', 7);
		$this->excel->set_column_width('I', 7);
		$this->excel->set_column_width('J', 7);
		$this->excel->set_column_width('K', 7);
		$this->excel->set_column_width('L', 8);
		$this->excel->set_column_width('M', 20);
		
		//連結
		$this->excel->cell_merge('A1:M1');
		$this->excel->cell_merge('L3:M3');
		$this->excel->cell_merge('A4:C4');
		$this->excel->cell_merge('L4:M4');
		$merge = $row_idx - 1;
		$this->excel->cell_merge('A'. $merge. ':E'. $merge);
		$this->excel->cell_merge('A'. $row_idx. ':E'. $row_idx);
		
		//罫線
		$this->excel->set_border('A5:M'. $row_idx);
		
		//着色
		$color_idx = $row_idx - 2;
		$this->excel->set_color('A5:M5', 'FFFF00');
		$this->excel->set_color('D6:E'. $color_idx, '00FFFF');
		$this->excel->set_color('G6:L'. $color_idx, '00FFFF');
		
		//横位置
		$this->excel->set_horizon_align('A1');
		$this->excel->set_horizon_align('L3');
		$this->excel->set_horizon_align('A4');
		$this->excel->set_horizon_align('L4');
		$this->excel->set_horizon_align('A5:M5');
		$this->excel->set_horizon_align('A6:L'. $row_idx);
		
		//縦位置
		$this->excel->set_vertical_align('A5:M'. $row_idx);
		
		//勤務日数、就業時間数
		$this->set_func_area($row_idx);
		
		//押印欄
		$this->set_stamp_area($row_idx);
	}
	
	/**
	 * 勤務日数、就業時間数
	 */
	private function set_func_area(&$row_idx) {
		
		$day_idx = $row_idx + 2;
		$time_idx = $day_idx + 1;
		$count_idx = $row_idx - 2;
		
		$this->excel->set_cell_value_A1('J'. $day_idx, '実出勤日数');
		$this->excel->set_cell_value('L'. $day_idx, '=COUNTA(E6:E'. $count_idx .') & "日"');
		$this->excel->cell_merge('J'. $day_idx. ':K'. $day_idx);
		$this->excel->cell_merge('L'. $day_idx. ':M'. $day_idx);
		
		$this->excel->set_cell_value_A1('J'. $time_idx, '就業時間数');
		$this->excel->set_cell_value('L'. $time_idx, '=L'. $row_idx);
		$this->excel->cell_merge('J'. $time_idx. ':K'. $time_idx);
		$this->excel->cell_merge('L'. $time_idx. ':M'. $time_idx);
		
		$this->excel->set_border('J'. $day_idx. ':M'. $time_idx);
		
		$row_idx += 4;
	}
	
	/**
	 * 押印欄の設定
	 */
	private function set_stamp_area($row_idx) {
		
		$title_idx = $row_idx + 2;
		$this->excel->cell_merge('I'. $title_idx. ':J'. $title_idx);
		$this->excel->cell_merge('K'. $title_idx. ':L'. $title_idx);
		
		$this->excel->set_cell_value_A1('I'. $title_idx, '確認');
		$this->excel->set_cell_value_A1('K'. $title_idx, '承認');
		
		$this->excel->set_horizon_align('I'. $title_idx. ':L'. $title_idx);
		
		$stamp_idx1 = $title_idx + 1;
		$stamp_idx2 = $stamp_idx1 + 3;
		
		$this->excel->cell_merge('I'. $stamp_idx1. ':J'. $stamp_idx2);
		$this->excel->cell_merge('K'. $stamp_idx1. ':L'. $stamp_idx2);
		
		$this->excel->set_border('I'. $title_idx. ':L'. $stamp_idx2);
	}
}
?>