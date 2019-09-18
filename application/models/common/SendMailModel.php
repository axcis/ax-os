<?php

/**
 * SendMailModel
 * @author takanori_gozu
 */
class SendMailModel extends CI_Model {

	public function __construct() {
		parent::__construct();
		$this->load->library('email');
		$this->email->set_wordwrap(false);
	}
	
	//イニシャライズ
	public function init($config) {
		$this->email->initialize($config);
	}

	//送信者
	public function from($addr, $name = '') {
		$this->email->from($addr, $name);
	}

	//宛先
	public function to($addr) {
		$this->email->to($addr);
	}

	//CC
	public function cc($addr) {
		$this->email->cc($addr);
	}

	//BCC
	public function bcc($addr) {
		$this->email->bcc($addr);
	}

	//件名
	public function subject($subject) {
		$this->email->subject($subject);
	}

	//本文
	public function message($msg) {
		$this->email->message($msg);
	}

	//header
	public function header($header, $value) {
		$this->email->header($header, $value);
	}

	//ファイルの添付
	public function attach($file, $dis = '', $new = '', $mime = '') {
		$this->email->attach($file, $dis, $new, $mime);
	}

	//送信
	public function send() {
		return $this->email->send();
	}

	//設定値クリア
	public function clear($attach = true) {
		$this->email->clear($attach);
	}

	public function debug() {
		$this->email->print_debugger();
	}
}
?>