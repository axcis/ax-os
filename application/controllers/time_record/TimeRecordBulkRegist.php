<?php

/**
 * TimeRecordBulkRegistController
 * @author takanori_gozu
 *
 */
class TimeRecordBulkRegist extends MY_Controller {
	
	/**
	 * コンストラクタ
	 */
	public function __construct() {
		
		parent::__construct();
		//TODO: ペンディング中(ここにきてはいけない)
		$this->session->sess_destroy();
		redirect('Login');
// 		if ($this->get_session('user_level') == self::LEVEL_ADMINISTRATOR) {
// 			//管理権限は一括登録不可
// 			$this->session->sess_destroy();
// 			redirect('Login');
// 		}
	}
	
	/**
	 * Index
	 */
	public function index() {
		
		$this->load->model('time_record/TimeRecordBulkRegistModel', 'model');
		$this->load->library('dao/TimeRecordDao');
		$this->load->library('dao/TimeRecordConfigDao');
		$this->load->library('dao/HolidayDao');
		
		$month = $this->get('month');
		$employee_id = $this->get_session('user_id');
		
		$result = $this->model->bulk_regist($month, $employee_id);
		
		if ($result == 'error') {
			$this->show_dialog($this->lang->line('db_regist_failure'));
		} else {
			$this->show_dialog($this->lang->line('db_bulk_regist'));
		}
		
		$this->redirect_js(base_url(). 'time_record/TimeRecordList');
	}
}
?>