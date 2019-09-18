<?php

/**
 * PasswordModifyController
 * @author takanori_gozu
 *
 */
class PasswordModify extends MY_Controller {
	
	/**
	 * Index
	 */
	public function index() {
		
		$this->set('action', 'modify');
		$this->set('delete_disable', '1');
		$this->view('password/password_modify_input');
	}
	
	/**
	 * パスワード更新
	 */
	public function modify() {
		
		$this->load->model('password/PasswordModifyModel', 'model');
		$this->load->library('dao/EmployeeDao');
		
		$input = $this->get_attribute();
		
		$msgs = $this->model->validation($input);
		
		if ($msgs != null) {
			$this->set_err_info($msgs);
			$this->set('delete_disable', '1');
			$this->view('password/password_modify_input');
			return;
		}
		
		$this->model->db_modify($input);
		
		$this->redirect_js(base_url(). 'TopPage');
	}
}
?>