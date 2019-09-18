<?php

/**
 * TrainingAnswerCheckModel
 * @author takanori_gozu
 *
 */
class TrainingAnswerCheckModel extends TrainingBaseModel {
	
	/**
	 * 項目一覧
	 */
	public function get_list_col() {
		
		$list_col = array();
		
		$list_col[] = array('width' => 300, 'value' => '氏名');
		$list_col[] = array('width' => 150, 'value' => '点数');
		$list_col[] = array('width' => 150, 'value' => '受講日(最終)');
		
		return $list_col;
	}
	
	/**
	 * 受講状況一覧
	 */
	public function get_list($type) {
		
		if ($type == '') return null;
		
		$list = array();
		
		//社員情報
		$this->set_table(EmployeeDao::TABLE_NAME, self::DB_MASTER);
		
		$this->add_select(EmployeeDao::COL_ID);
		$this->add_select(EmployeeDao::COL_NAME);
		
		$this->add_where(EmployeeDao::COL_NAME, $this->lang->line('administrator'), self::COMP_NOT_EQUAL);
		$this->add_where(EmployeeDao::COL_RETIREMENT, '0'); //退職者は除く
		
		$employee_list = $this->do_select();
		
		$this->set_table(TrainingQuestionAnswerDao::TABLE_NAME, self::DB_TRAN);
		
		$this->add_select(TrainingQuestionAnswerDao::COL_EMPLOYEE_ID);
		$this->add_select(TrainingQuestionAnswerDao::COL_POINT);
		$this->add_select(TrainingQuestionAnswerDao::COL_ANSWER_DATE);
		
		$this->add_where(TrainingQuestionAnswerDao::COL_TRAINING_TYPE, $type);
		
		$answer_info = $this->list_to_map($this->do_select(), TrainingQuestionAnswerDao::COL_EMPLOYEE_ID);
		
		foreach($employee_list as $row) {
			$employee_id = $row[EmployeeDao::COL_ID];
			$point = 0;
			$answer_date = '';
			$name = $row[EmployeeDao::COL_NAME];
			if (array_key_exists($employee_id, $answer_info) === true) {
				$point = $answer_info[$employee_id][TrainingQuestionAnswerDao::COL_POINT];
				$answer_date= $answer_info[$employee_id][TrainingQuestionAnswerDao::COL_ANSWER_DATE];
			}
			$list[] = array(
					'name' => $name,
					'point' => $point,
					'answer_date' => $answer_date == '' ? '' : date('Y年n月j日',  strtotime($answer_date)));
		}
		
		//ソート(点数の昇順)
		$sort = array();
		foreach ($list as $key => $value) {
			$sort[$key] = $value['point'];
		}
		
		array_multisort($sort, SORT_ASC, $list);
		
		return $list;
	}
}
?>