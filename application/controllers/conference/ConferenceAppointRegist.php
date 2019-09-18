<?php

/**
 * ConferenceAppointRegistController
 * @author takanori_gozu
 *
 */
class ConferenceAppointRegist extends MY_Controller {
	
	public function regist_input() {
		
		$this->load->model('conference/ConferenceAppointRegistModel', 'model');
		$this->load->library('dao/ConferenceDao');
		
		$this->set('action', 'regist');
		$this->set('class_path', 'conference/ConferenceAppoint');
		$this->set('conference_map', $this->model->get_conference_map());
		$this->set('time_map', $this->model->get_time_map());
		
		$this->view('conference/conference_appoint_input');
	}
	
	/**
	 * 新規登録
	 */
	public function regist() {
		
		$this->load->model('conference/ConferenceAppointRegistModel', 'model');
		$this->load->library('dao/ConferenceAppointDao');
		$this->load->library('dao/ConferenceDao');
		
		$input = $this->get_attribute();
		
		$msgs = $this->model->validation($input);
		
		if ($msgs != null) {
			$this->set_err_info($msgs);
			$this->set('conference_map', $this->model->get_conference_map());
			$this->set('time_map', $this->model->get_time_map());
			$this->view('conference/conference_appoint_input');
			return;
		}
		
		$insert_id = $this->model->db_regist($input);
		
		//管理宛にメールを送信する
		if ($insert_id > 0) {
			$this->model->send_mail($input);
		}
		
		$this->redirect_js(base_url(). $this->get('class_path'). 'List');
	}
	
	public function modify_input($id) {
		
		$this->load->model('conference/ConferenceAppointRegistModel', 'model');
		$this->load->library('dao/ConferenceDao');
		$this->load->library('dao/ConferenceAppointDao');
		
		$info = $this->model->get_info($id);
		
		$this->set_attribute($info);
		$this->set('action', 'modify');
		$this->set('class_path', 'conference/ConferenceAppoint');
		$this->set('conference_map', $this->model->get_conference_map());
		$this->set('time_map', $this->model->get_time_map());
		
		$this->view('conference/conference_appoint_input');
	}
	
	/**
	 * 更新
	 */
	public function modify() {
		
		$this->load->model('conference/ConferenceAppointRegistModel', 'model');
		$this->load->library('dao/ConferenceAppointDao');
		$this->load->library('dao/ConferenceDao');
		
		$input = $this->get_attribute();
		
		$msgs = $this->model->validation($input);
		
		if ($msgs != null) {
			$this->set_err_info($msgs);
			$this->set('conference_map', $this->model->get_conference_map());
			$this->set('time_map', $this->model->get_time_map());
			$this->view('conference/conference_appoint_input');
			return;
		}
		
		$this->model->db_modify($input);
		
		$this->redirect_js(base_url(). $this->get('class_path'). 'List');
	}
	
	/**
	 * 削除
	 */
	public function delete() {
		
		$this->load->model('conference/ConferenceAppointRegistModel', 'model');
		$this->load->library('dao/ConferenceAppointDao');
		
		$id = $this->get('id');
		
		$this->model->db_delete($id);
		
		$this->redirect_js(base_url(). $this->get('class_path'). 'List');
	}
}
?>