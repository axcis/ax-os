<?php

/**
 * AtiContentRegistModel
 * @author takanori_gozu
 *
 */
class AtiContentRegistModel extends AtiBaseModel {
	
	/**
	 * 最新のコンテンツNoを自動取得
	 */
	public function get_next_content_no($category_id) {
		
		$this->set_table(AtiContentDao::TABLE_NAME, self::DB_TRAN);
		
		$this->add_select_as("MAX(id)", 'last_id');
		$this->add_where(AtiContentDao::COL_CATEGORY_ID, $category_id);
		
		$info = $this->do_select_info();
		
		if ($info['last_id'] == null) return 1;
		
		return $info['last_id'] + 1;
	}
	
	/**
	 * 詳細
	 */
	public function get_info($category_id, $id) {
		
		$this->set_table(AtiContentDao::TABLE_NAME, self::DB_TRAN);
		
		$this->add_select(AtiContentDao::COL_ID);
		$this->add_select(AtiContentDao::COL_CATEGORY_ID);
		$this->add_select(AtiContentDao::COL_TITLE);
		$this->add_select(AtiContentDao::COL_CONTENT);
		$this->add_where(AtiContentDao::COL_ID, $id);
		$this->add_where(AtiContentDao::COL_CATEGORY_ID, $category_id);
		
		return $this->do_select_info();
	}
	
	/**
	 * バリデーション
	 */
	public function validation($input) {
		
		$act = $input['action'];
		$title = $input['title'];
		$content = $input['content'];
		$file_total_size = 0;
		
		$msgs = array();
		
		//未入力チェック
		if (trim($title) == '') $msgs[] = $this->lang->line('err_required', array($this->lang->line('title')));
		if (trim($content) == '') $msgs[] = $this->lang->line('err_required', array($this->lang->line('content')));
		
		if ($msgs != null) return $msgs;
		
		//文字数チェック
		if (mb_strlen(trim($title)) > 50) $msgs[] = $this->lang->line('err_max_length', array($this->lang->line('title'), 50));
		
		if ($msgs != null) return $msgs;
		
		//課題ファイルのチェック
		if ($act == 'regist') {
			if ($_FILES["exam_text_file"]["name"] != 'text.zip') {
				$msgs[] = '課題ファイルの名前はtext.zipでアップロードしてください。';
			}
		}
		
		$file_total_size += $_FILES["exam_text_file"]['size'];
		
		//画像ファイル
		if ($_FILES['content_img_files']["tmp_name"][0] != '') {
			for($i = 0; $i < count($_FILES["content_img_files"]["name"]); $i++ ){
				$file_name = mb_convert_encoding($_FILES["content_img_files"]["name"][$i], 'SJIS', 'UTF-8');
				$before_len = mb_strlen($_FILES["content_img_files"]["name"][$i]);
				$after_len = mb_strlen(mb_convert_encoding($file_name, 'UTF-8', 'SJIS'));
				if ($before_len != $after_len) {
					$msgs[] = $this->lang->line('err_file_upload_env_character');
					break;
				}
				if ($_FILES["content_img_files"]["error"][$i] == 1 || $_FILES["content_img_files"]["error"][$i] == 2) {
					$msgs[] = $this->lang->line('err_file_bigger', array('20MB'));
					break;
				}
				//コンテンツ内に画像ファイル名の文字列が存在するかチェックする
				if (strpos($content, $file_name) === false) {
					$msgs[] = "コンテンツ内に $file_name が指定されていません。";
					break;
				}
				$file_total_size += $_FILES["content_img_files"]['size'][$i];
			}
		}
		
		if ($file_total_size > 20971520) $msgs[] = 'ファイルの総合計サイズは20MBまでです。';
		
		return $msgs;
	}
	
	/**
	 * 新規登録
	 */
	public function db_regist($input) {
		
		$this->set_table(AtiContentDao::TABLE_NAME, self::DB_TRAN);
		
		$this->add_col_val(AtiContentDao::COL_ID, $input['id']);
		$this->add_col_val(AtiContentDao::COL_CATEGORY_ID, $input['category_id']);
		$this->add_col_val_str(AtiContentDao::COL_TITLE, $input['title']);
		$this->add_col_val_str(AtiContentDao::COL_CONTENT, $input['content']);
		if (isset($_FILES['content_img_files'])) {
			$this->add_col_val_str(AtiContentDao::COL_CONTENT_IMG_FILES, implode(',', $_FILES['content_img_files']['name']));
		}
		
		return $this->do_insert_get_rows();
	}
	
	/**
	 * 更新
	 */
	public function db_modify($input) {
		
		$this->set_table(AtiContentDao::TABLE_NAME, self::DB_TRAN);
		
		$this->add_col_val(AtiContentDao::COL_TITLE, $input['title']);
		$this->add_col_val(AtiContentDao::COL_CONTENT, $input['content']);
		if (isset($_FILES['content_img_files'])) {
			$this->add_col_val(AtiContentDao::COL_CONTENT_IMG_FILES, implode(',', $_FILES['content_img_files']['name']));
		}
		
		$this->add_where(AtiContentDao::COL_CATEGORY_ID, $input['category_id']);
		$this->add_where(AtiContentDao::COL_ID, $input['id']);
		
		$this->do_update();
		
		return true;
	}
	
	/**
	 * アップロード
	 */
	public function file_upload($input) {
		
		$this->load->model('common/FileOperationModel', 'file');
		
		$upload_dir = $this->lang->line('ati_content_dir'). $input['category_id']. "/". $input['id'];
		
		if (!file_exists($upload_dir)) {
			$this->file->make_dir($upload_dir); //ディレクトリ作成
		}
		
		//課題ファイル
		if ($_FILES['exam_text_file']["tmp_name"] != '') {
			$this->file->upload($upload_dir, $_FILES["exam_text_file"]["tmp_name"], $_FILES["exam_text_file"]["name"]);
		}
		
		//画像ファイル
		if ($_FILES['content_img_files']["tmp_name"][0] != '') {
			for($i = 0; $i < count($_FILES["content_img_files"]["name"]); $i++ ){
				$this->file->upload($upload_dir, $_FILES["content_img_files"]["tmp_name"][$i], $_FILES["content_img_files"]["name"][$i]);
			}
		}
	}
}
?>