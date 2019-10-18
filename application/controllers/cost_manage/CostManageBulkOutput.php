<?php

/**
 * CostManageBulkOutputController
 * @author takanori_gozu
 *
 */
class CostManageBulkOutput extends MY_Controller {
	
	/**
	 * コンストラクタ
	 */
	public function __construct() {
		
		parent::__construct();
		
		if ($this->get_session('user_level') != self::LEVEL_ADMINISTRATOR) {
			//管理権限のみ対応可能
			$this->session->sess_destroy();
			redirect('Login');
		}
	}
	
	/**
	 * Index
	 */
	public function index() {
		
		$this->load->model('cost_manage/CostManageBulkOutputModel', 'model');
		$this->load->library('dao/ExpensesDao');
		$this->load->library('dao/EmployeeDao');
		
		$this->set('month_map', $this->model->get_month_map());
		$this->set('employee_map', $this->model->get_employee_map());
		
		$this->view('cost_manage/cost_manage_bulk_output');
	}
	
	/**
	 * 出力(Zipで一括出力)
	 */
	public function output() {
		
		$this->load->model('cost_manage/CostManageBulkOutputModel', 'model');
		$this->load->library('dao/ExpensesDao');
		$this->load->library('dao/EmployeeDao');
		
		$input = $this->get_attribute();
		
		$this->model->bulk_output($input);
	}
}
?>