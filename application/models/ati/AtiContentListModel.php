<?php

/**
 * AtiContentListModel
 * @author takanori_gozu
 *
 */
class AtiContentListModel extends AtiBaseModel {
	
	/**
	 * コンテンツ一覧
	 */
	public function get_list($category_id) {
		
		$this->set_table(AtiContentDao::TABLE_NAME, self::DB_TRAN);
		
		$this->add_select(AtiContentDao::COL_ID);
		$this->add_select(AtiContentDao::COL_TITLE);
		
		$this->add_where(AtiContentDao::COL_CATEGORY_ID, $category_id);
		$this->add_order(AtiContentDao::COL_ID);
		
		return $this->do_select();
	}
	
	/**
	 * 一覧項目
	 */
	public function get_list_col() {
		
		$list_col = array();
		
		$list_col[] = array('width' => 70, 'value' => '編集');
		$list_col[] = array('width' => 70, 'value' => 'No');
		$list_col[] = array('width' => 300, 'value' => 'タイトル');
		$list_col[] = array('width' => 120, 'value' => '詳細');
		
		return $list_col;
	}
	
	/**
	 * リンク
	 */
	public function get_link($category_id) {
		
		$link_list = array();
		
		if (($this->get_session('user_level') == self::LEVEL_LEADER) || ($this->get_session('user_level') == self::LEVEL_SUB_LEADER)) {
			//コンテンツの登録はリーダー・サブリーダーのみ
			$link_list[] = array('url' => 'ati/AtiContentRegist/regist_input/'. $category_id, 'class' => 'far fa-edit', 'value' => '登録');
		}
		
		return $link_list;
	}
	
	/**
	 * 詳細
	 */
	public function get_info($category_id, $id) {
		
		$this->set_table(AtiContentDao::TABLE_NAME, self::DB_TRAN);
		
		$this->add_where(AtiContentDao::COL_ID, $id);
		$this->add_where(AtiContentDao::COL_CATEGORY_ID, $category_id);
		
		return $this->do_select_info();
	}
	
	/**
	 * Imgタグを変換(Path追加)
	 */
	public function change_img_tag(&$info) {
		
		$content = $info[AtiContentDao::COL_CONTENT];
		$add = $this->lang->line('ati_files_url'). $info[AtiContentDao::COL_CATEGORY_ID]. '/'. $info[AtiContentDao::COL_ID]. '/';
		
		$replace_content = preg_replace('/(<img[\s]+[^>]*?src=["\']?)/i', "$1$add", $content);
		
		$info[AtiContentDao::COL_CONTENT] = $replace_content;
	}
	
	/**
	 * 前後のコンテンツのリンク
	 */
	public function get_content_link_id($category_id, $get_id) {
		
		$this->set_table(AtiContentDao::TABLE_NAME, self::DB_TRAN);
		
		$this->add_select(AtiContentDao::COL_ID);
		$this->add_where(AtiContentDao::COL_ID, $get_id);
		$this->add_where(AtiContentDao::COL_CATEGORY_ID, $category_id);
		
		$info = $this->do_select_info();
		
		return $info[AtiContentDao::COL_ID] != null ? $info[AtiContentDao::COL_ID] : 0;
	}
	
	/**
	 * 課題のDL
	 */
	public function text_output($category_id, $id) {
		
		$this->load->model('common/FileOperationModel', 'file');
		
		$file_path = $this->lang->line('ati_content_dir'). $category_id. '/'. $id .'/'. 'text.zip';
		
		$this->file->download($file_path, $category_id. '-'. $id .'課題ファイル.zip');
	}
}
?>