<?php

/**
 * WeeklyReportOutputController
 * @author takanori_gozu
 *
 */
class WeeklyReportOutput extends MY_Controller {
	
	/**
	 * Index
	 */
	public function index() {
		
		$this->load->model('weekly_report/WeeklyReportOutputModel', 'model');
		$this->load->library('dao/EmployeeDao');
		
		$this->set('from_date', date('Y-m-d'));
		$this->set('to_date', date('Y-m-d'));
		$this->set('employee_map', $this->model->get_employee_map());
		
		$this->view('weekly_report/weekly_report_output');
	}
	
	/**
	 * 出力
	 */
	public function output() {
		
		$this->load->model('weekly_report/WeeklyReportOutputModel', 'model');
		$this->load->library('dao/EmployeeDao');
		$this->load->library('dao/WeeklyReportDao');
		
		$input = $this->get_attribute();
		
		$list = $this->model->get_list($input);
		
		//社員名を取得
		$employee_name = $this->model->get_employee_name($input['employee_id']);
		
		$this->model->excel_output($list, $employee_name);
	}
}
?>