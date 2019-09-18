<?php

/**
 * TopPageController
 * @author takanori_gozu
 *
 */
class TopPage extends MY_Controller {
	
	/**
	 * Index
	 */
	public function index() {
		
		$this->load->model('top/TopPageModel', 'model');
		$this->load->library('dao/NoticeDao');
		
		$this->set('class_key', 'top');
		
		//お知らせ
		$this->set('notice_list', $this->model->get_notice_list());
		$this->set('list_col', $this->model->get_notice_list_col());
		
		$this->set('no_search', '1');
		
		$this->view('top/top_page');
	}
	
	/**
	 * お知らせの詳細表示
	 */
	public function detail($notice_id) {
		
		$this->load->model('top/TopPageModel', 'model');
		$this->load->library('dao/NoticeDao');
		$this->load->library('dao/PresenceDao');
		
		$this->set('popup', '1');
		
		$info = $this->model->get_notice_info($notice_id);
		$this->set_attribute($info);
		
		//出欠確認欄
		if ($this->model->chk_show_presence_area($info)) {
			$this->set('show_presence_area', '1');
		}
		
		$this->view('top/notice_detail');
	}
	
	/**
	 * 出欠アクション
	 */
	public function presence() {
		
		$input = $this->get_attribute();
		
		$this->load->model('top/TopPageModel', 'model');
		$this->load->library('dao/PresenceDao');
		
		$this->model->db_regist_presence($input);
		
		$this->popup_close();
	}
}
?>