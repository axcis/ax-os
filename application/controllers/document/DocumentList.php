<?php

/**
 * DocumentListController
 * @author takanori_gozu
 *
 */
class DocumentList extends MY_Controller {
	
	/**
	 * Index
	 */
	public function index() {
		
		$this->load->model('document/DocumentListModel', 'model');
		$this->load->library('dao/DocumentCategoryDao');
		$this->load->library('dao/DocumentInfoDao');
		
		$this->set('class_key', 'document');
		$this->set('class_path', 'document/Document');
		
		$this->set('list', $this->model->get_list());
		$this->set('list_col', $this->model->get_list_col());
		$this->set('link', $this->model->get_link());
		
		$this->set('category_map', $this->model->get_category_map());
		
		$this->view('document/document_list');
	}
	
	/**
	 * 検索
	 */
	public function search() {
		
		$this->load->model('document/DocumentListModel', 'model');
		$this->load->library('dao/DocumentCategoryDao');
		$this->load->library('dao/DocumentInfoDao');
		
		$search = $this->get_attribute();
		
		$this->set('class_key', 'document');
		$this->set('class_path', 'document/Document');
		
		$this->set('list', $this->model->get_list($search));
		$this->set('list_col', $this->model->get_list_col());
		$this->set('link', $this->model->get_link());
		
		$this->set('category_map', $this->model->get_category_map());
		
		$this->view('document/document_list');
	}
	
	/**
	 * ダウンロード
	 */
	public function download($id) {
		
		$this->load->model('document/DocumentListModel', 'model');
		$this->load->library('dao/DocumentInfoDao');
		
		$this->model->document_output($id);
	}
	
	/**
	 * 削除
	 */
	public function delete() {
		
		$this->load->model('document/DocumentListModel', 'model');
		$this->load->library('dao/DocumentInfoDao');
		
		$del_ids = $this->get('del_ids');
		
		$infos = $this->model->get_infos($del_ids);
		
		//データ、ファイル削除
		$this->model->info_delete($del_ids, $infos);
		
		$this->show_dialog($this->lang->line('db_deleted'));
		$this->redirect_js(base_url(). 'document/DocumentList');
	}
}
?>