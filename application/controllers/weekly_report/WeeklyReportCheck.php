<?php

/**
 * WeeklyReportCheckController
 * @author takanori_gozu
 *
 */
class WeeklyReportCheck extends MY_Controller {
	
	/**
	 * Index
	 */
	public function index() {
		
		$this->load->model('weekly_report/WeeklyReportCheckModel', 'model');
		$this->load->library('dao/WeeklyReportDao');
		
		$this->set('popup', '1');
		
		$this->set('list_col', $this->model->get_list_col());
		$this->set('month_map', $this->model->get_month_map());
		
		$this->view('weekly_report/weekly_report_check');
	}
	
	/**
	 * 一覧の取得(ajax)
	 */
	public function select() {
		
		$this->load->model('weekly_report/WeeklyReportCheckModel', 'model');
		$this->load->library('dao/EmployeeDao');
		$this->load->library('dao/WeeklyReportDao');
		
		$month = $this->get('month');
		
		$list = $this->model->get_list($month);
		
		echo json_encode(array($list));
	}
}
?>