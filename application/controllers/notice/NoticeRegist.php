<?php

/**
 * NoticeRegistController
 * @author takanori_gozu
 *
 */
class NoticeRegist extends MY_Controller {
	
	public function regist_input() {
		
		$this->load->model('notice/NoticeRegistModel', 'model');
		
		$this->set('action', 'regist');
		$this->set('class_path', 'notice/Notice');
		
		$this->set('important_map', $this->model->get_important_map());
		$this->set('presence_type_map', $this->model->get_presence_type_map());
		
		$this->view('notice/notice_input');
	}
	
	/**
	 * 新規登録
	 */
	public function regist() {
		
		$this->load->model('notice/NoticeRegistModel', 'model');
		$this->load->library('dao/NoticeDao');
		
		$input = $this->get_attribute();
		
		$msgs = $this->model->validation($input);
		
		if ($msgs != null) {
			$this->set_err_info($msgs);
			$this->set('important_map', $this->model->get_important_map());
			$this->set('presence_type_map', $this->model->get_presence_type_map());
			return;
		}
		
		$insert_id = $this->model->db_regist($input);
		
		//All宛にメール通知
		if ($insert_id > 0 && isset($input['send_mail'])) {
			$this->model->send_mail($input);
		}
		
		$this->redirect_js(base_url(). $this->get('class_path'). 'List');
	}
	
	public function modify_input($id) {
		
		$this->load->model('notice/NoticeRegistModel', 'model');
		$this->load->library('dao/NoticeDao');
		
		$this->set_attribute($this->model->get_info($id));
		
		$this->set('action', 'modify');
		$this->set('class_path', 'notice/Notice');
		
		$this->set('important_map', $this->model->get_important_map());
		$this->set('presence_type_map', $this->model->get_presence_type_map());
		$this->set('delete_disable', '1');
		
		$this->view('notice/notice_input');
	}
	
	/**
	 * 更新
	 */
	public function modify() {
		
		$this->load->model('notice/NoticeRegistModel', 'model');
		$this->load->library('dao/NoticeDao');
		
		$input = $this->get_attribute();
		
		$msgs = $this->model->validation($input);
		
		if ($msgs != null) {
			$this->set_err_info($msgs);
			$this->set('important_map', $this->model->get_important_map());
			$this->set('presence_type_map', $this->model->get_presence_type_map());
			return;
		}
		
		$this->model->db_modify($input);
		
		$this->redirect_js(base_url(). $this->get('class_path'). 'List');
	}
}
?>