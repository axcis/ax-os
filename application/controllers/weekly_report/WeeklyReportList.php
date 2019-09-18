<?php

/**
 * WeeklyReportListController
 * @author takanori_gozu
 *
 */
class WeeklyReportList extends MY_Controller {
	
	/**
	 * Index
	 */
	public function index() {
		
		$this->load->model('weekly_report/WeeklyReportListModel', 'model');
		$this->load->library('dao/EmployeeDao');
		$this->load->library('dao/WeeklyReportDao');
		
		$this->set('class_key', 'weekly_report');
		$this->set('class_path', 'weekly_report/WeeklyReport');
		
		$this->set('list', $this->model->get_list());
		$this->set('list_col', $this->model->get_list_col());
		$this->set('link', $this->model->get_link());
		
		if ($this->get_session('user_level') < self::LEVEL_MEMBER) {
			$this->set('employee_map', $this->model->get_employee_map());
			$this->set('employee_search', '1');
		}
		
		$this->view('weekly_report/weekly_report_list');
	}
	
	/**
	 * 検索
	 */
	public function search() {
		
		$search = $this->get_attribute();
		
		$this->load->model('weekly_report/WeeklyReportListModel', 'model');
		$this->load->library('dao/EmployeeDao');
		$this->load->library('dao/WeeklyReportDao');
		
		$this->set('class_key', 'weekly_report');
		$this->set('class_path', 'weekly_report/WeeklyReport');
		
		$this->set('list', $this->model->get_list($search));
		$this->set('list_col', $this->model->get_list_col());
		$this->set('link', $this->model->get_link());
		
		if ($this->get_session('user_level') < self::LEVEL_MEMBER) {
			$this->set('employee_map', $this->model->get_employee_map());
			$this->set('employee_search', '1');
		}
		
		$this->view('weekly_report/weekly_report_list');
	}
	
	/**
	 * 詳細
	 */
	public function detail($id) {
		
		$this->load->model('weekly_report/WeeklyReportListModel', 'model');
		$this->load->library('dao/EmployeeDao');
		$this->load->library('dao/WeeklyReportDao');
		
		$this->set('popup', '1');
		
		$this->set_attribute($this->model->get_info($id));
		
		$this->view('weekly_report/weekly_report_detail');
	}
}
?>