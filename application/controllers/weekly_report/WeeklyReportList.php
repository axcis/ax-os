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
		
		$list = $this->model->get_list();
		
		$this->set('list', $list);
		$this->set('list_col', $this->model->get_list_col());
		$this->set('link', $this->model->get_link());
		
		//IDを取得してセッションへ
		$this->set_session('report_ids', $this->model->get_report_ids($list));
		
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
		
		$list = $this->model->get_list($search);
		
		$this->set('list', $list);
		$this->set('list_col', $this->model->get_list_col());
		$this->set('link', $this->model->get_link());
		
		//IDを取得してセッションへ
		$this->set_session('report_ids', $this->model->get_report_ids($list));
		
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
		
		//セッションから遷移対象のレポートIDをセットする
		$this->set('prev_report_id', $this->model->get_redirect_id($id, '1'));
		$this->set('next_report_id', $this->model->get_redirect_id($id));
		
		$this->view('weekly_report/weekly_report_detail');
	}
}
?>