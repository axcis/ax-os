<?php

/**
 * AtiContentHelpController
 * @author takanori_gozu
 *
 */
class AtiContentHelp extends MY_Controller {
	
	/**
	 * コンストラクタ
	 */
	public function __construct() {
		
		parent::__construct();
		
		if ($this->get_session('user_level') == self::LEVEL_ADMINISTRATOR ||
			$this->get_session('user_level') == self::LEVEL_MEMBER) {
				//管理権限及びメンバーは閲覧不可
				$this->session->sess_destroy();
				redirect('Login');
			}
	}
	
	/**
	 * Index
	 */
	public function index() {
		
		$this->set('popup', '1');
		
		$this->view('ati/ati_content_help');
	}
	
	/**
	 * ひな形のダウンロード
	 */
	public function download() {
		
		$this->load->model('common/FileOperationModel', 'file');
		
		$file_path = $this->lang->line('ati_content_dir'). 'example/example.zip';
		
		$this->file->download($file_path, 'コンテンツ登録ひな形HTML.zip');
	}
}
?>