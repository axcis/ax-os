<?php

/**
 * FaqRegistModel
 * @author takanori_gozu
 *
 */
class FaqRegistModel extends MY_Model {
	
	/**
	 * バリデーション
	 */
	public function validation($input) {
		
		$title = $input['title'];
		$question = $input['question'];
		$answer = $input['answer'];
		
		$msgs = array();
		
		//未入力チェック
		if (trim($title) == '') $msgs[] = $this->lang->line('err_required', array($this->lang->line('title')));
		if (trim($question) == '') $msgs[] = $this->lang->line('err_required', array($this->lang->line('question')));
		if (trim($answer) == '') $msgs[] = $this->lang->line('err_required', array($this->lang->line('answer')));
		
		if ($msgs != null) return $msgs;
		
		//文字列長チェック
		if (mb_strlen(trim($title)) > 50) $msgs[] = $this->lang->line('err_max_length', array($this->lang->line('title'), 50));
		
		return $msgs;
	}
	
	/**
	 * 詳細
	 */
	public function get_info($id) {
		
		$this->set_table(FaqDao::TABLE_NAME, self::DB_TRAN);
		
		$this->add_where(FaqDao::COL_ID, $id);
		
		return $this->do_select_info();
	}
	
	/**
	 * 新規登録
	 */
	public function db_regist($input) {
		
		$this->set_table(FaqDao::TABLE_NAME, self::DB_TRAN);
		
		$this->add_col_val(FaqDao::COL_TITLE, $input['title']);
		$this->add_col_val(FaqDao::COL_QUESTION, $input['question']);
		$this->add_col_val(FaqDao::COL_ANSWER, $input['answer']);
		
		$this->do_insert();
	}
	
	/**
	 * 更新
	 */
	public function db_modify($input) {
		
		$this->set_table(FaqDao::TABLE_NAME, self::DB_TRAN);
		
		$this->add_col_val(FaqDao::COL_TITLE, $input['title']);
		$this->add_col_val(FaqDao::COL_QUESTION, $input['question']);
		$this->add_col_val(FaqDao::COL_ANSWER, $input['answer']);
		
		$this->add_where(FaqDao::COL_ID, $input['id']);
		
		$this->do_update();
	}
	
	/**
	 * 削除
	 */
	public function db_delete($id) {
		
		$this->set_table(FaqDao::TABLE_NAME, self::DB_TRAN);
		
		$this->add_where(FaqDao::COL_ID, $id);
		
		$this->do_delete();
	}
}
?>