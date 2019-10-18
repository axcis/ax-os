<?php

/**
 * CostManageUploadController
 * @author takanori_gozu
 *
 */
class CostManageUpload extends MY_Controller {
	
	/**
	 * コンストラクタ
	 */
	public function __construct() {
		
		parent::__construct();
		
		if ($this->get_session('user_level') == self::LEVEL_ADMINISTRATOR) {
			//管理権限はアップロード不可
			$this->session->sess_destroy();
			redirect('Login');
		}
	}
	
	/**
	 * Index
	 */
	public function index() {
		
		$this->load->model('cost_manage/CostManageUploadModel', 'model');
		$this->load->library('dao/ExpensesDao');
		
		$this->set('month', date('Y-m'));
		$this->set('month_map', $this->model->get_month_map());
		
		$this->view('cost_manage/cost_manage_upload');
	}
	
	/**
	 * アップロード
	 */
	public function upload() {
		
		$this->load->model('cost_manage/CostManageUploadModel', 'model');
		$this->load->library('dao/ExpensesDao');
		
		$ym = date('Ym', strtotime($this->get('month')));
		$file_name = mb_convert_encoding($_FILES['up_file']['name'], 'SJIS', 'UTF-8');
		
		$msgs = $this->model->validation($ym, $file_name);
		
		if ($msgs != null) {
			$this->set_err_info($msgs);
			$this->set('month_map', $this->model->get_month_map());
			$this->view('cost_manage/cost_manage_upload');
			return;
		}
		
		$this->model->file_upload($ym, $file_name);
		
		$this->redirect_js(base_url(). 'cost_manage/CostManageList');
	}
}
?>