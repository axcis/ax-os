<?php

/**
 * TimeRecordConfigController
 * @author takanori_gozu
 *
 */
class TimeRecordConfig extends MY_Controller {
	
	/**
	 * コンストラクタ
	 */
	public function __construct() {
		
		parent::__construct();
		
		if ($this->get_session('user_level') == self::LEVEL_ADMINISTRATOR) {
			//管理権限は登録不可
			$this->session->sess_destroy();
			redirect('Login');
		}
	}
	
	/**
	 * Index
	 */
	public function index() {
		
		$this->load->model('time_record/TimeRecordConfigModel', 'model');
		$this->load->library('dao/TimeRecordConfigDao');
		
		$info = $this->model->get_info();
		
		if ($info != null) {
			$this->set('action', 'modify');
			$this->set_attribute($info);
		} else {
			$this->set('action', 'regist');
			$this->set('employee_id', $this->get_session('user_id'));
		}
		
		$this->set('class_path', 'time_record/TimeRecord');
		
		$this->view('time_record/time_record_config_input');
	}
	
	/**
	 * 新規登録
	 */
	public function regist() {
		
		$this->load->model('time_record/TimeRecordConfigModel', 'model');
		$this->load->library('dao/TimeRecordConfigDao');
		
		$input = $this->get_attribute();
		
		$msgs = $this->model->validation($input);
		
		if ($msgs != null) {
			$this->set_err_info($msgs);
			$this->view('time_record/time_record_config_input');
			return;
		}
		
		$this->model->db_regist($input);
		
		$this->redirect_js(base_url(). 'time_record/TimeRecordList');
	}
	
	/**
	 * 更新
	 */
	public function modify() {
		
		$this->load->model('time_record/TimeRecordConfigModel', 'model');
		$this->load->library('dao/TimeRecordConfigDao');
		
		$input = $this->get_attribute();
		
		$msgs = $this->model->validation($input);
		
		if ($msgs != null) {
			$this->set_err_info($msgs);
			$this->view('time_record/time_record_config_input');
			return;
		}
		
		$this->model->db_modify($input);
		
		$this->redirect_js(base_url(). 'time_record/TimeRecordList');
	}
}
?>