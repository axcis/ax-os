<?php

/**
 * CostManageUploadModel
 * @author takanori_gozu
 *
 */
class CostManageUploadModel extends CostManageBaseModel {
	
	/**
	 * バリデーション
	 */
	public function validation($ym, $file_name) {
		
		$exts = array('jpeg', 'jpg', 'pdf', 'JPEG', 'JPG', 'PDF', 'xlsx', 'xls', 'XLSX', 'XLS'); //拡張子チェック用
		$msgs = array();
		
		$before_len = 0;
		$after_len = 0;
		
		$before_len = mb_strlen($_FILES["up_file"]["name"]);
		$after_len = mb_strlen(mb_convert_encoding($file_name, 'UTF-8', 'SJIS'));
		
		if ($before_len != $after_len) {
			//環境依存文字対応
			$msgs[] = $this->lang->line('err_file_upload_env_character');
		}
		//ファイルサイズチェック
		if ($_FILES["up_file"]["error"] == 1 || $_FILES["up_file"]["error"] == 2) {
			//1…php.iniで設定されているupload_max_sizeを超えている場合に返される
			//2…htmlのhiddenで持っているMAX_FILE_SIZEを超えている場合に返される
			$msgs[] = $this->lang->line('err_file_bigger', array('3MB'));
		}
		//拡張子チェック
		$arr = explode(".", $file_name);
		$ext = $arr[1];
		if (!in_array($ext, $exts)) {
			$msgs[] = $this->lang->line('err_unmatch_ext', array(implode(",", $exts)));
		}
		//同一ファイルが存在している場合
		$file_path = $this->lang->line('upload_dir'). $this->get_session('login_id'). "/". $ym. "/". $file_name;
		if (file_exists($file_path)) {
			$msgs[] = $this->lang->line('err_file_exist');
		}
		
		return $msgs;
	}
	
	/**
	 * ファイルのアップロード
	 */
	public function file_upload($ym, $file_name) {
		
		$this->load->model('common/FileOperationModel', 'file');
		
		$upload_dir = $this->lang->line('upload_dir'). $this->get_session('login_id'). "/". $ym;
		
		if (!file_exists($upload_dir)) {
			$this->file->make_dir($upload_dir); //ディレクトリ作成
		}
		
		return $this->file->upload($upload_dir, $_FILES["up_file"]["tmp_name"], $file_name);
	}
}
?>