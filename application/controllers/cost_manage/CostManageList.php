<?php

/**
 * CostManageListController
 * @author takanori_gozu
 *
 */
class CostManageList extends MY_Controller {
	
	/**
	 * Index
	 */
	public function index() {
		
		$this->load->model('cost_manage/CostManageListModel', 'model');
		$this->load->library('dao/ExpensesDao');
		
		$this->set('class_key', 'cost_manage');
		$this->set('class_path', 'cost_manage/CostManage');
		
		$month = date('Y-m');
		$input_type = '1';
		$employee_id = $this->get_session('user_id');
		$this->set('input_type_map', $this->model->get_input_type_map());
		$this->set('month_map', $this->model->get_month_map());
		$this->set('month', $month);
		$this->set('input_type', $input_type);
		$this->set('link', $this->model->get_link());
		
		if ($this->get_session('user_level') == self::LEVEL_ADMINISTRATOR) {
			$this->load->library('dao/EmployeeDao');
			$this->set('employee_map', $this->model->get_employee_map());
			$this->set('modify_disable', '1'); //管理者は編集不可
		} else {
			$this->set('list', $this->model->get_list($month, $input_type, $employee_id));
			$this->set('list_col', $this->model->get_list_col($input_type));
		}
		
		$this->view('cost_manage/cost_manage_list');
	}
	
	/**
	 * 検索
	 */
	public function search() {
		
		$this->load->model('cost_manage/CostManageListModel', 'model');
		$this->load->library('dao/ExpensesDao');
		
		$this->set('class_key', 'cost_manage');
		$this->set('class_path', 'cost_manage/CostManage');
		
		$month = $this->get('search_month');
		$input_type = $this->get('search_input_type');
		$employee_id = $this->get('search_employee', $this->get_session('user_id'));
		$this->set('input_type_map', $this->model->get_input_type_map());
		$this->set('month_map', $this->model->get_month_map());
		$this->set('month', $month);
		$this->set('input_type', $input_type);
		
		$this->set('list', $this->model->get_list($month, $input_type, $employee_id));
		$this->set('list_col', $this->model->get_list_col($input_type));
		$this->set('link', $this->model->get_link());
		
		if ($this->get_session('user_level') == self::LEVEL_ADMINISTRATOR) {
			$this->load->library('dao/EmployeeDao');
			$this->set('employee_map', $this->model->get_employee_map());
			$this->set('modify_disable', '1'); //管理者は編集不可
		}
		
		$this->view('cost_manage/cost_manage_list');
	}
}
?>