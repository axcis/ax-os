<?php

/**
 * 会議室予約状況テーブル定義ファイル
 * @author takanori_gozu
 *
 */
class ConferenceAppointDao {
	
	const TABLE_NAME = 'conference_appoint';
	
	const COL_ID = 'id';
	const COL_CONFERENCE_ID = 'conference_id';
	const COL_TARGET_DATE = 'target_date';
	const COL_START_TIME = 'start_time';
	const COL_END_TIME = 'end_time';
	const COL_REGIST_USER_ID = 'regist_user_id';
	const COL_PURPOSE = 'purpose';
}
?>