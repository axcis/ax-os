<?php

/**
 * 勤怠管理テーブル定義ファイル
 * @author takanori_gozu
 *
 */
class TimeRecordDao {
	
	const TABLE_NAME = 'time_record';
	
	const COL_EMPLOYEE_ID = 'employee_id';
	const COL_WORK_DATE = 'work_date';
	const COL_SCENE = 'scene';
	const COL_CLASSIFICATION = 'classification';
	const COL_START_TIME = 'start_time';
	const COL_END_TIME = 'end_time';
	const COL_BREAK_TIME = 'break_time';
	const COL_PRESCRIBED_TIME = 'prescribed_time';
	const COL_OVER_WORK_TIME = 'over_work_time';
	const COL_MIDNIGHT_TIME = 'midnight_time';
	const COL_MIDNIGHT_BREAK_TIME = 'midnight_break_time';
	const COL_MIDNIGHT_OVER_WORK_TIME = 'midnight_over_work_time';
	const COL_WORK_TIME = 'work_time';
	const COL_REMARK = 'remark';
}
?>