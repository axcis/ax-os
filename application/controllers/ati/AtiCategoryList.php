<?php

/**
 * AtiCategoryListController
 * @author takanori_gozu
 *
 */
class AtiCategoryList extends MY_Controller {
	
	/**
	 * コンストラクタ
	 */
	public function __construct() {
		
		parent::__construct();
		
		if ($this->get_session('user_level') == self::LEVEL_ADMINISTRATOR) {
			//管理者は閲覧不可
			$this->session->sess_destroy();
			redirect('Login');
		}
	}
	
	/**
	 * Index
	 */
	public function index() {
		
		$this->load->model('ati/AtiCategoryListModel', 'model');
		$this->load->library('dao/AtiCategoryDao');
		
		$this->set('class_key', 'ati');
		$this->set('class_path', 'ati/AtiCategory');
		
		$this->set('no_search', '1');
		
		$this->set('list', $this->model->get_category_list());
		
		$this->view('ati/ati_category_list');
	}
}
?>