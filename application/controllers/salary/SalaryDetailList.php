<?php

/**
 * SalaryDetailListController
 * @author takanori_gozu
 *
 */
class SalaryDetailList extends MY_Controller {
	
	/**
	 * コンストラクタ
	 */
	public function __construct() {
		
		parent::__construct();
		
		if ($this->get_session('user_name') == $this->lang->line('administrator')) {
			//システム管理者は閲覧不可
			$this->session->sess_destroy();
			redirect('Login');
		}
	}
	
	/**
	 * Index
	 */
	public function index() {
		
		$this->load->model('salary/SalaryDetailListModel', 'model');
		$this->load->library('dao/SalaryDetailDao');
		
		$this->set('class_key', 'salary');
		$this->set('class_path', 'salary/SalaryDetail');
		
		$this->set('no_search', '1');
		
		$this->set('list', $this->model->get_list());
		$this->set('list_col', $this->model->get_list_col());
		
		$this->view('salary/salary_detail_list');
	}
	
	/**
	 * 詳細
	 */
	public function detail($supply_ym) {
		
		$this->load->model('salary/SalaryDetailListModel', 'model');
		$this->load->library('dao/SalaryDetailDao');
		
		$this->set('popup', '1');
		
		$info = $this->model->get_info($supply_ym);
		
		$this->model->make_pdf($supply_ym, $info);
	}
}
?>