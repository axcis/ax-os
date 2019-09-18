<?php

/**
 * FaqRegistController
 * @author takanori_gozu
 *
 */
class FaqRegist extends MY_Controller {
	
	public function regist_input() {
		
		$this->set('action', 'regist');
		$this->set('class_path', 'faq/Faq');
		
		$this->view('faq/faq_input');
	}
	
	/**
	 * 新規登録
	 */
	public function regist() {
		
		$this->load->model('faq/FaqRegistModel', 'model');
		$this->load->library('dao/FaqDao');
		
		$input = $this->get_attribute();
		
		$msgs = $this->model->validation($input);
		
		if ($msgs != null) {
			$this->set_err_info($msgs);
			$this->view('faq/faq_input');
			return;
		}
		
		$this->model->db_regist($input);
		
		$this->redirect_js(base_url(). $this->get('class_path'). 'List');
	}
	
	public function modify_input($id) {
		
		$this->load->model('faq/FaqRegistModel', 'model');
		$this->load->library('dao/FaqDao');
		
		$this->set_attribute($this->model->get_info($id));
		
		$this->set('action', 'modify');
		$this->set('class_path', 'faq/Faq');
		
		$this->view('faq/faq_input');
	}
	
	/**
	 * 更新
	 */
	public function modify() {
		
		$this->load->model('faq/FaqRegistModel', 'model');
		$this->load->library('dao/FaqDao');
		
		$input = $this->get_attribute();
		
		$msgs = $this->model->validation($input);
		
		if ($msgs != null) {
			$this->set_err_info($msgs);
			$this->view('faq/faq_input');
			return;
		}
		
		$this->model->db_modify($input);
		
		$this->redirect_js(base_url(). $this->get('class_path'). 'List');
	}
	
	/**
	 * 削除
	 */
	public function delete() {
		
		$this->load->model('faq/FaqRegistModel', 'model');
		$this->load->library('dao/FaqDao');
		
		$id = $this->get('id');
		
		$this->model->db_delete($id);
		
		$this->redirect_js(base_url(). $this->get('class_path'). 'List');
	}
}
?>