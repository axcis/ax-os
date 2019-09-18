<?php

/**
 * TrainingListModel
 * @author takanori_gozu
 *
 */
class TrainingListModel extends TrainingBaseModel {
	
	/**
	 * 受講コンテンツ
	 */
	public function get_content_list() {
		
		$contents_list = array();
		
		$category_info = $this->get_training_category();
		
		foreach ($category_info as $category) {
			$contents_list[] = array('btn_name' => $category[TrainingCategoryDao::COL_TRAINING_NAME], 
												 'type' => $category[TrainingCategoryDao::COL_ID], 
												 'detail' => $category[TrainingCategoryDao::COL_TRAINING_INFO]);
		}
		
		return $contents_list;
	}
	
	/**
	 * リンク
	 */
	public function get_link() {
		
		$link_list = array();
		
		if ($this->get_session('user_level') <= self::LEVEL_LEADER) {
			$link_list[] = array('url' => 'training/TrainingAnswerCheck', 'class' => 'far fa-check-circle', 'value' => 'チェック', 'popup' => '1');
		}
		
		if ($this->get_session('user_level') == self::LEVEL_ADMINISTRATOR) {
			$link_list[] = array('url' => 'training/TrainingUpload', 'class' => 'fas fa-cloud-upload-alt', 'value' => 'テキスト');
		}
		
		return $link_list;
	}
	
	/**
	 * 点数取得
	 */
	public function get_point($type) {
		
		$this->set_table(TrainingQuestionAnswerDao::TABLE_NAME, self::DB_TRAN);
		
		//登録済みか確認
		$this->add_where(TrainingQuestionAnswerDao::COL_EMPLOYEE_ID, $this->get_session('user_id'));
		$this->add_where(TrainingQuestionAnswerDao::COL_TRAINING_TYPE, $type);
		
		$result = $this->do_select_info();
		
		return $result[TrainingQuestionAnswerDao::COL_POINT];
	}
	
	/**
	 * 問題一覧
	 */
	public function get_question_list($type) {
		
		$this->set_table(TrainingQuestionDao::TABLE_NAME, self::DB_MASTER);
		
		$this->add_select(TrainingQuestionDao::COL_ID);
		$this->add_select(TrainingQuestionDao::COL_QUESTION);
		$this->add_select(TrainingQuestionDao::COL_ANSWER_LIST);
		$this->add_select(TrainingQuestionDao::COL_ANSWER);
		$this->add_select(TrainingQuestionDao::COL_POINT);
		
		$this->add_where(TrainingQuestionDao::COL_TRAINING_TYPE, $type);
		$this->add_order(TrainingQuestionDao::COL_ID);
		
		$list = $this->do_select();
		
		//解答群をマップに変換
		foreach ($list as &$row) {
			$answer_list = $row[TrainingQuestionDao::COL_ANSWER_LIST];
			parse_str($answer_list, $answers);
			$row[TrainingQuestionDao::COL_ANSWER_LIST] = $answers;
		}
		
		return $list;
	}
	
	/**
	 * 研修名・テキスト名
	 */
	public function get_question_name($type) {
		
		$category_info = $this->get_training_category($type);
		
		return $category_info[TrainingCategoryDao::COL_TRAINING_NAME]. "研修";
	}
	
	/**
	 * テキストのダウンロード
	 */
	public function text_output($type) {
		
		$this->load->model('common/FileOperationModel', 'file');
		
		$category_info = $this->get_training_category($type);
		
		$text_name = $category_info[TrainingCategoryDao::COL_TEXT_FILE_NAME];
		$file_name = $category_info[TrainingCategoryDao::COL_DL_TEXT_NAME];
		
		$path = $this->lang->line('training_text_dir'). $text_name;
		
		$this->file->download($path, $file_name);
	}
	
	/**
	 * 解答情報
	 */
	public function get_answer_info($input) {
		
		$info = array();
		
		foreach ($input as $key => $value) {
			if(strpos($key, 'answer_list') !== false){
				$id = substr($key, 11);
				$info[$id] = $value[0]; //0で固定
			}
		}
		
		return $info;
	}
	
	/**
	 * 点数を取得
	 */
	public function get_answer_point($answer_info, $type) {
		
		$point = 0;
		
		$this->set_table(TrainingQuestionDao::TABLE_NAME, self::DB_MASTER);
		
		$this->add_select(TrainingQuestionDao::COL_ID);
		$this->add_select(TrainingQuestionDao::COL_ANSWER);
		$this->add_select(TrainingQuestionDao::COL_POINT);
		
		$this->add_where(TrainingQuestionDao::COL_TRAINING_TYPE, $type);
		
		$this->add_order(TrainingQuestionDao::COL_ID);
		
		$list = $this->do_select();
		
		foreach ($list as $row) {
			$answer = $answer_info[$row[TrainingQuestionDao::COL_ID]];
			if ($answer == $row[TrainingQuestionDao::COL_ANSWER]) {
				$point += $row[TrainingQuestionDao::COL_POINT];
			}
		}
		
		return $point;
	}
	
	/**
	 * 解答状況の更新
	 */
	public function db_insert_update($point, $type) {
		
		$this->set_table(TrainingQuestionAnswerDao::TABLE_NAME, self::DB_TRAN);
		
		//登録済みか確認
		$this->add_where(TrainingQuestionAnswerDao::COL_EMPLOYEE_ID, $this->get_session('user_id'));
		$this->add_where(TrainingQuestionAnswerDao::COL_TRAINING_TYPE, $type);
		
		$result = $this->do_select_info();
		
		$this->set_table(TrainingQuestionAnswerDao::TABLE_NAME, self::DB_TRAN);
		
		if ($result != null) {
			//更新
			$this->add_col_val(TrainingQuestionAnswerDao::COL_POINT, $point);
			$this->add_col_val(TrainingQuestionAnswerDao::COL_ANSWER_DATE, date('Y-m-d'));
			$this->add_where(TrainingQuestionAnswerDao::COL_EMPLOYEE_ID, $this->get_session('user_id'));
			$this->add_where(TrainingQuestionAnswerDao::COL_TRAINING_TYPE, $type);
			$this->do_update();
		} else {
			//登録
			$this->add_col_val(TrainingQuestionAnswerDao::COL_EMPLOYEE_ID, $this->get_session('user_id'));
			$this->add_col_val(TrainingQuestionAnswerDao::COL_TRAINING_TYPE, $type);
			$this->add_col_val(TrainingQuestionAnswerDao::COL_POINT, $point);
			$this->add_col_val(TrainingQuestionAnswerDao::COL_ANSWER_DATE, date('Y-m-d'));
			$this->do_insert();
		}
	}
}
?>