<?php

/**
 * DocumentUploadModel
 * @author takanori_gozu
 *
 */
class DocumentUploadModel extends DocumentBaseModel {
	
	/**
	 * バリデーション
	 */
	public function validation($file_name) {
		
		$msgs = array();
		$before_len = mb_strlen($file_name);
		$after_len = mb_strlen(mb_convert_encoding(mb_convert_encoding($file_name, 'SJIS', 'UTF-8'), 'UTF-8', 'SJIS'));
		
		//ファイル名は200文字(全角100文字)以内
		if (mb_strlen(trim($file_name)) > 100) $msgs[] = $this->lang->line('err_max_length', array($this->lang->line('file_name'), 100));
		
		//ファイルサイズチェック
		if ($_FILES["up_file"]["error"] == 1 || $_FILES["up_file"]["error"] == 2) {
			//1…php.iniで設定されているupload_max_sizeを超えている場合に返される
			//2…htmlのhiddenで持っているMAX_FILE_SIZEを超えている場合に返される
			$msgs[] = $this->lang->line('err_file_bigger', array('5MB'));
		}
		
		if ($before_len != $after_len) {
			//環境依存文字対応
			$msgs[] = $this->lang->line('err_file_upload_env_character');
		}
		
		return $msgs;
	}
	
	/**
	 * データの新規登録
	 * (データ登録済み判定後)
	 */
	public function db_regist($category_id, $file_name) {
		
		//まずはデータが登録済みか確認する
		$this->set_table(DocumentInfoDao::TABLE_NAME, self::DB_TRAN);
		
		$this->add_where(DocumentInfoDao::COL_CATEGORY_ID, $category_id);
		$this->add_where(DocumentInfoDao::COL_FILE_NAME, $file_name);
		
		$count = $this->do_count();
		
		if ($count == 0) {
			//新規登録
			$this->set_table(DocumentInfoDao::TABLE_NAME, self::DB_TRAN);
			$this->add_col_val(DocumentInfoDao::COL_CATEGORY_ID, $category_id);
			$this->add_col_val(DocumentInfoDao::COL_FILE_NAME, $file_name);
			$this->do_insert();
		}
	}
	
	/**
	 * ファイルのアップロード
	 */
	public function file_upload($category_id, $file_name) {
		
		$this->load->model('common/FileOperationModel', 'file');
		
		$upload_dir = $this->lang->line('document_dir'). $category_id. "/";
		
		if (!file_exists($upload_dir)) {
			//パーミッション755で作成
			$this->file->make_dir($upload_dir);
		}
		
		return $this->file->upload($upload_dir, $_FILES["up_file"]["tmp_name"], mb_convert_encoding($file_name, 'SJIS', 'UTF-8'));
	}
}
?>