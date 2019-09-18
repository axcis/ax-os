<?php

/**
 * Logクラスのオリジナル
 * エラーログ出力時はメールをあわせて送信する
 * @author takanori_gozu
 *
 */
class MY_Log extends CI_Log {
	
	private $_info;
	
	/**
	 * コンストラクタ
	 */
	public function __construct() {
		parent::__construct();
	}
	
	/**
	 * ログ出力(オーバーライド)
	 */
	public function write_log($level, $msg) {
		
		$config = & get_config();
		
		$result = parent::write_log($level, $msg);
		
// 		if ($result == true && strtoupper($level) == 'ERROR' && $config['system_maintenance'] !== true) {
// 			//メール送信
// 			$message = $config['system_name']. "にてシステムエラーが発生しました。\n\n";
// 			$message .= $level.' - '.date($this->_date_fmt)."\n\n";
// 			$message .= $msg."\n";
			
// 			$to = $config['develop_email_to'];
// 			$subject = '社内システムエラー発生！';
// 			$headers = 'From: アクシスデベロッパー <'. $config['develop_email_from']. '>' . "\r\n";
// 			$headers .= 'Content-type: text/plain; charset=utf-8\r\n';
			
// 			mail($to, $subject, $message, $headers);
// 		}
		
		return $result;
	}
}
?>