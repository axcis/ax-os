<?php

/**
 * FaqListModel
 * @author takanori_gozu
 *
 */
class FaqListModel extends MY_Model {
	
	/**
	 * 一覧
	 */
	public function get_list($search = null) {
		
		$this->set_table(FaqDao::TABLE_NAME, self::DB_TRAN);
		
		$this->add_select(FaqDao::COL_ID);
		$this->add_select(FaqDao::COL_TITLE);
		$this->add_select(FaqDao::COL_QUESTION);
		
		if ($search != null) {
			$this->set_search_like($search, FaqDao::COL_TITLE, 'search_title');
			$this->set_search_like($search, FaqDao::COL_QUESTION, 'search_question');
		}
		
		$this->add_order(FaqDao::COL_ID);
		
		return $this->do_select();
	}
	
	/**
	 * 一覧項目
	 */
	public function get_list_col() {
		
		$list_col = array();
		
		if ($this->get_session('user_level') == self::LEVEL_ADMINISTRATOR) {
			$list_col[] = array('width' => 70, 'value' => '編集');
		}
		$list_col[] = array('width' => 300, 'value' => 'タイトル');
		$list_col[] = array('width' => 300, 'value' => '質問内容');
		$list_col[] = array('width' => 120, 'value' => '詳細');
		
		return $list_col;
	}
	
	/**
	 * リンク
	 */
	public function get_link() {
		
		$link_list = array();
		
		if ($this->get_session('user_level') == self::LEVEL_ADMINISTRATOR) {
			$link_list[] = array('url' => 'faq/FaqRegist/regist_input', 'class' => 'far fa-edit', 'value' => '登録');
		}
		
		return $link_list;
	}
	
	/**
	 * 詳細
	 */
	public function get_info($id) {
		
		$this->set_table(FaqDao::TABLE_NAME, self::DB_TRAN);
		
		$this->add_select(FaqDao::COL_TITLE);
		$this->add_select(FaqDao::COL_QUESTION);
		$this->add_select(FaqDao::COL_ANSWER);
		
		$this->add_where(FaqDao::COL_ID, $id);
		
		return $this->do_select_info();
	}
}
?>