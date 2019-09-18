<?php

/**
 * ConferenceAppointListController
 * @author takanori_gozu
 *
 */
class ConferenceAppointList extends MY_Controller {
	
	/**
	 * Index
	 */
	public function index() {
		
		$this->load->model('conference/ConferenceAppointListModel', 'model');
		$this->load->library('dao/ConferenceDao');
		$this->load->library('dao/ConferenceAppointDao');
		
		//カレンダーのロード
		$this->model->load_calendar();
		
		//データ部などの詳細設定
		$calendar_info = $this->model->get_calendar_info();
		
		//データ取得
		$this->set('list_col', $this->model->get_list_col());
		$this->set('link', $this->model->get_link_list());
		$this->set('calendar', $calendar_info);
		
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