<?php

/**
 * TrainingAnswerCheckController
 * @author takanori_gozu
 *
 */
class TrainingAnswerCheck extends MY_Controller {
	
	/**
	 * コンストラクタ
	 */
	public function __construct() {
		
		parent::__construct();
		
		if ($this->get_session('user_level') > self::LEVEL_LEADER) {
			//サブリーダー・メンバーはチェック不可
			$this->session->sess_destroy();
			redirect('Login');
		}
	}
	
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