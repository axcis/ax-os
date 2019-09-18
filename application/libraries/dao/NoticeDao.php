<?php

/**
 * お知らせテーブル定義ファイル
 * @author takanori_gozu
 *
 */
class NoticeDao {
	
	const TABLE_NAME = 'notice';
	
	const COL_ID = 'id';
	const COL_NOTICE_NAME = 'notice_name';
	const COL_NOTICE_DETAIL = 'notice_detail';
	const COL_IMPORTANT = 'important';
	const COL_REGIST_DATE = 'regist_date';
	const COL_PUBLISHED_DATE = 'published_date';
	const COL_PRESENCE_CHK_FLG = 'presence_chk_flg';
	const COL_PRESENCE_DATE = 'presence_date';
}
?>