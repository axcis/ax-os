<?php

/**
 * SalaryDetailRegistController
 * @author takanori_gozu
 *
 */
class SalaryDetailRegist extends MY_Controller {
	
	/**
	 * コンストラクタ
	 */
	public function __construct() {
		
		parent::__construct();
		
		if ($this->get_session('user_name') != $this->lang->line('administrator')) {
			//システム管理者以外は閲覧不可
			$this->session->sess_destroy();
			redirect('Login');
		}
	}
	
	/**
	 * Index
	 */
	public function index() {
		
		$this->view('salary/salary_detail_input');
	}
	
	/**
	 * 確認画面
	 */
	public function confirm() {
		
		$this->load->model('salary/SalaryDetailRegistModel', 'model');
		
		$file_name = $_FILES['up_file']['name'];
		
		$msgs = $this->model->validation($file_name);
		
		if ($msgs != null) {
			$this->set_err_info($msgs);
			$this->view('salary/salary_detail_input');
			return;
		}
		
		$ret = $this->model->file_upload($file_name);
		
		if ($ret == false) {
			$this->set_err_info(array("ファイルのアップロードに失敗しました。"));
			$this->view('salary/salary_detail_input');
			return;
		}
		
		$csv_err = '0';
		
		//csvを読み込んで、中身をチェックする
		$data = $this->model->read_csv($file_name, $csv_err);
		
		$this->set('data', $data);
		$this->set('err', $csv_err);
		$this->set('list_col', $this->model->get_list_col());
		
		$this->view('salary/salary_detail_input_confirm');
	}
	
	/**
	 * 登録
	 */
	public function regist() {
		
		$this->load->model('salary/SalaryDetailRegistModel', 'model');
		$this->load->library('dao/SalaryDetailDao');
		
		$data = json_decode($this->get('csv_data'));
		
		$this->model->bulk_regist($data);
		
		//メッセージを出して画面はそのままにする
		$this->show_dialog($this->lang->line('db_registed'));
		$this->redirect_js(base_url(). 'TopPage');
	}
}
?>