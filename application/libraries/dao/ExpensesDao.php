<?php

/**
 * 経費データテーブル定義ファイル
 * @author takanori_gozu
 *
 */
class ExpensesDao {
	
	const TABLE_NAME = 'expenses';
	
	const COL_ID = 'id';
	const COL_EMPLOYEE_ID = 'employee_id';
	const COL_EXPENSES_YMD = 'expenses_ymd';
	const COL_REGIST_YM = 'regist_ym';
	const COL_INPUT_TYPE = 'input_type';
	const COL_PAY_TYPE = 'pay_type';
	const COL_EXPENSES_TYPE = 'expenses_type';
	const COL_ROUND_TRIP_TYPE = 'round_trip_type';
	const COL_TRANSPORT = 'transport';
	const COL_FROM_PLACE = 'from_place';
	const COL_TO_PLACE = 'to_place';
	const COL_EXPENSES_DETAIL = 'expenses_detail';
	const COL_COST = 'cost';
	const COL_RECEIPT_FILE_NAME = 'receipt_file_name';
}
?>