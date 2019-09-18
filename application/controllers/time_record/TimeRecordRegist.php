<?php

/**
 * TimeRecordRegistController
 * @author takanori_gozu
 *
 */
class TimeRecordRegist extends MY_Controller {
	
	/**
	 * Input
	 */
	public function input($date, $scene, $employee_id) {
		
		$this->load->model('time_record/TimeRecordRegistModel', 'model');
		$this->load->library('dao/TimeRecordDao');
		
		if ($this->get_session('user_level') == self::LEVEL_ADMINISTRATOR) {
			//社員名を取得する
			$this->load->library('dao/EmployeeDao');
			$this->set('employee_name', $this->model->get_employee_name($employee_id));
		}
		
		$info = $this->model->get_info($date, $scene, $employee_id);
		
		$this->set('scene_map', $this->model->get_scene_map(true));
		$this->set('classification_map', $this->model->get_classification_map());
		$this->set('class_path', 'time_record/TimeRecord');
		
		if ($info != null) {
			$this->set('action', 'modify');
			$this->set_attribute($info);
		} else {
			$this->set('action', 'regist');
			$this->set('work_date', $date);
			$this->set('employee_id', $employee_id);
			$this->set('scene', '3');
			//設定値取得
			$this->load->library('dao/TimeRecordConfigDao');
			$config = $this->model->get_config_info();
			if ($config != null) $this->set_attribute($config);
		}
		
		$this->view('time_record/time_record_input');
	}
	
	/**
	 * 新規登録
	 */
	public function regist() {
		
		$this->load->model('time_record/TimeRecordRegistModel', 'model');
		$this->load->library('dao/TimeRecordDao');
		
		$input = $this->get_attribute();
		
		$msgs = $this->model->validation($input);
		
		if ($msgs != null) {
			$this->set_err_info($msgs);
			$this->set('scene_map', $this->model->get_scene_map(true));
			$this->set('classification_map', $this->model->get_classification_map());
			$this->view('time_record/time_record_input');
			return;
		}
		
		//計算
		$record_map = $this->model->get_time_record_map($input);
		
		//新規登録
		$this->model->db_regist($record_map);
		
		$this->redirect_js(base_url(). $this->get('class_path'). 'List');
	}
	
	/**
	 * 更新
	 */
	public function modify() {
		
		$this->load->model('time_record/TimeRecordRegistModel', 'model');
		$this->load->library('dao/TimeRecordDao');
		
		$input = $this->get_attribute();
		
		$msgs = $this->model->validation($input);
		
		if ($msgs != null) {
			$this->set_err_info($msgs);
			$this->set('scene_map', $this->model->get_scene_map(true));
			$this->set('classification_map', $this->model->get_classification_map());
			$this->view('time_record/time_record_input');
			return;
		}
		
		//計算
		$record_map = $this->model->get_time_record_map($input);
		
		$this->model->db_modify($record_map);
		
		$this->redirect_js(base_url(). $this->get('class_path'). 'List');
	}
	
	/**
	 * 削除
	 */
	public function delete() {
		
		$this->load->model('time_record/TimeRecordRegistModel', 'model');
		$this->load->library('dao/TimeRecordDao');
		
		$input = $this->get_attribute();
		
		$this->model->db_delete($input);
		
		$this->redirect_js(base_url(). $this->get('class_path'). 'List');
	}
}
?>