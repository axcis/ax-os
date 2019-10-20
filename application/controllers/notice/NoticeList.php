<?php

/**
 * NoticeListController
 * @author takanori_gozu
 * 
 */
class NoticeList extends MY_Controller {
	
	/**
	 * コンストラクタ
	 */
	public function __construct() {
		
		parent::__construct();
		
		if ($this->get_session('user_level') > self::LEVEL_LEADER) {
			//サブリーダー・メンバーは閲覧不可
			$this->session->sess_destroy();
			redirect('Login');
		}
	}
	
	/**
	 * Index
	 */
	public function index() {
		
		$this->load->model('notice/NoticeListModel', 'model');
		$this->load->library('dao/NoticeDao');
		
		$this->set('class_key', 'notice');
		$this->set('class_path', 'notice/Notice');
		
		$this->set('list', $this->model->get_list());
		$this->set('list_col', $this->model->get_list_col());
		$this->set('link', $this->model->get_link());
		
		//検索不要
		$this->set('no_search', '1');
		
		$this->view('notice/notice_list');
	}
	
	/**
	 * 出欠確認の表示
	 */
	public function presence_confirm($notice_id) {
		
		$this->set('popup', '1');
		
		$this->load->model('notice/NoticeListModel', 'model');
		$this->load->library('dao/EmployeeDao');
		$this->load->library('dao/NoticeDao');
		$this->load->library('dao/PresenceDao');
		
		//お知らせタイトルだけ取ってくる
		$info = $this->model->get_notice_info($notice_id);
		$notice_name = $info['notice_name'];
		
		//出欠状況リスト
		$list = $this->model->get_presence_list($notice_id);
		$list_col = $this->model->get_presence_list_col();
		$this->set('id', $notice_id);
		$this->set('list', $list);
		$this->set('list_col', $list_col);
		
		$this->set('notice_name', $notice_name);
		
		$this->view('notice/presence_check_list');
	}
	
	/**
	 * 出力
	 */
	public function excel_output($id) {
		
		$this->load->model('notice/NoticeListModel', 'model');
		$this->load->library('dao/EmployeeDao');
		$this->load->library('dao/PresenceDao');
		
		//出欠状況リスト
		$list = $this->model->get_presence_list($id);
		
		$file_name = '出欠一覧.xlsx';
		
		//Excel出力
		$this->model->make_excel($list, $file_name);
	}
}
?>