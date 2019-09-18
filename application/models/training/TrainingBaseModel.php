<?php

/**
 * TrainingBaseModel
 * 社内研修の共通モデル
 * @author takanori_gozu
 *
 */
class TrainingBaseModel extends MY_Model {
	
	/**
	 * 研修タイプ
	 */
	public function get_training_type_map($no_select = true) {
		
		$map = array();
		$category_info = $this->get_training_category();
		
		if ($no_select) {
			$map[''] = '研修を選択';
		}
		
		foreach ($category_info as $category) {
			$key = $category[TrainingCategoryDao::COL_ID];
			$value = $category[TrainingCategoryDao::COL_TRAINING_NAME];
			$map[$key] = $value;
		}
		
		return $map;
	}
	
	/**
	 * 社内研修のカテゴリ情報
	 */
	protected function get_training_category($type = '') {
		
		$this->set_table(TrainingCategoryDao::TABLE_NAME, self::DB_MASTER);
		
		if ($type != '') {
			$this->add_where(TrainingCategoryDao::COL_ID, $type);
			return $this->do_select_info();
		}
		
		$this->add_order(TrainingCategoryDao::COL_ID);
		
		return $this->list_to_map($this->do_select());
	}
}
?>