<?php

/**
 * CostManageRegistController
 * @author takanori_gozu
 *
 */
class CostManageRegist extends MY_Controller {
	
	public function regist_input() {
		
		$this->load->model('cost_manage/CostManageRegistModel', 'model');
		
		$this->set('action', 'regist');
		$this->set('class_path', 'cost_manage/CostManage');
		$this->set('upload', '1');
		
		$this->set('input_type_map', $this->model->get_input_type_map());
		
		$this->view('cost_manage/cost_manage_input');
	}
	
	/**
	 * 新規登録
	 */
	public function regist() {
		
		$this->load->model('cost_manage/CostManageRegistModel', 'model');
		$this->load->library('dao/ExpensesDao');
		
		$input = $this->get_attribute();
		
		$msgs = $this->model->validation($input);
		
		if ($msgs != null) {
			$this->set_err_info($msgs);
			$this->set('upload', '1');
			$this->set('input_type_map', $this->model->get_input_type_map());
			$this->view('cost_manage/cost_manage_input');
			return;
		}
		
		$insert_id = $this->model->db_regist($input);
		
		if ($insert_id > 0 && $_FILES['receipt_file']['size'] !== 0) {
			$result = $this->model->file_upload();
			if ($result === false) {
				//アップロード失敗時
				$this->model->db_delete($insert_id);
				$this->show_dialog('登録に失敗しました。');
			}
		}
		
		$this->redirect_js(base_url(). $this->get('class_path'). 'List');
	}
	
	public function modify_input($id) {
		
		$this->load->model('cost_manage/CostManageRegistModel', 'model');
		$this->load->library('dao/ExpensesDao');
		
		$info = $this->model->get_info($id);
		
		$this->set_attribute($info);
		$this->set('action', 'modify');
		$this->set('class_path', 'cost_manage/CostManage');
		$this->set('upload', '1');
		
		$this->set('input_type_map', $this->model->get_input_type_map());
		
		$this->view('cost_manage/cost_manage_input');
	}
	
	/**
	 * 更新
	 */
	public function modify() {
		
		$this->load->model('cost_manage/CostManageRegistModel', 'model');
		$this->load->library('dao/ExpensesDao');
		
		$input = $this->get_attribute();
		
		$msgs = $this->model->validation($input);
		
		if ($msgs != null) {
			$this->set_err_info($msgs);
			$this->set('input_type_map', $this->model->get_input_type_map());
			$this->view('cost_manage/cost_manage_input');
			return;
		}
		
		$this->model->db_modify($input);
		
		$this->redirect_js(base_url(). $this->get('class_path'). 'List');
	}
	
	/**
	 * 削除
	 */
	public function delete() {
		
		$this->load->model('cost_manage/CostManageRegistModel', 'model');
		$this->load->library('dao/ExpensesDao');
		
		$id = $this->get('id');
		
		//ファイル情報を取得する
		$info = $this->model->get_info($id);
		
		if ($info[ExpensesDao::COL_RECEIPT_FILE_NAME] != '') {
			//ファイル削除
			$this->model->file_delete($info);
		}
		
		$this->model->db_delete($id);
		
		$this->redirect_js(base_url(). $this->get('class_path'). 'List');
	}
}
?>