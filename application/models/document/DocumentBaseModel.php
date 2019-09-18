<?php

/**
 * DocumentBaseModel
 * 社内文書管理一覧の共通モデル
 * @author takanori_gozu
 *
 */
class DocumentBaseModel extends MY_Model {
	
	/**
	 * 区分のマッピング
	 */
	public function get_category_map() {
		
		$this->set_table(DocumentCategoryDao::TABLE_NAME, self::DB_MASTER);
		
		$this->add_select(DocumentCategoryDao::COL_ID);
		$this->add_select(DocumentCategoryDao::COL_CATEGORY_NAME);
		
		$list = $this->do_select();
		
		$map = array();
		$map[''] = '区分を選択';
		
		foreach ($list as $row) {
			$map[$row[DocumentCategoryDao::COL_ID]] = $row[DocumentCategoryDao::COL_ID]. '_'. $row[DocumentCategoryDao::COL_CATEGORY_NAME];
		}
		
		return $map;
	}
}
?>