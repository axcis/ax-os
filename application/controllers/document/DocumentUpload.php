<?php

/**
 * DocumentUploadController
 * @author takanori_gozu
 *
 */
class DocumentUpload extends MY_Controller {
	
	/**
	 * コンストラクタ
	 */
	public function __construct() {
		
		parent::__construct();
		
		if ($this->get_session('user_level') != self::LEVEL_ADMINISTRATOR) {
			//管理権限以外はアップロード不可
			$this->session->sess_destroy();
			redirect('Login');
		}
	}
	
	/**
	 * Index
	 */
	public function index() {
		
		$this->load->model('document/DocumentUploadModel', 'model');
		$this->load->library('dao/DocumentCategoryDao');
		
		$this->set('category_map', $this->model->get_category_map());
		
		$this->view('document/document_upload');
	}
	
	/**
	 * アップロード
	 */
	public function upload() {
		
		$this->load->model('document/DocumentUploadModel', 'model');
		$this->load->library('dao/DocumentInfoDao');
		
		$category_id = $this->get('category_id');
		$file_name = $_FILES['up_file']['name'];
		
		$msgs = $this->model->validation($file_name);
		
		if ($msgs != null) {
			$this->set_err_info($msgs);
			$this->load->library('dao/DocumentCategoryDao');
			$this->set('category_map', $this->model->get_category_map());
			$this->view('document/document_upload');
			return;
		}
		
		$this->model->db_regist($category_id, $file_name);
		
		$this->model->file_upload($category_id, $file_name);
		
		//メッセージを出して画面はそのままにする
		$this->show_dialog($this->lang->line('file_uploaded'));
		$this->redirect_js(base_url(). 'document/DocumentUpload');
	}
}
?>