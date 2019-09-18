<?php

/**
 * LoginController
 * @author takanori_gozu
 *
 */
class Login extends MY_Controller {

	/**
	 * Index
	 */
	public function index() {
		if ($this->config->item('system_maintenance') === TRUE) {
			//システムメンテナンス時
			$this->session->sess_destroy();
			throw new Exception("システムメンテナンス中");
		}
		$this->view('login/login');
	}

	/**
	 * 認証チェック
	 */
	public function auth() {

		$input = $this->get_attribute();
		
		$this->load->model('login/LoginModel', 'model');
		$this->load->library('dao/EmployeeDao');
		
		//認証チェック
		$result = $this->model->login_check($input);
		
		if ($result!= null) {
			//失敗した場合は結果にエラーメッセージが入ってくる
			$this->set_err_info($result);
			$this->view('login/login');
			return;
		}
		
		//メインへリダイレクト
		redirect('TopPage');
	}
}
?>