<?php

/**
 * 勤怠設定テーブル定義ファイル
 * @author takanori_gozu
 *
 */
class TimeRecordConfigDao {
	
	const TABLE_NAME = 'time_record_config';
	
	const COL_EMPLOYEE_ID = 'employee_id';
	const COL_START_TIME = 'start_time';
	const COL_END_TIME = 'end_time';
	const COL_BREAK_TIME = 'break_time';
	const COL_PRESCRIBED_TIME = 'prescribed_time';
	const COL_MIDNIGHT_BREAK_TIME = 'midnight_break_time';
}
?>