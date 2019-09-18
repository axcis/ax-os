<?php

/**
 * CostManageListModel
 * @author takanori_gozu
 *
 */
class CostManageListModel extends CostManageBaseModel {
	
	/**
	 * 一覧
	 */
	public function get_list($month, $input_type, $employee_id) {
		
		$this->set_table(ExpensesDao::TABLE_NAME, self::DB_TRAN);
		
		$this->add_select(ExpensesDao::COL_ID);
		$this->add_select(ExpensesDao::COL_EMPLOYEE_ID);
		$this->add_select(ExpensesDao::COL_EXPENSES_YMD);
		$this->add_select(ExpensesDao::COL_INPUT_TYPE);
		$this->add_select(ExpensesDao::COL_PAY_TYPE);
		$this->add_select(ExpensesDao::COL_EXPENSES_TYPE);
		$this->add_select(ExpensesDao::COL_TRANSPORT);
		$this->add_select(ExpensesDao::COL_FROM_PLACE);
		$this->add_select(ExpensesDao::COL_TO_PLACE);
		$this->add_select(ExpensesDao::COL_EXPENSES_DETAIL);
		$this->add_select(ExpensesDao::COL_COST);
		
		$this->add_where(ExpensesDao::COL_EMPLOYEE_ID, $employee_id);
		$this->add_where(ExpensesDao::COL_INPUT_TYPE, $input_type);
		$this->add_where(ExpensesDao::COL_REGIST_YM, $month);
		$this->add_order(ExpensesDao::COL_EXPENSES_YMD);
		
		$list = $this->do_select();
		
		//変換
		$pay_type_map = $this->get_pay_type_map();
		$expenses_type_map = $this->get_expenses_type_map();
		
		foreach ($list as &$info) {
			if ($info['pay_type'] != null) {
				$info['pay_type'] = $pay_type_map[$info['pay_type']];
			}
			if ($info['expenses_type'] != null) {
				$info['expenses_type'] = $expenses_type_map[$info['expenses_type']];
			}
		}
		
		return $list;
	}
	
	/**
	 * 項目
	 */
	public function get_list_col($input_type) {
		
		$list_col = array();
		
		if ($this->get_session('user_level') > self::LEVEL_ADMINISTRATOR) {
			$list_col[] = array('width' => 50, 'value' => '編集'); //編集
		}
		
		switch ($input_type) {
			case '1':
				//交通費
				$list_col[] = array('width' => 150, 'value' => '日付');
				$list_col[] = array('width' => 150, 'value' => '手段');
				$list_col[] = array('width' => 150, 'value' => '出発地');
				$list_col[] = array('width' => 150, 'value' => '到着地');
				$list_col[] = array('width' => 300, 'value' => '目的');
				$list_col[] = array('width' => 120, 'value' => '金額');
				break;
			case '2':
				//経費
				$list_col[] = array('width' => 150, 'value' => '日付');
				$list_col[] = array('width' => 150, 'value' => '支払方法');
				$list_col[] = array('width' => 150, 'value' => '内訳');
				$list_col[] = array('width' => 350, 'value' => '目的');
				$list_col[] = array('width' => 120, 'value' => '金額');
				break;
		}
		
		return $list_col;
	}
	
	/**
	 * リンク
	 */
	public function get_link() {
		
		$link_list = array();
		if ($this->get_session('user_level') > self::LEVEL_ADMINISTRATOR) {
			$link_list[] = array('url' => 'cost_manage/CostManageRegist/regist_input', 'class' => 'far fa-edit', 'value' => '登録');
			$link_list[] = array('url' => 'cost_manage/CostManageUpload', 'class' => 'fas fa-cloud-upload-alt', 'value' => 'アップロード');
			$link_list[] = array('url' => 'cost_manage/CostManageOutput', 'class' => 'far fa-file-alt', 'value' => '出力');
		} else {
			$link_list[] = array('url' => 'cost_manage/CostManageBulkOutput', 'class' => 'fas fa-cloud-download-alt', 'value' => '一括DL');
		}
		
		return $link_list;
	}
}
?>