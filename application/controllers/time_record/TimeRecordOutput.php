<?php

/**
 * TimeRecordOutputController
 * @author takanori_gozu
 *
 */
class TimeRecordOutput extends MY_Controller {
	
	/**
	 * Index
	 */
	public function index() {
		
		$this->load->model('time_record/TimeRecordOutputModel', 'model');
		$this->load->library('dao/TimeRecordDao');
		
		$this->set('action', 'output');
		$this->set('employee_id', $this->get_session('user_id'));
		$this->set('month_map', $this->model->get_month_map());
		$this->set('scene_map', $this->model->get_scene_map());
		
		if ($this->get_session('user_level') == self::LEVEL_ADMINISTRATOR) {
			$this->load->library('dao/EmployeeDao');
			$this->set('employee_map', $this->model->get_employee_map());
		}
		
		$this->view('time_record/time_record_output');
	}
	
	/**
	 * 勤怠表出力
	 */
	public function output() {
		
		$this->load->model('time_record/TimeRecordOutputModel', 'model');
		$this->load->library('dao/EmployeeDao');
		$this->load->library('dao/TimeRecordDao');
		$this->load->library('dao/HolidayDao');
		
		$input = $this->get_attribute();
		
		$list = $this->model->get_list($input);
		$total_list = $this->model->get_total_list($input);
		
		//社員名を取得
		$employee_name = $this->model->get_employee_name($input['employee_id']);
		
		$this->model->excel_output($input, $list, $total_list, $employee_name);
	}
}
?>