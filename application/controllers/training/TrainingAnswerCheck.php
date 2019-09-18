<?php

/**
 * TrainingAnswerCheckController
 * @author takanori_gozu
 *
 */
class TrainingAnswerCheck extends MY_Controller {
	
	/**
	 * Index
	 */
	public function index() {
		
		$this->load->model('training/TrainingAnswerCheckModel', 'model');
		$this->load->library('dao/TrainingCategoryDao');
		
		$this->set('popup', '1');
		
		$this->set('list_col', $this->model->get_list_col());
		$this->set('training_type_map', $this->model->get_training_type_map());
		
		$this->view('training/training_answer_check');
	}
	
	/**
	 * 一覧の取得(ajax)
	 */
	public function select() {
		
		$this->load->model('training/TrainingAnswerCheckModel', 'model');
		$this->load->library('dao/EmployeeDao');
		$this->load->library('dao/TrainingQuestionAnswerDao');
		
		$type = $this->get('training_type');
		
		$list = $this->model->get_list($type);
		
		echo json_encode(array($list));
	}
}
?>