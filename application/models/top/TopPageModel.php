<?php

/**
 * TopPageModel
 * @author takanori_gozu
 *
 */
class TopPageModel extends MY_Model {
	
	/**
	 * お知らせ一覧
	 */
	public function get_notice_list() {
		
		$this->set_table(NoticeDao::TABLE_NAME, self::DB_TRAN);
		
		$this->add_select(NoticeDao::COL_ID);
		$this->add_select(NoticeDao::COL_NOTICE_NAME);
		$this->add_select(NoticeDao::COL_IMPORTANT);
		$this->add_select(NoticeDao::COL_REGIST_DATE);
		$this->add_select(NoticeDao::COL_PRESENCE_DATE);
		
		$this->add_where(NoticeDao::COL_PUBLISHED_DATE, date('Y-m-d'), self::COMP_GREATER_EQUAL);
		
		$this->add_order(NoticeDao::COL_REGIST_DATE, self::ORDER_DESC);
		
		$list = $this->do_select();
		$important_map = $this->get_important_map();
		
		foreach ($list as &$info) {
			$info['important'] = $important_map[$info['important']];
		}
		
		return $list;
	}
	
	/**
	 * お知らせ一覧の項目
	 */
	public function get_notice_list_col() {
		
		$list_cols = array();
		
		$list_cols[] = array('width' => 350, 'value' => 'タイトル');
		$list_cols[] = array('width' => 120, 'value' => '重要');
		$list_cols[] = array('width' => 150, 'value' => '掲載日');
		$list_cols[] = array('width' => 150, 'value' => '出欠確認期限');
		$list_cols[] = array('width' => 120, 'value' => '詳細');
		
		return $list_cols;
	}
	
	/**
	 * お知らせの詳細
	 */
	public function get_notice_info($id) {
		
		$this->set_table(NoticeDao::TABLE_NAME, self::DB_TRAN);
		
		$this->add_select(NoticeDao::COL_ID);
		$this->add_select(NoticeDao::COL_NOTICE_NAME);
		$this->add_select(NoticeDao::COL_NOTICE_DETAIL);
		$this->add_select(NoticeDao::COL_REGIST_DATE);
		$this->add_select(NoticeDao::COL_PUBLISHED_DATE);
		$this->add_select(NoticeDao::COL_PRESENCE_CHK_FLG);
		$this->add_select(NoticeDao::COL_PRESENCE_DATE);
		
		$this->add_where(NoticeDao::COL_ID, $id);
		
		return $this->do_select_info();
	}
	
	/**
	 * 出欠確認欄の表示制御
	 */
	public function chk_show_presence_area($info) {
		
		//期限切れの場合は表示しない
		if (date('Y-m-d') > $info[NoticeDao::COL_PRESENCE_DATE]) return false;
		
		//出欠確認対象外のお知らせは表示しない
		if ($info[NoticeDao::COL_PRESENCE_CHK_FLG] == '0') return false;
		
		//管理者は表示しない
		if ($this->get_session('user_level') == self::LEVEL_ADMINISTRATOR) return false;
		
		//既に出欠連絡済の場合は表示しない
		$this->set_table(PresenceDao::TABLE_NAME, self::DB_TRAN);
		
		$this->add_where(PresenceDao::COL_NOTICE_ID, $info[NoticeDao::COL_ID]);
		$this->add_where(PresenceDao::COL_EMPLOYEE_ID, $this->get_session('user_id'));
		
		$count = $this->do_count();
		
		if ($count > 0) return false;
		
		return true;
	}
	
	/**
	 * 参加・不参加の登録
	 */
	public function db_regist_presence($input) {
		
		$this->set_table(PresenceDao::TABLE_NAME, self::DB_TRAN);
		
		$this->add_col_val(PresenceDao::COL_NOTICE_ID, $input['id']);
		$this->add_col_val(PresenceDao::COL_EMPLOYEE_ID, $this->get_session('user_id'));
		$this->add_col_val(PresenceDao::COL_ANSWER_DATE, date('Y-m-d'));
		$this->add_col_val(PresenceDao::COL_ATTENDANT, $input['flg']);
		$this->add_col_val(PresenceDao::COL_REASON, $input['reason']);
		
		$this->do_insert();
	}
}
?>