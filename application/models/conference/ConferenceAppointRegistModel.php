<?php

/**
 * ConferenceAppointRegistModel
 * @author takanori_gozu
 * 
 */
class ConferenceAppointRegistModel extends ConferenceAppointBaseModel {
	
	/**
	 * 時間のマッピング(9時から22時までの30分単位)
	 */
	public function get_time_map() {
		
		$start = 540;
		$end = 1320;
		$map = array();
		
		for ($i = $start; $i <= $end; $i += 30) {
			$map[$i] = $this->int_to_time($i);
		}
		
		return $map;
	}
	
	/**
	 * バリデーション
	 */
	public function validation($input) {
		
		$target_date = $input['target_date'];
		$conference_id = $input['conference_id'];
		$start_time = $input['start_time'];
		$end_time = $input['end_time'];
		$purpose = $input['purpose'];
		
		$msgs = array();
		
		//未入力・未選択
		if ($target_date == '') $msgs[] = $this->lang->line('err_not_select', array($this->lang->line('target_date')));
		if ($purpose == '') $msgs[] = $this->lang->line('err_required', array($this->lang->line('purpose')));
		
		if (mb_strlen($purpose) > 50) $msgs[] = $this->lang->line('err_max_length', array($this->lang->line('purpose'), 50));
		
		if ($msgs != null) return $msgs;
		
		//時間整合性チェック
		if ($start_time >= $end_time) {
			$msgs[] = $this->lang->line('err_select_time_before', array($this->lang->line('start_time2'), $this->lang->line('end_time2')));
		}
		
		if ($msgs != null) return $msgs;
		
		//登録済みデータ存在チェック
		$this->set_table(ConferenceAppointDao::TABLE_NAME, self::DB_TRAN);
		
		$this->add_select(ConferenceAppointDao::COL_START_TIME);
		$this->add_select(ConferenceAppointDao::COL_END_TIME);
		$this->add_where(ConferenceAppointDao::COL_TARGET_DATE, $target_date);
		$this->add_where(ConferenceAppointDao::COL_CONFERENCE_ID, $conference_id);
		
		if ($input['action'] == 'modify') {
			$this->add_where(ConferenceAppointDao::COL_ID, $input[ConferenceAppointDao::COL_ID], self::COMP_NOT_EQUAL);
		}
		
		$result = $this->do_select();
		
		foreach ($result as $row) {
			if (($end_time > $row[ConferenceAppointDao::COL_START_TIME] && $start_time < $row[ConferenceAppointDao::COL_END_TIME]) ||
				($start_time < $row[ConferenceAppointDao::COL_END_TIME] && $end_time > $row[ConferenceAppointDao::COL_START_TIME])) {
				$msgs[] = $this->lang->line('err_already_regist', array('時間帯'));
				break;
			}
		}
		
		return $msgs;
	}
	
	/**
	 * 詳細
	 */
	public function get_info($id) {
		
		$this->set_table(ConferenceAppointDao::TABLE_NAME, self::DB_TRAN);
		
		$this->add_select(ConferenceAppointDao::COL_ID);
		$this->add_select(ConferenceAppointDao::COL_CONFERENCE_ID);
		$this->add_select(ConferenceAppointDao::COL_TARGET_DATE);
		$this->add_select(ConferenceAppointDao::COL_START_TIME);
		$this->add_select(ConferenceAppointDao::COL_END_TIME);
		$this->add_select(ConferenceAppointDao::COL_PURPOSE);
		
		$this->add_where(ConferenceAppointDao::COL_ID, $id);
		
		return $this->do_select_info();
	}
	
	/**
	 * 新規登録
	 */
	public function db_regist($input) {
		
		$this->set_table(ConferenceAppointDao::TABLE_NAME, self::DB_TRAN);
		
		$this->add_col_val(ConferenceAppointDao::COL_CONFERENCE_ID, $input['conference_id']);
		$this->add_col_val(ConferenceAppointDao::COL_TARGET_DATE, $input['target_date']);
		$this->add_col_val(ConferenceAppointDao::COL_START_TIME, $input['start_time']);
		$this->add_col_val(ConferenceAppointDao::COL_END_TIME, $input['end_time']);
		$this->add_col_val(ConferenceAppointDao::COL_REGIST_USER_ID, $this->get_session('user_id'));
		$this->add_col_val(ConferenceAppointDao::COL_PURPOSE, $input['purpose']);
		
		return $this->do_insert_get_id(); //登録IDを返す
	}
	
	/**
	 * 予約通知メールを送信する
	 */
	public function send_mail($input) {
		
		$this->load->model('common/SendMailModel', 'mail');
		$this->mail->init($this->lang->line('conference_appoint_mail_config'));
		
		$conference_map = $this->get_conference_map();
		
		$replace = array($this->get_session('user_name'),
				date('Y年n月j日', strtotime($input['target_date'])),
				$conference_map[$input['conference_id']],
				$this->int_to_time($input['start_time']),
				$this->int_to_time($input['end_time']),
				$input['purpose']);
		
		$this->mail->from($this->lang->line('conference_appoint_mail_from'));
		$this->mail->to($this->lang->line('conference_appoint_mail_to'));
		$this->mail->cc($this->lang->line('conference_appoint_mail_cc'));
		$this->mail->subject($this->lang->line('conference_appoint_subject'));
		$this->mail->message($this->lang->line('conference_appoint_msg', $replace));
		
		$this->mail->send();
	}
	
	/**
	 * 更新
	 */
	public function db_modify($input) {
		
		$this->set_table(ConferenceAppointDao::TABLE_NAME, self::DB_TRAN);
		
		$this->add_col_val(ConferenceAppointDao::COL_CONFERENCE_ID, $input['conference_id']);
		$this->add_col_val(ConferenceAppointDao::COL_TARGET_DATE, $input['target_date']);
		$this->add_col_val(ConferenceAppointDao::COL_START_TIME, $input['start_time']);
		$this->add_col_val(ConferenceAppointDao::COL_END_TIME, $input['end_time']);
		$this->add_col_val(ConferenceAppointDao::COL_PURPOSE, $input['purpose']);
		
		$this->add_where(ConferenceAppointDao::COL_ID, $input['id']);
		
		$this->do_update();
	}
	
	/**
	 * 削除
	 */
	public function db_delete($id) {
		
		$this->set_table(ConferenceAppointDao::TABLE_NAME, self::DB_TRAN);
		
		$this->add_where(ConferenceAppointDao::COL_ID, $id);
		
		$this->do_delete();
	}
}
?>