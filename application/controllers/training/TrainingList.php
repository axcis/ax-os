<?php

/**
 * TrainingListController
 * @author takanori_gozu
 *
 */
class TrainingList extends MY_Controller {
	
	/**
	 * Index
	 */
	public function index() {
		
		$this->load->model('training/TrainingListModel', 'model');
		$this->load->library('dao/TrainingCategoryDao');
		
		$this->set('class_key', 'training');
		$this->set('class_path', 'training/Training');
		
		$this->set('no_search', '1');
		
		$this->set('list', $this->model->get_content_list());
		$this->set('link', $this->model->get_link());
		
		$this->view('training/training_list');
	}
	
	/**
	 * 研修選択
	 */
	public function select($type) {
		
		$this->load->model('training/TrainingListModel', 'model');
		$this->load->library('dao/TrainingCategoryDao');
		$this->load->library('dao/TrainingQuestionDao');
		$this->load->library('dao/TrainingQuestionAnswerDao');
		
		$this->set('class_key', 'training');
		$this->set('class_path', 'training/Training');
		
		$this->set('type', $type);
		$this->set('no_search', '1');
		
		$point = $this->model->get_point($type);
		$training_name = $this->model->get_question_name($type);
		
		$this->set('question_list', $this->model->get_question_list($type));
		$this->set('training_name', $training_name);
		$this->set('point', $point);
		
		$this->view('training/training_question_list');
	}
	
	/**
	 * テキストのDL
	 */
	public function download($type) {
		
		$this->load->model('training/TrainingListModel', 'model');
		$this->load->library('dao/TrainingCategoryDao');
		
		$this->model->text_output($type);
	}
	
	/**
	 * 解答を送信する
	 */
	public function send() {
		
		$this->load->model('training/TrainingListModel', 'model');
		$this->load->library('dao/TrainingQuestionDao');
		$this->load->library('dao/TrainingQuestionAnswerDao');
		
		$input = $this->get_attribute();
		
		//解答情報を取得
		$answer_info = $this->model->get_answer_info($input);
		
		//正答チェックし、点数を計算
		$point = $this->model->get_answer_point($answer_info, $input['type']);
		
		//DB更新
		$this->model->db_insert_update($point, $input['type']);
		
		$this->set('class_key', 'training');
		$this->set('class_path', 'training/Training');
		
		$this->set('no_search', '1');
		$this->set('point', $point);
		
		$this->view('training/training_answer_complete');
	}
}
?>