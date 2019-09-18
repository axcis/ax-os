<?php

/**
 * LogoutController
 * @author takanori_gozu
 *
 */
class Logout extends MY_Controller {

	public function index() {
		//セッションを破棄してログイン画面へ
		$this->session->sess_destroy();
		redirect('Login');
	}
}
?>