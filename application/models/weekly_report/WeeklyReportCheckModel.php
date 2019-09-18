<?php

/**
 * WeeklyReportCheckModel
 * @author takanori_gozu
 *
 */
class WeeklyReportCheckModel extends MY_Model {
	
	/**
	 * 年月プルダウン
	 */
	public function get_month_map() {
		
		$this->set_table(WeeklyReportDao::TABLE_NAME, self::DB_TRAN);
		
		$this->add_select_as('left(standard_date, 7)', 'month');
		$this->add_order('month', self::ORDER_DESC);
		
		$list = $this->do_select();
		
		$map = array();
		
		$map[''] = '年月を選択';
		
		for($i = 0; $i < count($list); $i++) {
			$month = $list[$i]['month'];
			$map[$month] = date('Y年n月', strtotime($month));
		}
		
		return $map;
	}
	
	/**
	 * 集計一覧
	 */
	public function get_list($month) {
		
		if ($month == '') return null;
		
		//社員情報
		$this->set_table(EmployeeDao::TABLE_NAME, self::DB_MASTER);
		
		$this->add_select(EmployeeDao::COL_ID);
		$this->add_select(EmployeeDao::COL_NAME);
		
		$this->add_where(EmployeeDao::COL_USER_LEVEL, self::LEVEL_ADMINISTRATOR, self::COMP_GREATER_THAN);
		$this->add_where(EmployeeDao::COL_RETIREMENT, '0'); //退職者は除く
		
		$employee_list = $this->do_select();
		
		$this->set_table(WeeklyReportDao::TABLE_NAME, self::DB_TRAN);
		
		$this->add_select(WeeklyReportDao::COL_REGIST_USER_ID);
		$this->add_select_as('count(*)', 'cnt');
		
		$this->add_where_like(WeeklyReportDao::COL_STANDARD_DATE, $month, self::WILD_CARD_AFTER);
		$this->add_group(WeeklyReportDao::COL_REGIST_USER_ID);
		
		$report_map = $this->key_value_map($this->do_select(), WeeklyReportDao::COL_REGIST_USER_ID, 'cnt');
		
		$list = array();
		
		foreach ($employee_list as $row) {
			$cnt = 0;
			if (array_key_exists($row['id'], $report_map) === true) {
				$cnt = $report_map[$row['id']];
			}
			$list[] = array('name' => $row['name'], 'count' => $cnt);
		}
		
		//ソート(提出回数の昇順)
		$sort = array();
		foreach ($list as $key => $value) {
			$sort[$key] = $value['count'];
		}
		
		array_multisort($sort, SORT_ASC, $list);
		
		return $list;
	}
	
	/**
	 * 一覧項目
	 */
	public function get_list_col() {
		
		$list_col = array();
		
		$list_col[] = array('width' => 300, 'value' => '氏名');
		$list_col[] = array('width' => 300, 'value' => '提出回数');
		
		return $list_col;
	}
}
?>