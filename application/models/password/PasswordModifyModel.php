<?php

/**
 * PasswordModifyModel
 * @author takanori_gozu
 *
 */
class PasswordModifyModel extends MY_Model {
	
	/**
	 * バリデーション
	 */
	public function validation($input) {
		
		$new_password = $input['new_password'];
		$new_password_confirm = $input['new_password_confirm'];
		
		$msgs = array();
		
		//未入力チェック
		if (trim($new_password) == '') $msgs[] = $this->lang->line('err_required', array($this->lang->line('new_password')));
		if (trim($new_password_confirm) == '') $msgs[] = $this->lang->line('err_required', array($this->lang->line('new_password_confirm')));
		
		if ($msgs != null) return $msgs;
		
		if (mb_strlen($new_password) > 8) $msgs[] = $this->lang->line('err_max_length', array($this->lang->line('new_password'), 8));
		if ($new_password != $new_password_confirm) $msgs[] = $this->lang->line('err_not_match', array($this->lang->line('new_password'), $this->lang->line('new_password_confirm')));
		
		if (!preg_match("/^[a-zA-Z0-9]+$/", $new_password)) $msgs[] = $this->lang->line('err_regex_match', array($this->lang->line('new_password')));
		
		return $msgs;
	}
	
	/**
	 * 更新
	 */
	public function db_modify($input) {
		
		$this->set_table(EmployeeDao::TABLE_NAME, self::DB_MASTER);
		
		$this->add_col_val(EmployeeDao::COL_PASSWORD, password_hash($input['new_password'], PASSWORD_BCRYPT));
		$this->add_col_val(EmployeeDao::COL_UPD_USER_ID, $this->get_session('user_id'));
		$this->add_col_val(EmployeeDao::COL_UPD_USER_NAME, $this->get_session('user_name'));
		$this->add_where(EmployeeDao::COL_ID, $this->get_session('user_id'));
		
		$this->do_update();
	}
}
?>