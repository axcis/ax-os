<?php

/**
 * PdfModel
 * @author takanori_gozu
 *
 */
class PdfModel extends MY_Model {
	
	private $_pdf;
	
	/**
	 * コンストラクタ
	 */
	public function __construct() {
		parent::__construct();
	}
	
	/**
	 * 初期化
	 */
	public function reset($orientation = 'L', $unit = 'mm', $size = 'A4', $unicode = true, $char = 'UTF-8') {
		$this->_pdf = new TCPDF($orientation, $unit, $size, $unicode, $char);
		$this->_pdf->AddPage();
	}
	
	/**
	 * フォント名
	 */
	public function set_font($font_name = 'kozgopromedium', $style = '', $size = 10) {
		$this->_pdf->SetFont($font_name, $style, $size);
	}
	
	/**
	 * ヘッダー
	 */
	public function set_header($flg = false) {
		$this->_pdf->setPrintHeader($flg);
	}
	
	/**
	 * フッター
	 */
	public function set_footer($flg = false) {
		$this->_pdf->setPrintFooter($flg);
	}
	
	/**
	 * 余白
	 */
	public function set_margin($left = 0, $top = 0, $right = 0) {
		$this->_pdf->SetMargins($left, $top, $right);
	}
	
	/**
	 * タイトル
	 */
	public function set_title($title) {
		$this->_pdf->SetTitle($title);
	}
	
	/**
	 * イメージ配置
	 */
	public function set_image($image, $x = '', $y = '', $w = 0, $h = 0) {
		$this->_pdf->Image($image, $x, $y, $w, $h);
	}
	
	/**
	 * 背景色
	 */
	public function set_fill_color($r, $g, $b) {
		$this->_pdf->SetFillColor($r, $g, $b);
	}
	
	/**
	 * 文字色
	 */
	public function set_font_color($r, $g, $b) {
		$this->_pdf->SetTextColor($r, $g, $b);
	}
	
	/**
	 * 書き込み
	 */
	public function set_value($w = 0, $h = 0, $text = '', $border = 0, $next = 0, $align = '') {
		$this->_pdf->Cell($w, $h, $text, $border, $next, $align);
	}
	
	/**
	 * HTMLを書き込む
	 */
	public function write_html($html) {
		$this->_pdf->writeHTML($html);
	}
	
	/**
	 * 出力
	 */
	public function output($file_name, $method = 'I') {
		$this->_pdf->Output($file_name. '.pdf', $method);
	}
}
?>