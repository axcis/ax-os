<?php

/**
 * TimeRecordListController
 * @author takanori_gozu
 *
 */
class TimeRecordList extends MY_Controller {
	
	/**
	 * Index
	 */
	public function index() {
		
		$this->load->model('time_record/TimeRecordListModel', 'model');
		$this->load->library('dao/TimeRecordDao');
		$this->load->library('dao/HolidayDao');
		
		$month = date('Y-m');
		$scene = '1';
		$employee_id = $this->get_session('user_id');
		$this->set('class_key', 'time_record');
		$this->set('class_path', 'time_record/TimeRecord');
		
		$this->set('month', $month);
		$this->set('scene', $scene);
		$this->set('employee_id', $employee_id);
		$this->set('link', $this->model->get_link());
		
		$this->set('month_map', $this->model->get_month_map());
		$this->set('scene_map', $this->model->get_scene_map());
		if ($this->get_session('user_level') == self::LEVEL_ADMINISTRATOR) {
			$this->load->library('dao/EmployeeDao');
			$this->set('employee_map', $this->model->get_employee_map());
		} else {
			$this->set('list', $this->model->get_list($month, $scene, $employee_id));
			$this->set('list_col', $this->model->get_list_col());
			$this->set('total_list', $this->model->get_total_list($month, $scene, $employee_id));
		}
		
		$this->view('time_record/time_record_list');
	}
	
	/**
	 * 検索
	 */
	public function search() {
		
		$this->load->model('time_record/TimeRecordListModel', 'model');
		$this->load->library('dao/TimeRecordDao');
		$this->load->library('dao/HolidayDao');
		
		$month = $this->get('search_month');
		$scene = $this->get('search_scene');
		$employee_id = $this->get('search_employee', $this->get_session('user_id'));
		$this->set('class_key', 'time_record');
		$this->set('class_path', 'time_record/TimeRecord');
		
		$this->set('month', $month);
		$this->set('scene', $scene);
		$this->set('employee_id', $employee_id);
		$this->set('list', $this->model->get_list($month, $scene, $employee_id));
		$this->set('list_col', $this->model->get_list_col());
		$this->set('total_list', $this->model->get_total_list($month, $scene, $employee_id));
		$this->set('link', $this->model->get_link());
		
		$this->set('month_map', $this->model->get_month_map());
		$this->set('scene_map', $this->model->get_scene_map());
		if ($this->get_session('user_level') == self::LEVEL_ADMINISTRATOR) {
			$this->load->library('dao/EmployeeDao');
			$this->set('employee_map', $this->model->get_employee_map());
		}
		
		$this->view('time_record/time_record_list');
	}
}
?>