<?php

/**
 * AtiBaseModel
 * Eラーニング画面の共通モデル
 * @author takanori_gozu
 *
 */
class AtiBaseModel extends MY_Model {
	
	/**
	 * カテゴリ情報
	 */
	public function get_category_info($id) {
		
		$this->set_table(AtiCategoryDao::TABLE_NAME, self::DB_MASTER);
		
		$this->add_where(AtiCategoryDao::COL_ID, $id);
		
		return $this->do_select_info();
	}
}
?>