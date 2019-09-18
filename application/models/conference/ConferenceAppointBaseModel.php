<?php

/**
 * ConferenceAppointBaseModel
 * 会議室予約画面の共通モデル
 * @author takanori_gozu
 *
 */
class ConferenceAppointBaseModel extends MY_Model {
	
	/**
	 * 会議室のマッピング
	 * DBの値をセットする
	 */
	public function get_conference_map() {
		
		$this->set_table(ConferenceDao::TABLE_NAME, self::DB_MASTER);
		
		$this->add_select(ConferenceDao::COL_ID);
		$this->add_select(ConferenceDao::COL_ROOM_NAME);
		
		return $this->key_value_map($this->do_select(), ConferenceDao::COL_ID, ConferenceDao::COL_ROOM_NAME);
	}
	
	/**
	 * 数値→HH:MM
	 */
	protected function int_to_time($int) {
		
		if ($int == null) return null;
		
		$m = $int % 60;
		$h = ($int - $m) / 60;
		
		return $h. ':'. sprintf('%02d', $m);
	}
	
	/**
	 * 社員のマッピング
	 */
	public function get_employee_map() {
		
		$this->set_table(EmployeeDao::TABLE_NAME, self::DB_MASTER);
		
		$this->add_select(EmployeeDao::COL_ID);
		$this->add_select(EmployeeDao::COL_NAME);
		
		$this->add_where(EmployeeDao::COL_RETIREMENT, '0'); //退職者は除く
		
		return $this->key_value_map($this->do_select());
	}
}
?>