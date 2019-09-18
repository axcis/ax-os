<?php

/**
 * Langのオリジナル継承クラス
 * @author takanori_gozu
 *
 */
class MY_Lang extends CI_Lang {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * lineメソッドをオーバーライド
	 */
	public function line($line, $arr = null) {

		$value = parent::line($line, true);

		if ($arr == null) {
			return $value;
		}

		//文字列を置換する
		for($i = 0; $i < count($arr); $i++) {
			$value = str_replace("[%$i]", $arr[$i], $value);
		}

		return $value;
	}
}
?>