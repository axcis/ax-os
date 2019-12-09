<?php

/**
 * CostManageBaseModel
 * 経費の共通モデル
 * @author takanori_gozu
 *
 */
class CostManageBaseModel extends MY_Model {
	
	/**
	 * 入力タイプ
	 */
	public function get_input_type_map() {
		
		$map = array();
		$map[1] = $this->lang->line('traffic');
		$map[2] = $this->lang->line('expenses');
		
		return $map;
	}
	
	/**
	 * 支払方法
	 */
	public function get_pay_type_map() {
		
		$map = array();
		$map[1] = '現金';
		$map[2] = 'クレジット';
		
		return $map;
	}
	
	/**
	 * 内訳
	 */
	public function get_expenses_type_map() {
		
		$map = array();
		$map[1] = '電車代';
		$map[2] = 'タクシー代';
		$map[3] = '租税公課';
		$map[4] = '通信費';
		$map[5] = '会議費';
		$map[6] = '交際費';
		$map[7] = '地代家賃';
		$map[8] = '事務用品';
		$map[9] = '雑費';
		$map[10] = '立替金';
		
		return $map;
	}
	
	public function get_round_trip_type_map() {
		
		$map = array();
		
		$map[1] = '片道';
		$map[2] = '往復';
		$map[3] = '定期';
		
		return $map;
	}
	
	/**
	 * 年月のマッピング
	 */
	public function get_month_map() {
		
		$this->set_table(ExpensesDao::TABLE_NAME, self::DB_TRAN);
		
		$this->set_distinct();
		$this->add_select_as(ExpensesDao::COL_REGIST_YM, 'month');
		$this->add_order('month', self::ORDER_DESC);
		
		$list = $this->do_select();
		
		$map = array();
		//当月を入れておく
		$map[date('Y-m')] = date('Y年n月', strtotime("now"));
		
		for($i = 0; $i < count($list); $i++) {
			$month = $list[$i]['month'];
			$map[$month] = date('Y年n月', strtotime($month));
		}
		
		return $map;
	}
	
	/**
	 * 社員のマッピング
	 */
	public function get_employee_map($no_select_show = true) {
		
		$this->set_table(EmployeeDao::TABLE_NAME, self::DB_MASTER);
		
		$this->add_select(EmployeeDao::COL_ID);
		$this->add_select(EmployeeDao::COL_NAME);
		
		//Adminは除外
		$this->add_where(EmployeeDao::COL_USER_LEVEL, self::LEVEL_ADMINISTRATOR, self::COMP_NOT_EQUAL);
		$this->add_where(EmployeeDao::COL_RETIREMENT, '0'); //退職者は除く
		
		$list = $this->do_select();
		
		$map = array();
		
		if ($no_select_show) {
			$map[''] = '名前を選択';
		}
		$map += $this->key_value_map($list);
		
		return $map;
	}
}
?>