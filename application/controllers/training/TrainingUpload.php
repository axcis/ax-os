<?php

/**
 * TrainingUploadController
 * @author takanori_gozu
 *
 */
class TrainingUpload extends MY_Controller {
	
	/**
	 * Index
	 */
	public function index() {
		
		$this->load->model('training/TrainingUploadModel', 'model');
		$this->load->library('dao/TrainingCategoryDao');
		
		$this->set('training_type_map', $this->model->get_training_type_map(false));
		
		$this->view('training/training_upload');
	}
	
	/**
	 * アップロード
	 */
	public function upload() {
		
		$this->load->model('training/TrainingUploadModel', 'model');
		$this->load->library('dao/TrainingCategoryDao');
		
		$training_type = $this->get('training_type');
		$file_name = $_FILES['up_file']['name'];
		
		$msgs = $this->model->validation($training_type, $file_name);
		
		if ($msgs != null) {
			$this->set_err_info($msgs);
			$this->set('training_type_map', $this->model->get_training_type_map(false));
			$this->view('training/training_upload');
			return;
		}
		
		$this->model->file_upload($file_name);
		
		//メッセージを出して画面はそのままにする
		$this->show_dialog($this->lang->line('file_uploaded'));
		$this->redirect_js(base_url(). 'training/TrainingUpload');
	}
}
?>