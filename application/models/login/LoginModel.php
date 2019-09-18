<?php

/**
 * LoginModel
 * @author takanori_gozu
 *
 */
class LoginModel extends MY_Model {
	
	/**
	 * コンストラクタ
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * ログイン認証チェック
	 */
	public function login_check($input) {

		$msg = array();
		$login_id = $input['login_id'];
		$password = $input['password'];

		if ($login_id == '') {
			$msg[] = $this->lang->line('err_required', array($this->lang->line('login_id')));
		}

		if ($password == '') {
			$msg[] = $this->lang->line('err_required', array($this->lang->line('password')));
		}

		if ($msg != null) return $msg;

		$this->set_table(EmployeeDao::TABLE_NAME, self::DB_MASTER);
		
		$this->add_select_as(EmployeeDao::COL_ID, 'user_id');
		$this->add_select_as(EmployeeDao::COL_NAME, 'user_name');
		$this->add_select(EmployeeDao::COL_LOGIN_ID);
		$this->add_select(EmployeeDao::COL_PASSWORD);
		$this->add_select(EmployeeDao::COL_USER_LEVEL);
		$this->add_select(EmployeeDao::COL_DIVISION_ID);
		
		$this->add_where(EmployeeDao::COL_LOGIN_ID, $login_id);
		$this->add_where(EmployeeDao::COL_RETIREMENT, '0');
		
		$result = $this->do_select_info();
		
		if ($result == null) {
			$msg[] = $this->lang->line('err_not_match', array($this->lang->line('login_id'), $this->lang->line('password')));
			return $msg;
		}
		
		$db_password = $result[EmployeeDao::COL_PASSWORD];
		
		if (!password_verify($password, $db_password)) {
			$msg[] = $this->lang->line('err_not_match', array($this->lang->line('login_id'), $this->lang->line('password')));
			return $msg;
		}
		
		//取得情報をセッションへ
		foreach ($result as $key => $value) {
			$this->set_session($key, $value);
		}
		$this->set_session('is_login', '1'); //認証フラグ

		return null;
	}
}
?>