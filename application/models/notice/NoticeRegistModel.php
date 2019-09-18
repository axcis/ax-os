<?php

/**
 * NoticeRegistModel
 * @author takanori_gozu
 *
 */
class NoticeRegistModel extends MY_Model {
	
	/**
	 * 出欠確認マッピング
	 */
	public function get_presence_type_map() {
		
		$map = array();
		
		$map['0'] = '不要';
		$map['1'] = '必要';
		
		return $map;
	}
	
	/**
	 * バリデーション
	 */
	public function validation($input) {
		
		$msgs = array();
		
		$notice_name = $input['notice_name'];
		$notice_detail = $input['notice_detail'];
		$published_date = $input['published_date'];
		$presence_chk_flg = $input['presence_chk_flg'];
		$presence_date = $presence_chk_flg == '1' ? $input['presence_date'] : '';
		
		$msgs = array();
		
		//未入力・未選択チェック
		if (trim($notice_name) == '') $msgs[] = $this->lang->line('err_required', array($this->lang->line('notice_name')));
		if (trim($notice_detail) == '') $msgs[] = $this->lang->line('err_required', array($this->lang->line('detail')));
		if (trim($published_date) == '') $msgs[] = $this->lang->line('err_not_select', array($this->lang->line('published_date')));
		
		if ($presence_chk_flg == '1') {
			if (trim($presence_date) == '') $msgs[] = $this->lang->line('err_not_select', array($this->lang->line('presence_date')));
		}
		
		if ($msgs != null) return $msgs;
		
		if (mb_strlen(trim($notice_name)) > 100) $msgs[] = $this->lang->line('err_max_length', array($this->lang->line('notice_name'), 100));
		
		if ($msgs != null) return $msgs;
		
		//日付の整合性チェック
		if ($presence_chk_flg == '1') {
			//出欠確認期限が掲載期日よりあとは登録不可
			if ($published_date < $presence_date) $msgs[] = $this->lang->line('err_select_date_before', array($this->lang->line('presence_date'), $this->lang->line('published_date')));
		}
		
		return $msgs;
	}
	
	/**
	 * 新規登録
	 */
	public function db_regist($input) {
		
		$this->set_table(NoticeDao::TABLE_NAME, self::DB_TRAN);
		
		$this->add_col_val(NoticeDao::COL_NOTICE_NAME, $input['notice_name']);
		$this->add_col_val(NoticeDao::COL_NOTICE_DETAIL, $input['notice_detail']);
		$this->add_col_val(NoticeDao::COL_IMPORTANT, $input['important']);
		$this->add_col_val(NoticeDao::COL_REGIST_DATE, date('Y-m-d'));
		$this->add_col_val(NoticeDao::COL_PUBLISHED_DATE, $input['published_date']);
		$this->add_col_val(NoticeDao::COL_PRESENCE_CHK_FLG, $input['presence_chk_flg']);
		if ($input[NoticeDao::COL_PRESENCE_CHK_FLG] == '1') {
			$this->add_col_val(NoticeDao::COL_PRESENCE_DATE, $input['presence_date']);
		}
		
		return $this->do_insert_get_id();
	}
	
	/**
	 * Allメール送信
	 */
	public function send_mail($input) {
		
		$this->load->model('common/SendMailModel', 'mail');
		$this->mail->init($this->lang->line('notice_mail_config'));
		
		$important_map = $this->get_important_map();
		
		$presence_date = ($input['presence_chk_flg'] == '1') ? date('Y年n月j日', strtotime($input['presence_date'])) : '';
		
		$replace = array($input['notice_name'],
				$input['notice_detail'],
				$important_map[$input['important']],
				date('Y年n月j日', strtotime($input['published_date'])),
				$presence_date);
		
		$from = $this->lang->line('notice_mail_from');
		$to = $this->lang->line('notice_mail_to');
		$cc = $this->lang->line('notice_mail_cc');
		$subject = $this->lang->line('notice_subject');
		$message = $this->lang->line('notice_msg', $replace);
		
		$this->mail->from($from);
		$this->mail->to($to);
		$this->mail->cc($cc);
		$this->mail->subject($subject);
		$this->mail->message($message);
		
		$this->mail->send();
	}
	
	/**
	 * 詳細
	 */
	public function get_info($id) {
		
		$this->set_table(NoticeDao::TABLE_NAME, self::DB_TRAN);
		
		$this->add_select(NoticeDao::COL_ID);
		$this->add_select(NoticeDao::COL_NOTICE_NAME);
		$this->add_select(NoticeDao::COL_NOTICE_DETAIL);
		$this->add_select(NoticeDao::COL_IMPORTANT);
		$this->add_select(NoticeDao::COL_PUBLISHED_DATE);
		$this->add_select(NoticeDao::COL_PRESENCE_CHK_FLG);
		$this->add_select(NoticeDao::COL_PRESENCE_DATE);
		
		$this->add_where(NoticeDao::COL_ID, $id);
		
		return $this->do_select_info();
	}
	
	/**
	 * 更新
	 */
	public function db_modify($input) {
		
		$this->set_table(NoticeDao::TABLE_NAME, self::DB_TRAN);
		
		$this->add_col_val(NoticeDao::COL_NOTICE_NAME, $input['notice_name']);
		$this->add_col_val(NoticeDao::COL_NOTICE_DETAIL, $input['notice_detail']);
		$this->add_col_val(NoticeDao::COL_IMPORTANT, $input['important']);
		$this->add_col_val(NoticeDao::COL_PUBLISHED_DATE, $input['published_date']);
		$this->add_col_val(NoticeDao::COL_PRESENCE_CHK_FLG, $input['presence_chk_flg']);
		if ($input[NoticeDao::COL_PRESENCE_CHK_FLG] == '1') {
			$this->add_col_val(NoticeDao::COL_PRESENCE_DATE, $input['presence_date']);
		}
		
		$this->add_where(NoticeDao::COL_ID, $input['id']);
		
		$this->do_update();
	}
}
?>