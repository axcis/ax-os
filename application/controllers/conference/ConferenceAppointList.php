<?php

/**
 * ConferenceAppointListController
 * @author takanori_gozu
 *
 */
class ConferenceAppointList extends MY_Controller {
	
	/**
	 * コンストラクタ
	 */
	public function __construct() {
		
		parent::__construct();
		
		if ($this->get_session('user_level') > self::LEVEL_SUB_LEADER) {
			//メンバーは閲覧不可
			$this->session->sess_destroy();
			redirect('Login');
		}
	}
	
	/**
	 * Index
	 */
	public function index() {
		
		$this->load->model('conference/ConferenceAppointListModel', 'model');
		$this->load->library('dao/HolidayDao');
		$this->load->library('dao/ConferenceDao');
		$this->load->library('dao/ConferenceAppointDao');
		
		//カレンダー生成
		$calendar = $this->model->make_calendar();
		
		//データ取得
		$this->set('list_col', $this->model->get_list_col());
		$this->set('link', $this->model->get_link_list());
		$this->set('calendar', $calendar);
		
		$this->set('class_key', 'conference');
		$this->set('class_path', 'conference/ConferenceAppoint');
		$this->set('no_search', '1');
		
		$this->view('conference/conference_appoint_list');
	}
	
	/**
	 * 遷移
	 */
	public function show() {
		$this->index();
	}
	
	/**
	 * 該当日付選択時の挙動(ajax)
	 */
	public function select() {
		
		$target_date = $this->get('target_date');
		
		$this->load->model('conference/ConferenceAppointListModel', 'model');
		$this->load->library('dao/EmployeeDao');
		$this->load->library('dao/ConferenceDao');
		$this->load->library('dao/ConferenceAppointDao');
		
		//予約状況を取ってくる
		$list = $this->model->get_conference_appoint_list($target_date);
		
		echo json_encode(array($list));
	}
}
?>