<?php

/**
 * WeeklyReportRegistController
 * @author takanori_gozu
 *
 */
class WeeklyReportRegist extends MY_Controller {
	
	public function regist_input() {
		
		$this->set('action', 'regist');
		$this->set('class_path', 'weekly_report/WeeklyReport');
		//直前の月曜日をデフォルトでセット
		$this->set('standard_date', date('Y-m-d', strtotime('last monday', strtotime(date('Y-m-d')))));
		
		$this->view('weekly_report/weekly_report_input');
	}
	
	/**
	 * 新規登録
	 */
	public function regist() {
		
		$this->load->model('weekly_report/WeeklyReportRegistModel', 'model');
		$this->load->library('dao/WeeklyReportDao');
		
		$input = $this->get_attribute();
		
		$msgs = $this->model->validation($input);
		
		if ($msgs != null) {
			$this->set_err_info($msgs);
			$this->view('weekly_report/weekly_report_input');
			return;
		}
		
		$this->model->db_regist($input);
		
		$this->redirect_js(base_url(). $this->get('class_path'). 'List');
	}
	
	public function modify_input($id) {
		
		$this->load->model('weekly_report/WeeklyReportRegistModel', 'model');
		$this->load->library('dao/WeeklyReportDao');
		
		$this->set('action', 'modify');
		$this->set('class_path', 'weekly_report/WeeklyReport');
		$this->set('delete_disable', '1');
		
		$info = $this->model->get_info($id);
		
		$this->set_attribute($info);
		
		$this->view('weekly_report/weekly_report_input');
	}
	
	/**
	 * 更新
	 */
	public function modify() {
		
		$this->load->model('weekly_report/WeeklyReportRegistModel', 'model');
		$this->load->library('dao/WeeklyReportDao');
		
		$input = $this->get_attribute();
		
		$msgs = $this->model->validation($input);
		
		if ($msgs != null) {
			$this->set_err_info($msgs);
			$this->set('delete_disable', '1');
			$this->view('weekly_report/weekly_report_input');
			return;
		}
		
		$this->model->db_modify($input);
		
		$this->redirect_js(base_url(). $this->get('class_path'). 'List');
	}
}
?>