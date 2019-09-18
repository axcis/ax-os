<?php

/**
 * FileOperationModel
 * @author takanori_gozu
 *
 */
class FileOperationModel extends MY_Model {
	
	/**
	 * ディレクトリ内のファイル一覧を取得
	 */
	public function get_files($dir) {
		
		$files = array();
		
		if ($handle = opendir($dir)) {
			while (($file = readdir($handle)) !== false) {
				if (filetype($path = $dir.$file) == "file") {
					$files[] = $file;
				}
			}
		}
		
		return $files;
	}
	
	/**
	 * ディレクトリの作成
	 */
	public function make_dir($dir) {
		mkdir($dir, 0755, true);
	}
	
	/**
	 * 表記の置換
	 * 英語→日本語
	 */
	public function replace_en_jp($en, $jp, $param) {
		return str_replace($en, $jp, $param);
	}
	
	/**
	 * 表記の置換
	 * 日本語→英語
	 */
	public function replace_jp_en($jp, $en, $param) {
		return str_replace($jp, $en, $param);
	}
	
	/**
	 * ダウンロード
	 */
	public function download($path, $file_name = 'test') {
		
		$this->load->helper('download');
		
		$data = file_get_contents($path);
		
		force_download($file_name, $data);
	}
	
	/**
	 * アップロード
	 */
	public function upload($path, $tmp_name, $upload_name) {
		return move_uploaded_file($tmp_name, $path. '/'. $upload_name);
	}
}
?>