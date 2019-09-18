<?php

/**
 * TimeRecordBulkRegistController
 * @author takanori_gozu
 *
 */
class TimeRecordBulkRegist extends MY_Controller {
	
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