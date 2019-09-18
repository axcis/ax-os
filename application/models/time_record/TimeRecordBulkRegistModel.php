<?php

/**
 * TimeRecordBulkRegistModel
 * @author takanori_gozu
 *
 */
class TimeRecordBulkRegistModel extends TimeRecordBaseModel {
	
	/**
	 * 一括登録
	 * 仕様
	 * ・平日のみ(土日祝日は一括登録除外)
	 * ・区分は出勤で登録
	 * ・提出先は共通で登録
	 * ・時間は設定テーブルに設定されている値を固定で登録する
	 * ・データ登録済(本社・現場・共通いずれかで)の場合は除外する
	 */
	public function bulk_regist($month, $employee_id) {
		
		$data = array(); //一括登録用配列
		
		//月情報を取得
		$start = new DateTime(date('Y-m-d', strtotime('first day of '. $month)));
		$end = new DateTime(date('Y-m-d', strtotime('first day of next month '. $month)));
		$interval = new DateInterval('P1D');
		$daterange = new DatePeriod($start, $interval, $end);
		
		//祝祭日マスタを取得
		$holiday_map = $this->get_holiday_map($month);
		
		//設定情報を先に取得しておく
		$config = $this->get_config_info($employee_id);
		if ($config == null) return 'error'; //設定情報がなければ一括登録できないので終了
		
		foreach ($daterange as $date) {
			$key = $date->format('Y-m-d');
			if (array_key_exists($key, $holiday_map) !== false) continue; //祝祭日の場合は次の日付へ
			
			$weekday = self::WEEKDAY_JP[$date->format('w')];
			if ($weekday == '土' || $weekday == '日') continue; //土日の場合は次の日付へ
			
			$data_count = $this->get_data_count($key, $employee_id);
			if ($data_count > 0) continue; //対象日付でデータが登録済みの場合は次の日付へ(上書きしない)
			
			//登録用データを作成
			$this->make_bulk_data($data, $key, $employee_id, $config);
		}
		
		if ($data != null) {
			$this->set_table(TimeRecordDao::TABLE_NAME, self::DB_TRAN);
			$this->do_bulk_insert($data);
		}
	}
	
	/**
	 * 祝祭日マスタ
	 */
	private function get_holiday_map($month) {
		
		$this->set_table(HolidayDao::TABLE_NAME, self::DB_MASTER);
		$this->add_select(HolidayDao::COL_HOLIDAY_DATE);
		$this->add_select(HolidayDao::COL_HOLIDAY_NAME);
		$this->add_where(HolidayDao::COL_MONTH, $month);
		
		return $this->key_value_map($this->do_select(), 'holiday_date', 'holiday_name');
	}
	
	/**
	 * 設定情報取得
	 */
	private function get_config_info($employee_id) {
		
		$this->set_table(TimeRecordConfigDao::TABLE_NAME, self::DB_TRAN);
		
		$this->add_where(TimeRecordConfigDao::COL_EMPLOYEE_ID, $employee_id);
		
		return $this->do_select_info();
	}
	
	/**
	 * 登録データ件数を取得
	 */
	private function get_data_count($date, $employee_id) {
		
		$this->set_table(TimeRecordDao::TABLE_NAME, self::DB_TRAN);
		
		$this->add_where(TimeRecordDao::COL_EMPLOYEE_ID, $employee_id);
		$this->add_where(TimeRecordDao::COL_WORK_DATE, $date);
		
		return $this->do_count();
	}
	
	/**
	 * 一括登録用データを作成
	 */
	private function make_bulk_data(&$data, $date, $employee_id, $config) {
		
		$start_time = $config[TimeRecordConfigDao::COL_START_TIME];
		$end_time = $config[TimeRecordConfigDao::COL_END_TIME];
		$break_time = $config[TimeRecordConfigDao::COL_BREAK_TIME];
		$midnight_break_time = $config[TimeRecordConfigDao::COL_MIDNIGHT_BREAK_TIME];
		$prescribed_time = $config[TimeRecordConfigDao::COL_PRESCRIBED_TIME];
		
		//計算
		$work_time = $end_time - $start_time - $break_time - $midnight_break_time;
		$over_work_time = $work_time - $prescribed_time;
		
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
		
		$midnight_over_work_time = $midnight_over_work_time > $midnight_time ? $midnight_time : $midnight_over_work_time;
		
		$data_map = array();
		
		$data_map[TimeRecordDao::COL_EMPLOYEE_ID] = $employee_id;
		$data_map[TimeRecordDao::COL_WORK_DATE] = $date;
		$data_map[TimeRecordDao::COL_SCENE] = '3'; //提出先は3で固定
		$data_map[TimeRecordDao::COL_CLASSIFICATION] = '1'; //区分は出勤で固定
		$data_map[TimeRecordDao::COL_START_TIME] = $start_time;
		$data_map[TimeRecordDao::COL_END_TIME] = $end_time;
		$data_map[TimeRecordDao::COL_BREAK_TIME] = $break_time;
		$data_map[TimeRecordDao::COL_MIDNIGHT_BREAK_TIME] = $midnight_break_time;
		$data_map[TimeRecordDao::COL_PRESCRIBED_TIME] = $prescribed_time;
		$data_map[TimeRecordDao::COL_WORK_TIME] = $work_time;
		$data_map[TimeRecordDao::COL_OVER_WORK_TIME] = $over_work_time <= 0 ? 0 : $over_work_time;
		$data_map[TimeRecordDao::COL_MIDNIGHT_TIME] = $midnight_time <= 0 ? 0 : $midnight_time;
		$data_map[TimeRecordDao::COL_MIDNIGHT_OVER_WORK_TIME] = $midnight_over_work_time <= 0 ? 0 : $midnight_over_work_time;
		
		$data[] = $data_map;
	}
}
?>