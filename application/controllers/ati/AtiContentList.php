<?php

/**
 * AtiContentListController
 * @author takanori_gozu
 *
 */
class AtiContentList extends MY_Controller {
	
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
	 * コンテンツ一覧
	 */
	public function content_list($category_id) {
		
		$this->load->model('ati/AtiContentListModel', 'model');
		$this->load->library('dao/AtiCategoryDao');
		$this->load->library('dao/AtiContentDao');
		
		$this->set('list', $this->model->get_list($category_id));
		$this->set('list_col', $this->model->get_list_col());
		$this->set('link', $this->model->get_link($category_id));
		
		$this->set('class_key', 'ati');
		$this->set('class_path', 'ati/AtiContent');
		$this->set('no_search', '1');
		
		if ($this->get_session('user_level') == self::LEVEL_LEADER ||
			$this->get_session('user_level') == self::LEVEL_SUB_LEADER) {
				$this->set('modifiable', '1');
			}
		
		$category_info = $this->model->get_category_info($category_id);
		
		$this->set('category_id', $category_id);
		$this->set('category_name', $category_info[AtiCategoryDao::COL_CATEGORY_NAME]);
		
		$this->view('ati/ati_content_list');
	}
	
	/**
	 * 詳細
	 */
	public function detail($category_id, $id) {
		
		$this->load->model('ati/AtiContentListModel', 'model');
		$this->load->library('dao/AtiContentDao');
		
		$info = $this->model->get_info($category_id, $id);
		
		//Imgタグの変換
		$this->model->change_img_tag($info);
		$this->set_attribute($info);
		
		//前コンテンツ、後コンテンツへのリンク用
		$this->set('prev_id', $this->model->get_content_link_id($category_id, $id - 1));
		$this->set('next_id', $this->model->get_content_link_id($category_id, $id + 1));
		
		$this->view('ati/ati_content_detail');
	}
	
	/**
	 * 課題ファイルのDL
	 */
	public function download($category_id, $id) {
		
		$this->load->model('ati/AtiContentListModel', 'model');
		
		$this->model->text_output($category_id, $id);
	}
}
?>