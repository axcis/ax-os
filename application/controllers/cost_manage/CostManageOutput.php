<?php

/**
 * CostManageOutputController
 * @author takanori_gozu
 *
 */
class CostManageOutput extends MY_Controller {
	
	/**
	 * コンストラクタ
	 */
	public function __construct() {
		
		parent::__construct();
		
		if ($this->get_session('user_level') == self::LEVEL_ADMINISTRATOR) {
			//管理権限は出力不可(一括出力で実施)
			$this->session->sess_destroy();
			redirect('Login');
		}
	}
	
	/**
	 * Index
	 */
	public function index() {
		
		$this->load->model('cost_manage/CostManageOutputModel', 'model');
		$this->load->library('dao/ExpensesDao');
		
		$this->set('input_type_map', $this->model->get_input_type_map());
		$this->set('month_map', $this->model->get_month_map());
		
		$this->view('cost_manage/cost_manage_output');
	}
	
	/**
	 * 出力
	 */
	public function output() {
		
		$this->load->model('cost_manage/CostManageOutputModel', 'model');
		$this->load->library('dao/ExpensesDao');
		$this->load->library('dao/EmployeeDao');
		
		$input_type = $this->get('input_type');
		$employee_id = $this->get_session('user_id');
		$month = $this->get('month');
		
		$this->model->excel_output($input_type, $employee_id, $month);
	}
}
?>