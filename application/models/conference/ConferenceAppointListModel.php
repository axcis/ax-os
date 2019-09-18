<?php

/**
 * ConferenceAppointListModel
 * @author takanori_gozu
 *
 */
class ConferenceAppointListModel extends ConferenceAppointBaseModel {
	
	/**
	 * カレンダー
	 */
	public function load_calendar() {
		
		$config = array();
		$config['show_next_prev'] = true;
		$config['next_prev_url'] = base_url(). 'conference/ConferenceAppointList/show/';
		
		//カレンダーのスタイル
		$config['template'] = $this->set_calendar_template();
		
		$this->load->library('calendar', $config);
	}
	
	/**
	 * カレンダーのスタイル設定
	 */
	private function set_calendar_template() {
		
		$template = array();
		
		$template['table_open'] = '<table id="calendar">';
		$template['heading_row_start'] = '<tr id="calendar-header">';
		$template['heading_previous_cell'] = '<th colspan="2"><a href="{previous_url}"><div id="btn_prev">前月</div></a></th>';
		$template['heading_next_cell'] = '<th colspan="2"><a href="{next_url}"><div id="btn_next">次月</div></a></th>';
		$template['cal_row_start'] = '<tr class="week">';
		$template['week_day_cell'] = '<td class="weekday">{week_day}';
		$template['cal_cell_start'] = '<td class="day">';
		$template['cal_cell_start_today'] = '<td id="today">';
		$template['cal_cell_content'] = '<p class="day_num">{day}</p><p class="content">{content}';
		$template['cal_cell_content_today'] = '<p class="day_num">{day}</p><p class="content">{content}';
		
		return $template;
	}
	
	/**
	 * カレンダーの詳細設定
	 */
	public function get_calendar_info() {
		
		$year = $this->uri->segment(4) == null ? date('Y') : $this->uri->segment(4);
		$month = $this->uri->segment(5) == null ? date('m') : $this->uri->segment(5);
		
		$data = $this->get_conference_appoint_info($year, $month);
		
		return $this->calendar->generate($year, $month, $data);
	}
	
	/**
	 * スケジュールのリンク情報を取得
	 */
	private function get_conference_appoint_info($year, $month) {
		
		$ym = $year. '-'. $month;
		
		$this->set_table(ConferenceAppointDao::TABLE_NAME, self::DB_TRAN);
		
		$this->add_select(ConferenceAppointDao::COL_TARGET_DATE);
		$this->add_where_like(ConferenceAppointDao::COL_TARGET_DATE, $ym, self::WILD_CARD_AFTER);
		
		$list = $this->do_select();
		
		$infos = array();
		
		foreach ($list as $info) {
			$date = $info[ConferenceAppointDao::COL_TARGET_DATE];
			$key = intval(substr($date, -2)); //日だけ取り出す
			$infos[$key] = '<label><input type="checkbox" class="checkbox" value="'. $date. '" ><span class="checkbox-icon"><i class="fas fa-marker" aria-hidden="true"></i></span></label>';
		}
		
		return $infos;
	}
	
	/**
	 * 一覧
	 */
	public function get_conference_appoint_list($target_date) {
		
		$this->set_table(ConferenceAppointDao::TABLE_NAME, self::DB_TRAN);
		
		$this->add_select(ConferenceAppointDao::COL_ID);
		$this->add_select(ConferenceAppointDao::COL_REGIST_USER_ID);
		$this->add_select_as("''", 'employee_name');
		$this->add_select(ConferenceAppointDao::COL_TARGET_DATE);
		$this->add_select_as(ConferenceAppointDao::COL_CONFERENCE_ID, 'room_name');
		$this->add_select(ConferenceAppointDao::COL_START_TIME);
		$this->add_select(ConferenceAppointDao::COL_END_TIME);
		$this->add_select(ConferenceAppointDao::COL_PURPOSE);
		
		$this->add_where(ConferenceAppointDao::COL_TARGET_DATE, $target_date);
		$this->add_order(ConferenceAppointDao::COL_START_TIME);
		
		$list = $this->do_select();
		
		//マージ
		$employee_map = $this->get_employee_map();
		$conference_map = $this->get_conference_map();
		
		foreach ($list as &$row) {
			$row['employee_name'] = $employee_map[$row[ConferenceAppointDao::COL_REGIST_USER_ID]];
			$row['room_name'] = $conference_map[$row['room_name']];
			$row[ConferenceAppointDao::COL_TARGET_DATE] = date('Y年n月j日', strtotime($row[ConferenceAppointDao::COL_TARGET_DATE]));
			$row[ConferenceAppointDao::COL_START_TIME] = $this->int_to_time($row[ConferenceAppointDao::COL_START_TIME]);
			$row[ConferenceAppointDao::COL_END_TIME] = $this->int_to_time($row[ConferenceAppointDao::COL_END_TIME]);
			if ($row[ConferenceAppointDao::COL_REGIST_USER_ID] == $this->get_session('user_id')) {
				$row['modify_url'] = '<a href="'. base_url(). 'conference/ConferenceAppointRegist/modify_input/'. $row[ConferenceAppointDao::COL_ID]. '"><i class="far fa-edit" aria-hidden="true"></i></a>';
			}
		}
		
		return $list;
	}
	
	/**
	 * 一覧項目名
	 */
	public function get_list_col() {
		
		$list_cols = array();
		
		$list_cols[] = array('width' => 70, 'value' => ''); //編集
		$list_cols[] = array('width' => 150, 'value' => '登録者');
		$list_cols[] = array('width' => 150, 'value' => '日付');
		$list_cols[] = array('width' => 150, 'value' => '使用場所');
		$list_cols[] = array('width' => 100, 'value' => '開始');
		$list_cols[] = array('width' => 100, 'value' => '終了');
		$list_cols[] = array('width' => 300, 'value' => '使用目的');
		
		return $list_cols;
	}
	
	/**
	 * リンク情報
	 */
	public function get_link_list() {
		
		$link_list = array();
		
		$link_list[] = array('url' => 'conference/ConferenceAppointRegist/regist_input', 'class' => 'far fa-edit', 'value' => '予約');
		
		return $link_list;
	}
}
?>