<?php

/**
 * DocumentListModel
 * @author takanori_gozu
 *
 */
class DocumentListModel extends DocumentBaseModel {
	
	/**
	 * 一覧
	 */
	public function get_list($search = null) {
		
		$this->set_table(DocumentInfoDao::TABLE_NAME, self::DB_TRAN);
		
		$this->add_select(DocumentInfoDao::COL_ID);
		$this->add_select(DocumentInfoDao::COL_CATEGORY_ID);
		$this->add_select(DocumentInfoDao::COL_FILE_NAME);
		
		if ($search != null) {
			$this->set_search($search, DocumentInfoDao::COL_CATEGORY_ID, 'search_category');
			$this->set_search_like($search, DocumentInfoDao::COL_FILE_NAME, 'search_file_name');
		}
		
		$this->add_order(DocumentInfoDao::COL_CATEGORY_ID);
		$this->add_order(DocumentInfoDao::COL_ID);
		
		$list = $this->do_select();
		
		$category_map = $this->get_category_map();
		
		foreach ($list as &$row) {
			$row[DocumentInfoDao::COL_CATEGORY_ID] = $category_map[$row[DocumentInfoDao::COL_CATEGORY_ID]];
		}
		
		return $list;
	}
	
	/**
	 * 一覧項目
	 */
	public function get_list_col() {
		
		$list_cols = array();
		
		if ($this->get_session('user_level') == self::LEVEL_ADMINISTRATOR) {
			$list_cols[] = array('width' => 70, 'value' => ''); //削除チェック
		}
		$list_cols[] = array('width' => 150, 'value' => '区分');
		$list_cols[] = array('width' => 300, 'value' => 'ファイル名');
		$list_cols[] = array('width' => 200, 'value' => 'ダウンロード');
		
		return $list_cols;
	}
	
	/**
	 * リンク
	 */
	public function get_link() {
		
		$link_list = array();
		
		if ($this->get_session('user_level') == self::LEVEL_ADMINISTRATOR) {
			$link_list[] = array('url' => 'document/DocumentUpload', 'class' => 'fas fa-cloud-upload-alt', 'value' => 'アップロード');
		}
		
		return $link_list;
	}
	
	/**
	 * ドキュメントのダウンロード
	 */
	public function document_output($id) {
		
		$this->load->model('common/FileOperationModel', 'file');
		
		$this->set_table(DocumentInfoDao::TABLE_NAME, self::DB_TRAN);
		
		$this->add_where(DocumentInfoDao::COL_ID, $id);
		
		$info = $this->do_select_info();
		
		$file_path = $this->lang->line('document_dir'). $info[DocumentInfoDao::COL_CATEGORY_ID]. '/'. mb_convert_encoding($info[DocumentInfoDao::COL_FILE_NAME], 'SJIS', 'UTF-8');
		
		$this->file->download($file_path, $info[DocumentInfoDao::COL_FILE_NAME]);
	}
	
	/**
	 * 削除対象情報を取得する
	 */
	public function get_infos($ids) {
		
		$this->set_table(DocumentInfoDao::TABLE_NAME, self::DB_TRAN);
		
		$this->add_where_in(DocumentInfoDao::COL_ID, $ids);
		
		return $this->do_select();
	}
	
	/**
	 * データ削除
	 */
	public function info_delete($ids, $infos) {
		
		//DB削除
		$this->set_table(DocumentInfoDao::TABLE_NAME, self::DB_TRAN);
		
		$this->add_where_in(DocumentInfoDao::COL_ID, $ids);
		
		$this->do_delete();
		
		//ファイル削除
		foreach ($infos as $info) {
			$path = $this->lang->line('document_dir'). $info[DocumentInfoDao::COL_CATEGORY_ID]. '/'. mb_convert_encoding($info[DocumentInfoDao::COL_FILE_NAME], 'SJIS', 'UTF-8');
			unlink($path);
		}
	}
}
?>