<?php

/**
 * AtiContentRegistController
 * @author takanori_gozu
 *
 */
class AtiContentRegist extends MY_Controller {
	
	/**
	 * コンストラクタ
	 */
	public function __construct() {
		
		parent::__construct();
		
		if ($this->get_session('user_level') == self::LEVEL_ADMINISTRATOR ||
			$this->get_session('user_level') == self::LEVEL_MEMBER) {
			//管理権限及びメンバーは登録不可
			$this->session->sess_destroy();
			redirect('Login');
		}
	}
	
	public function regist_input($category_id) {
		
		$this->load->model('ati/AtiContentRegistModel', 'model');
		$this->load->library('dao/AtiCategoryDao');
		$this->load->library('dao/AtiContentDao');
		
		$this->set('action', 'regist');
		
		$category_info = $this->model->get_category_info($category_id);
		$this->set('id', $this->model->get_next_content_no($category_id));
		
		$this->set('category_id', $category_id);
		$this->set('category_name', $category_info[AtiCategoryDao::COL_CATEGORY_NAME]);
		
		$this->view('ati/ati_content_input');
	}
	
	/**
	 * 新規登録
	 */
	public function regist() {
		
		$this->load->model('ati/AtiContentRegistModel', 'model');
		$this->load->library('dao/AtiContentDao');
		
		$input = $this->get_attribute();
		
		$msgs = $this->model->validation($input);
		
		if ($msgs != null) {
			$this->set_err_info($msgs);
			$this->view('ati/ati_content_input');
			return;
		}
		
		//登録
		$row = $this->model->db_regist($input);
		//アップロード
		if ($row == 1) $this->model->file_upload($input);
		
		$this->show_dialog($this->lang->line('db_registed'));
		$this->redirect_js(base_url(). 'ati/AtiContentList/content_list/'. $input['category_id']); //コンテンツ一覧に戻る
	}
	
	public function modify_input($category_id, $id) {
		
		$this->load->model('ati/AtiContentRegistModel', 'model');
		$this->load->library('dao/AtiCategoryDao');
		$this->load->library('dao/AtiContentDao');
		
		$this->set('action', 'modify');
		
		$this->set_attribute($this->model->get_info($category_id, $id));
		
		$category_info = $this->model->get_category_info($category_id);
		$this->set('category_name', $category_info[AtiCategoryDao::COL_CATEGORY_NAME]);
		
		$this->view('ati/ati_content_input');
	}
	
	/**
	 * 更新
	 */
	public function modify() {
		
		$this->load->model('ati/AtiContentRegistModel', 'model');
		$this->load->library('dao/AtiContentDao');
		
		$input = $this->get_attribute();
		
		$msgs = $this->model->validation($input);
		
		if ($msgs != null) {
			$this->set_err_info($msgs);
			$this->view('ati/ati_content_input');
			return;
		}
		
		//登録
		$ret = $this->model->db_modify($input);
		//アップロード
		if ($ret == true) $this->model->file_upload($input);
		
		$this->show_dialog($this->lang->line('db_modified'));
		$this->redirect_js(base_url(). 'ati/AtiContentList/content_list/'. $input['category_id']); //コンテンツ一覧に戻る
	}
}
?>