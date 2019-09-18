<?php

/**
 * TrainingUploadModel
 * @author takanori_gozu
 *
 */
class TrainingUploadModel extends TrainingBaseModel {
	
	/**
	 * バリデーション
	 */
	public function validation($training_type, $file_name) {
		
		$msgs = array();
		
		$category_info = $this->get_training_category($training_type);
		$chk_file_name = $category_info[TrainingCategoryDao::COL_TEXT_FILE_NAME];
		
		if ($chk_file_name != $file_name) {
			$msgs[] = 'ファイル名が正しくありません。';
		}
		
		//ファイルサイズチェック
		if ($_FILES["up_file"]["error"] == 1 || $_FILES["up_file"]["error"] == 2) {
			//1…php.iniで設定されているupload_max_sizeを超えている場合に返される
			//2…htmlのhiddenで持っているMAX_FILE_SIZEを超えている場合に返される
			$msgs[] = $this->lang->line('err_file_bigger', array('3MB'));
		}
		
		return $msgs;
	}
	
	/**
	 * テキストのアップロード
	 */
	public function file_upload($file_name) {
		
		$this->load->model('common/FileOperationModel', 'file');
		
		$upload_dir = $this->lang->line('training_text_dir');
		
		return $this->file->upload($upload_dir, $_FILES["up_file"]["tmp_name"], $file_name);
	}
}
?>