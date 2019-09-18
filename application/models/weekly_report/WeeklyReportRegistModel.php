<?php

/**
 * WeeklyReportRegistModel
 * @author takanori_gozu
 *
 */
class WeeklyReportRegistModel extends MY_Model {
	
	/**
	 * バリデーション
	 */
	public function validation($input) {
		
		$act = $input['action'];
		$id = $input['id'];
		$standard_date = $input['standard_date'];
		$project_name = $input['project_name'];
		$work_content = $input['work_content'];
		$reflect = $input['reflect'];
		
		$msgs = array();
		
		if (trim($project_name) == '') $msgs[] = $this->lang->line('err_required', array($this->lang->line('project_name')));
		if (trim($work_content) == '') $msgs[] = $this->lang->line('err_required', array($this->lang->line('work_content')));
		if (trim($reflect) == '') $msgs[] = $this->lang->line('err_required', array($this->lang->line('reflect')));
		
		//プロジェクト名は50文字以内
		if (mb_strlen(trim($project_name)) > 50) $msgs[] = $this->lang->line('err_max_length', array($this->lang->line('project_name'), 50));
		
		if ($msgs != null) return $msgs;
		
		//重複登録不可
		$this->set_table(WeeklyReportDao::TABLE_NAME, self::DB_TRAN);
		
		$this->add_where(WeeklyReportDao::COL_STANDARD_DATE, $standard_date);
		if ($act == 'modify') $this->add_where(WeeklyReportDao::COL_ID, $id, self::COMP_NOT_EQUAL);
		$this->add_where(WeeklyReportDao::COL_REGIST_USER_ID, $this->get_session('user_id'));
		
		$count = $this->do_count();
		
		if ($count > 0) $msgs[] = $this->lang->line('err_already_regist', array($this->lang->line('standard_date')));
		
		return $msgs;
	}
	
	/**
	 * 詳細
	 */
	public function get_info($id) {
		
		$this->set_table(WeeklyReportDao::TABLE_NAME, self::DB_TRAN);
		
		$this->add_select(WeeklyReportDao::COL_ID);
		$this->add_select(WeeklyReportDao::COL_PROJECT_NAME);
		$this->add_select(WeeklyReportDao::COL_WORK_CONTENT);
		$this->add_select(WeeklyReportDao::COL_REFLECT);
		$this->add_select(WeeklyReportDao::COL_OTHER);
		$this->add_select(WeeklyReportDao::COL_STANDARD_DATE);
		
		$this->add_where(WeeklyReportDao::COL_ID, $id);
		
		return $this->do_select_info();
	}
	
	/**
	 * 新規登録
	 */
	public function db_regist($input) {
		
		$this->set_table(WeeklyReportDao::TABLE_NAME, self::DB_TRAN);
		
		$this->add_col_val(WeeklyReportDao::COL_REGIST_USER_ID, $this->get_session('user_id'));
		$this->add_col_val(WeeklyReportDao::COL_STANDARD_DATE, $input['standard_date']);
		$this->add_col_val(WeeklyReportDao::COL_PROJECT_NAME, $input['project_name']);
		$this->add_col_val(WeeklyReportDao::COL_WORK_CONTENT, $input['work_content']);
		$this->add_col_val(WeeklyReportDao::COL_REFLECT, $input['reflect']);
		$this->add_col_val(WeeklyReportDao::COL_OTHER, $input['other']);
		
		$this->do_insert();
	}
	
	/**
	 * 編集
	 */
	public function db_modify($input) {
		
		$this->set_table(WeeklyReportDao::TABLE_NAME, self::DB_TRAN);
		
		$this->add_col_val(WeeklyReportDao::COL_STANDARD_DATE, $input['standard_date']);
		$this->add_col_val(WeeklyReportDao::COL_PROJECT_NAME, $input['project_name']);
		$this->add_col_val(WeeklyReportDao::COL_WORK_CONTENT, $input['work_content']);
		$this->add_col_val(WeeklyReportDao::COL_REFLECT, $input['reflect']);
		$this->add_col_val(WeeklyReportDao::COL_OTHER, $input['other']);
		
		$this->add_where(WeeklyReportDao::COL_ID, $input['id']);
		
		$this->do_update();
	}
}
?>