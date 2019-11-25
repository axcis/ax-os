<?php

/**
 * Controllerのオリジナル継承クラス
 *
 * 基本的にControllerで行う処理は以下
 * ・Viewの呼び出し
 * ・(必要に応じて)Modelのロード、呼び出し
 * ・値の受取、渡し
 * @author takanori_gozu
 *
 */
class MY_Controller extends CI_Controller {
	
	private $_twig;
	private $_data; //Formデータ
	
	const LEVEL_ADMINISTRATOR = '1';
	const LEVEL_LEADER = '2';
	const LEVEL_SUB_LEADER = '3';
	const LEVEL_MEMBER = '4';
	
	/**
	 * コンストラクタ
	 */
	public function __construct() {
		
		parent::__construct();
		
		//Twigライブラリをロードする
		$loader = new Twig_Loader_Filesystem('application/views');
		$this->_twig = new Twig_Environment($loader, array('cache' => APPPATH.'/cache/twig', 'debug' => true));
		
		//Twigで使用する関数を追加
		$this->add_function();
		
		//初期化
		$this->init();
		
		//ログイン認証チェック
		if (!$this->is_logined()) redirect('Login');
		
		//postされたデータをFormにセットしておく
		$form = $this->input->post();
		$this->set_attribute($form);
		
		//サイドメニューセット
		$list = $this->get_contents_list();
		$this->set('contents_list', $list);
		
		$this->_twig->addGlobal("session", $this->get_session_attribute());
	}
	
	/**
	 * デフォルトイニシャライズ
	 */
	private function init() {
		
		//base_url
		$this->set("base_url", base_url());
		//システム名
		$this->set('system_name', $this->config->item('system_name'));
		//会社名
		$this->set('company_name', $this->config->item('company_name'));
		
		$class = $this->router->fetch_class();
		if (strpos($class, 'WeeklyReport') === false) $this->del_session('report_ids'); //週報以外では不要
	}

	/**
	 * ログイン認証チェック
	 */
	public function is_logined() {
		$class = $this->router->fetch_class();
		if ($class != 'Login' && $class != 'Logout') {
			if (!$this->get_session('is_login')) {
				return false;
			}
		}
		return true;
	}

	//setter
	public function set($key, $value) {
		$this->_data[$key] = $value;
	}
	
	//一括設定
	public function set_attribute($values) {
		foreach ($values as $key => $value) {
			$this->set($key, $value);
		}
	}
	
	//getter
	public function get($key, $value = '') {
		if (array_key_exists($key, $this->_data)) {
			return $this->_data[$key];
		}
		return $value;
	}
	
	//一括取得
	public function get_attribute() {
		return $this->_data;
	}
	
	//set_session
	public function set_session($key, $value) {
		$this->session->set_userdata($key, $value);
	}
	
	//session一括設定
	public function set_session_attribute($sessions) {
		foreach ($sessions as $key => $value) {
			$this->set_session($key, $value);
		}
	}
	
	//get_session
	public function get_session($key) {
		return $this->session->userdata($key);
	}
	
	//session一括取得
	public function get_session_attribute() {
		return $this->session->all_userdata();
	}
	
	//session削除
	public function del_session($key) {
		$this->session->unset_userdata($key);
	}

	/**
	 * エラー情報をセット
	 */
	public function set_err_info($msgs) {
		$this->set("err", "1");
		$this->set("err_msg", $msgs);
	}

	/**
	 * View
	 */
	public function view($template) {
		$view = $this->_twig->loadTemplate($template. '.twig');
		$this->output->set_output($view->render($this->_data));
	}

	/**
	 * ダイアログ表示
	 */
	public function show_dialog($msg) {

		$src = '<script type="text/javascript">';
		$src .= 'alert("'. $msg. '");';
		$src .= '</script>';

		echo $src;
	}

	/**
	 * Javascriptでのリダイレクト
	 */
	public function redirect_js($url) {

		$src = '<script type="text/javascript">';
		$src .= 'location.href = "'. $url. '";';
		$src .= '</script>';

		echo $src;
	}
	
	/**
	 * ポップアップ画面のクローズ
	 */
	public function popup_close() {
		
		$src = '<script type="text/javascript">';
		$src .= 'window.close();';
		$src .= '</script>';
		
		echo $src;
	}
	
	/**
	 * ヘルパーに定義されているTwig関数を追加
	 */
	private function add_function() {
		
		$twig_helpers = array('select', 'checkbox', 'radio');
		
		foreach ($twig_helpers as $helper_name) {
			$name = 'form_'. $helper_name;
			$func = 'twig_func_'. $helper_name;
			$function = new Twig_SimpleFunction($name, $func);
			$this->_twig->addFunction($function);
		}
	}
	
	/**
	 * サイドメニューのコンテンツ一覧
	 */
	private function get_contents_list() {
		
		$list = array();
		
		//コンテンツ情報の記載
		$list[] = array('btn_name' => 'トップ', 'url' => base_url(). 'TopPage', 'key' => 'top');
		$list[] = array('btn_name' => '週報', 'url' => base_url(). 'weekly_report/WeeklyReportList', 'key' => 'weekly_report');
		$list[] = array('btn_name' => '勤怠', 'url' => base_url(). 'time_record/TimeRecordList', 'key' => 'time_record');
		$list[] = array('btn_name' => '経費', 'url' => base_url(). 'cost_manage/CostManageList', 'key' => 'cost_manage');
		$list[] = array('btn_name' => '社内研修', 'url' => base_url(). 'training/TrainingList', 'key' => 'training');
		if ($this->get_session('user_level') > self::LEVEL_ADMINISTRATOR) {
			$list[] = array('btn_name' => '学習', 'url' => 'https://ati.axcis-sys.com/main', 'target' => 'blank');
		}
		$list[] = array('btn_name' => 'FAQ', 'url' => base_url(). 'faq/FaqList', 'key' => 'faq');
		if ($this->get_session('user_level') <= self::LEVEL_LEADER) {
			$list[] = array('btn_name' => 'お知らせ', 'url' => base_url(). 'notice/NoticeList', 'key' => 'notice');
		}
		if ($this->get_session('user_level') <= self::LEVEL_SUB_LEADER) {
			$list[] = array('btn_name' => '会議室予約', 'url' => base_url(). 'conference/ConferenceAppointList', 'key' => 'conference');
		}
// 		if ($this->get_session('user_level') == self::LEVEL_ADMINISTRATOR) {
// 			$list[] = array('btn_name' => '有休申請', 'url' => base_url(). 'vacation/VacationList', 'key' => 'vacation');
// 		} else {
// 			$list[] = array('btn_name' => '有休申請', 'url' => base_url(). 'vacation/VacationRequestList', 'key' => 'vacation');
// 		}
		
		$list[] = array('btn_name' => '社内文書', 'url' => base_url(). 'document/DocumentList', 'key' => 'document');
		if ($this->get_session('user_name') != $this->lang->line('administrator')) {
			$list[] = array('btn_name' => 'パスワード', 'url' => base_url(). 'password/PasswordModify', 'key' => 'password');
		}
// 		$list[] = array('btn_name' => 'ヘルプ', 'url' => base_url(). 'help/Help', 'key' => 'help');
		
		return $list;
	}
}
?>