<?php

/**
 * ClendarModel
 * カレンダーの共通モデル
 * @author takanori_gozu
 *
 */
class CalendarModel extends CI_Model {
	
	private $_config = array();
	private $_year = '';
	private $_month = '';
	private $_holidays = array();
	private $_data = array();
	
	/**
	 * ロード
	 */
	public function load_calendar() {
		$this->load->library('calendar', $this->_config);
	}
	
	//設定(次月・前月表示)
	public function set_show_next_prev() {
		$this->_config['show_next_prev'] = true;
	}
	
	//設定(次月・前月のリダイレクトURL)
	public function set_next_prev_url($url = '') {
		$this->_config['next_prev_url'] = $url;
	}
	
	//設定(カレンダーのテンプレート)
	public function set_template($template = array()) {
		$this->_config['template'] = $template;
	}
	
	//セグメント(年月)
	public function set_segment($year, $month) {
		$this->_year = $year;
		$this->_month = $month;
	}
	
	//祝日
	public function set_holiday($holidays) {
		$this->_holidays = $holidays;
	}
	
	//データ
	public function set_data($data) {
		$this->_data = $data;
	}
	
	//生成
	public function make() {
		return $this->calendar->generate($this->_year, $this->_month, $this->_holidays, $this->_data);
	}
}
?>