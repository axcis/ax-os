<?php

/**
 * AtiContentHelpController
 * @author takanori_gozu
 *
 */
class AtiContentHelp extends MY_Controller {
	
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