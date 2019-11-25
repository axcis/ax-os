<?php

/**
 * WeeklyReportListModel
 * @author takanori_gozu
 *
 */
class WeeklyReportListModel extends MY_Model {
	
	/**
	 * 一覧
	 */
	public function get_list($search = null) {
		
		//社員情報取得
		$employee_map = $this->get_employee_map(false);
		
		$this->set_table(WeeklyReportDao::TABLE_NAME, self::DB_TRAN);
		
		$this->add_select(WeeklyReportDao::COL_ID);
		$this->add_select(WeeklyReportDao::COL_STANDARD_DATE);
		$this->add_select(WeeklyReportDao::COL_REGIST_USER_ID);
		$this->add_select_as('""', 'name');
		$this->add_select_as('""', 'modifiable');
		
		$this->add_where_in(WeeklyReportDao::COL_REGIST_USER_ID, implode(',', array_keys($employee_map)));
		
		if ($search != null) {
			$this->set_search($search, WeeklyReportDao::COL_REGIST_USER_ID, 'search_regist_user_id');
			$this->set_search($search, WeeklyReportDao::COL_STANDARD_DATE, 'search_date_from', self::COMP_GREATER_EQUAL);
			$this->set_search($search, WeeklyReportDao::COL_STANDARD_DATE, 'search_date_to', self::COMP_LESS_EQUAL);
		}
		
		$this->add_order(WeeklyReportDao::COL_STANDARD_DATE, self::ORDER_DESC);
		
		$list = $this->do_select();
		
		foreach ($list as &$row) {
			$user_id = $row['regist_user_id'];
			$row['name'] = $employee_map[$row['regist_user_id']]; //社員名マージ
			if ($user_id == $this->get_session('user_id')) {
				$row['modifiable'] = '1';
			}
		}
		
		return $list;
	}
	
	/**
	 * 社員情報
	 */
	public function get_employee_map($no_select_show = true) {
		
		$this->set_table(EmployeeDao::TABLE_NAME, self::DB_MASTER);
		
		$this->add_select(EmployeeDao::COL_ID);
		$this->add_select(EmployeeDao::COL_NAME);
		
		//Adminは除外
		$this->add_where(EmployeeDao::COL_USER_LEVEL, self::LEVEL_ADMINISTRATOR, self::COMP_NOT_EQUAL);
		$this->add_where(EmployeeDao::COL_RETIREMENT, '0'); //退職者は除く
		
		switch ($this->get_session(EmployeeDao::COL_USER_LEVEL)) {
			case self::LEVEL_ADMINISTRATOR:
			case self::LEVEL_LEADER:
				//管理者・リーダーは全員を取得
				break;
			case self::LEVEL_SUB_LEADER:
				//サブリーダーは所属部署のメンバーのみ
				$this->add_where(EmployeeDao::COL_DIVISION_ID, $this->get_session(EmployeeDao::COL_DIVISION_ID));
				$this->add_where(EmployeeDao::COL_USER_LEVEL, self::LEVEL_SUB_LEADER, self::COMP_GREATER_EQUAL);
				break;
			case self::LEVEL_MEMBER:
				//メンバーは自分のみ
				$this->add_where(EmployeeDao::COL_ID, $this->get_session('user_id'));
				break;
		}
		
		$list = $this->do_select();
		
		$map = array();
		
		if ($no_select_show) {
			$map[''] = '名前を選択';
		}
		$map += $this->key_value_map($list);
		
		return $map;
	}
	
	/**
	 * 一覧項目
	 */
	public function get_list_col() {
		
		$list_col = array();
		
		$list_col[] = array('width' => 70, 'value' => '編集');
		$list_col[] = array('width' => 300, 'value' => '日付');
		$list_col[] = array('width' => 300, 'value' => '氏名');
		$list_col[] = array('width' => 120, 'value' => '詳細');
		
		return $list_col;
	}
	
	/**
	 * リンク
	 */
	public function get_link() {
		
		$link_list = array();
		
		if ($this->get_session('user_level') > self::LEVEL_ADMINISTRATOR) {
			$link_list[] = array('url' => 'weekly_report/WeeklyReportRegist/regist_input', 'class' => 'far fa-edit', 'value' => '登録');
		}
		if ($this->get_session('user_level') < self::LEVEL_SUB_LEADER) {
			//リーダー以上はチェック可
			$link_list[] = array('url' => 'weekly_report/WeeklyReportCheck', 'class' => 'far fa-check-circle', 'value' => 'チェック', 'popup' => '1');
		}
		if ($this->get_session('user_level') == self::LEVEL_ADMINISTRATOR) {
			$link_list[] = array('url' => 'weekly_report/WeeklyReportOutput', 'class' => 'far fa-file-alt', 'value' => '出力');
		}
		
		return $link_list;
	}
	
	/**
	 * 詳細
	 */
	public function get_info($id) {
		
		$this->set_table(WeeklyReportDao::TABLE_NAME, self::DB_TRAN);
		
		$this->add_select_as(WeeklyReportDao::COL_REGIST_USER_ID, 'name');
		$this->add_select(WeeklyReportDao::COL_PROJECT_NAME);
		$this->add_select(WeeklyReportDao::COL_WORK_CONTENT);
		$this->add_select(WeeklyReportDao::COL_REFLECT);
		$this->add_select(WeeklyReportDao::COL_OTHER);
		$this->add_select(WeeklyReportDao::COL_STANDARD_DATE);
		
		$this->add_where(WeeklyReportDao::COL_ID, $id);
		
		$info = $this->do_select_info();
		
		$employee_id = $info['name'];
		
		//社員マスタから名前を取得
		$this->set_table(EmployeeDao::TABLE_NAME, self::DB_MASTER);
		
		$this->add_select(EmployeeDao::COL_NAME);
		$this->add_where(EmployeeDao::COL_ID, $employee_id);
		
		$employee_info = $this->do_select_info();
		
		$info['name'] = $employee_info[EmployeeDao::COL_NAME];
		
		return $info;
	}
	
	/**
	 * セッション格納用レポートID
	 */
	public function get_report_ids($list) {
		
		$ids = '';
		
		for ($i = 0; $i < count($list); $i++) {
			$report_id = $list[$i][WeeklyReportDao::COL_ID];
			if ($i > 0) {
				$ids .= ',';
			}
			$ids .= $report_id;
		}
		
		return $ids;
	}
	
	/**
	 * 詳細画面の遷移
	 */
	public function get_redirect_id($id, $reverse = '0') {
		
		if ($reverse == '1') {
			$report_ids = array_reverse(explode(',', $this->get_session('report_ids')));
		} else {
			$report_ids = explode(',', $this->get_session('report_ids'));
		}
		
		foreach ($report_ids as $key => $now) {
			next($report_ids);
			if ($id == $now) {
				$next_id = (current($report_ids) !== false) ? current($report_ids) : 0;
				break;
			}
		}
		
		return $next_id;
	}
}
?>