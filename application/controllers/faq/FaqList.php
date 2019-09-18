<?php

/**
 * FaqListController
 * @author takanori_gozu
 *
 */
class FaqList extends MY_Controller {
	
	/**
	 * Index
	 */
	public function index() {
		
		$this->load->model('faq/FaqListModel', 'model');
		$this->load->library('dao/FaqDao');
		
		$this->set('class_key', 'faq');
		$this->set('class_path', 'faq/Faq');
		
		$this->set('list', $this->model->get_list());
		$this->set('list_col', $this->model->get_list_col());
		$this->set('link', $this->model->get_link());
		
		$this->view('faq/faq_list');
	}
	
	/**
	 * 検索
	 */
	public function search() {
		
		$search = $this->get_attribute();
		
		$this->load->model('faq/FaqListModel', 'model');
		$this->load->library('dao/FaqDao');
		
		$this->set('class_key', 'faq');
		$this->set('class_path', 'faq/Faq');
		
		$this->set('list', $this->model->get_list($search));
		$this->set('list_col', $this->model->get_list_col());
		$this->set('link', $this->model->get_link());
		
		$this->view('faq/faq_list');
	}
	
	/**
	 * 詳細
	 */
	public function detail($id) {
		
		$this->load->model('faq/FaqListModel', 'model');
		$this->load->library('dao/FaqDao');
		
		$this->set('popup', '1');
		
		$this->set_attribute($this->model->get_info($id));
		
		$this->view('faq/faq_detail');
	}
}
?>