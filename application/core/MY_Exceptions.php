<?php

/**
 * Exceptionsのオリジナル継承クラス
 * viewはすべてviews/errors/html/error_general.phpを呼び出している
 * @author takanori_gozu
 */
class MY_Exceptions extends CI_Exceptions {
	
	const message = 'ただいまメンテナンス中です。申し訳ありませんがしばらくお待ちください。';
	
	/**
	 * コンストラクタ
	 */
	public function __construct() {
		parent::__construct();
	}
	
	/**
	 * 404エラー発生時
	 */
	public function show_404($page = '', $log_error = TRUE){
		
		$heading = 'Not Found';
		
		if ($log_error)
		{
			log_message('error', $heading.': '.$page);
		}
		echo $this->show_error($heading, self::message, 'error_general');
		exit(4);
	}
	
	/**
	 * PHP FatalError発生時
	 */
	public function show_php_error($severity, $message, $filepath, $line) {
		
		$heading = 'Error';
		
		echo $this->show_error($heading, self::message, 'error_general');
		exit(4);
	}
	
	/**
	 * 例外発生時
	 */
	public function show_exception($exception) {
		
		$heading = 'Error';
		
		echo $this->show_error($heading, self::message, 'error_general');
		exit(4);
	}
}
?>