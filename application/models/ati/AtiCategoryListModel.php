<?php

/**
 * AtiCategoryListModel
 * @author takanori_gozu
 *
 */
class AtiCategoryListModel extends AtiBaseModel {
	
	/**
	 * カテゴリ一覧
	 */
	public function get_category_list() {
		
		$this->set_table(AtiCategoryDao::TABLE_NAME, self::DB_MASTER);
		
		$this->add_select(AtiCategoryDao::COL_ID);
		$this->add_select(AtiCategoryDao::COL_CATEGORY_NAME);
		$this->add_select(AtiCategoryDao::COL_CATEGORY_DETAIL);
		
		$this->add_order(AtiCategoryDao::COL_ID);
		
		$list = $this->do_select();
		
		$category_list = array();
		
		foreach ($list as $row) {
			$category_list[$row[AtiCategoryDao::COL_ID]] = array(
					'btn_name' => $row[AtiCategoryDao::COL_CATEGORY_NAME],
					'detail' => $row[AtiCategoryDao::COL_CATEGORY_DETAIL]);
		}
		
		return $category_list;
	}
}
?>