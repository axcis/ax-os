<?php

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

/**
 * PHPExcelModel
 * @author takanori_gozu
 *
 */
class PHPExcelModel extends MY_Model {

	private $_excel;
	private $_sheet;
	private $_write;
	private $_type;

	/**
	 * コンストラクタ
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * 新規Excelファイルを作成
	 */
	public function init($ext = '.xlsx') {
		$this->set_type($ext);
		$this->_excel = new Spreadsheet();
	}

	/**
	 * フォーマット用Excelをロード
	 */
	public function load($format_file, $ext = '.xlsx') {
		$this->set_type($ext);
		$path = 'excel/'. $format_file. $ext;
		$this->_excel = IOFactory::createReader($this->_type)->load($path);
	}

	/**
	 * Excel形式の判定
	 */
	public function set_type($ext) {
		if ($ext == '.xlsx') {
			$this->_type = 'Xlsx';
		} else {
			$this->_type = 'Xls';
		}
	}
	
	/**
	 * シートの追加
	 */
	public function add_sheet() {
		$this->_excel->createSheet();
	}

	/**
	 * アクティブシートの設定
	 */
	public function set_sheet($sheet_idx = 0) {
		$this->_excel->setActiveSheetIndex($sheet_idx);
		$this->_sheet = $this->_excel->getActiveSheet();
	}
	
	/**
	 * シートのコピー
	 * $posはシートの追加場所(null→末尾、0→先頭)
	 */
	public function copy($title, $pos = null) {
		$first = $this->_excel->getSheet(0);
		$new = $first->copy();
		$new->setTitle($title);
		$this->_excel->addSheet($new, $pos);
	}

	/**
	 * シート名
	 */
	public function set_title($title) {
		$this->_sheet->setTitle($title);
	}

	/**
	 * サイズの設定(A4)
	 */
	public function set_pagesize_A4() {
		$this->_sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
	}
	
	/**
	 * ページを横向きに設定する
	 */
	public function set_page_orientation() {
		$this->_sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
	}

	/**
	 * サイズの設定(A3)
	 */
	public function set_pagesize_A3() {
		$this->_sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A3);
	}
	
	/**
	 * 指定したページ数に収める
	 */
	public function set_page_reap($width_page_count = 1, $height_page_count = 1) {
		$this->_sheet->getPageSetup()->setFitToWidth($width_page_count)->setFitToHeight($height_page_count);
	}

	/**
	 * 罫線印刷の設定
	 */
	public function set_show_grid($flg = true) {
		$this->_sheet->setShowGridlines($flg);
	}

	/**
	 * ページ番号印刷
	 */
	public function set_footer_pager() {
		$this->_sheet->getHeaderFooter()->setOddFooter('Page &P of &N');
	}

	/**
	 * 余白の設定
	 */
	public function set_margin($top = 0, $bot = 0, $left = 0, $right = 0, $head = 0, $foot = 0) {
		$this->_sheet->getPageMargins()->setTop($top);
		$this->_sheet->getPageMargins()->setBottom($bot);
		$this->_sheet->getPageMargins()->setLeft($left);
		$this->_sheet->getPageMargins()->setRight($right);
		$this->_sheet->getPageMargins()->setHeader($head);
		$this->_sheet->getPageMargins()->setFooter($foot);
	}

	/**
	 * 列幅の設定
	 */
	public function set_column_width($column_no, $value) {
		$this->_sheet->getColumnDimension($column_no)->setWidth($value);
	}
	
	/**
	 * 行の高さ設定
	 */
	public function set_row_height($row_no, $value) {
		$this->_sheet->getRowDimension($row_no)->setRowHeight($value);
	}
	
	/**
	 * デフォルトのフォント設定
	 */
	public function set_default_font($font, $size = 11) {
		$this->_sheet->getParent()->getDefaultStyle()->getFont()->setName($font)->setSize($size);
	}
	
	/**
	 * フォントサイズの変更
	 */
	public function set_font_size($range, $size = 10) {
		$this->_sheet->getStyle($range)->getFont()->setSize($size);
	}
	
	/**
	 * 数値フォーマットの設定
	 */
	public function set_number_format($range, $format = '#,##0') {
		$this->_sheet->getStyle($range)->getNumberFormat()->setFormatCode($format);
	}

	/**
	 * セルの結合
	 */
	public function cell_merge($range) {
		$this->_sheet->mergeCells($range);
	}

	/**
	 * セルの着色
	 */
	public function set_color($range, $color) {
		$this->_sheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($color);
	}

	/**
	 * 罫線
	 */
	public function set_border($range, $place = 'all', $border_style = Border::BORDER_THIN) {
		switch ($place) {
			case 'top':
				$this->_sheet->getStyle($range)->getBorders()->getTop()->setBorderStyle($border_style);
				break;
			case 'bottom':
				$this->_sheet->getStyle($range)->getBorders()->getBottom()->setBorderStyle($border_style);
				break;
			case 'left':
				$this->_sheet->getStyle($range)->getBorders()->getLeft()->setBorderStyle($border_style);
				break;
			case 'right':
				$this->_sheet->getStyle($range)->getBorders()->getRight()->setBorderStyle($border_style);
				break;
			case 'outer':
				$this->_sheet->getStyle($range)->getBorders()->getOutline()->setBorderStyle($border_style);
				break;
			case 'all':
				$this->_sheet->getStyle($range)->getBorders()->getAllBorders()->setBorderStyle($border_style);
				break;
		}
	}
	
	/**
	 * 縦位置中央揃え
	 */
	public function set_vertical_align($range, $position = Alignment::VERTICAL_CENTER) {
		$this->_sheet->getStyle($range)->getAlignment()->setVertical($position);
	}

	/**
	 * 横位置中央揃え
	 */
	public function set_horizon_align($range, $position = Alignment::HORIZONTAL_CENTER) {
		$this->_sheet->getStyle($range)->getAlignment()->setHorizontal($position);
	}
	
	/**
	 * セル内の改行設定
	 */
	public function set_wrap_text($range) {
		$this->_sheet->getStyle($range)->getAlignment()->setWrapText(true);
	}
	
	/**
	 * 列インデックスから列番号を取得する
	 * 例) 1→A、2→B…
	 */
	public function get_col_name($idx) {
		return Coordinate::stringFromColumnIndex($idx);
	}
	
	/**
	 * 列番号から列インデックスを取得する
	 * 例) A→1、B→2…
	 */
	public function get_col_index($col_name) {
		return Coordinate::columnIndexFromString($col_name);
	}
	
	/**
	 * セルに数式を代入する
	 */
	public function set_cell_value($range, $value) {
		$this->_sheet->setCellValue($range, $value);
	}
	
	/**
	 * セルの値をクリアする
	 */
	public function cell_clear($range) {
		$this->_sheet->setCellValueExplicit($range, "", DataType::TYPE_STRING);
	}

	/**
	 * セルに値を代入(A1方式)
	 * 数値は文字列に強制設定
	 */
	public function set_cell_value_A1($range, $value) {
		$this->_sheet->setCellValueExplicit($range, $value, DataType::TYPE_STRING);
	}
	
	/**
	 * セルに値を代入(R1C1方式)
	 */
	public function set_cell_value_R1C1($col_idx, $row_idx, $value) {
		$this->_sheet->setCellValueByColumnAndRow($col_idx, $row_idx, $value);
	}
	
	/**
	 * dataの中身をstartの位置から一括出力
	 */
	public function set_cell_arr($data, $start) {
		$this->_sheet->fromArray($data, null, $start);
	}
	
	/**
	 * Excelの出力
	 */
	public function output($file_path) {
		$this->_write = IOFactory::createWriter($this->_excel, $this->_type);
		$this->_write->save($file_path);
		$this->fin();
	}

	/**
	 * 保存
	 */
	public function save($file_name) {
		$this->_write = IOFactory::createWriter($this->_excel, $this->_type);
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Content-Type: application/vnd.ms-excel");
		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download");
		header("Content-Transfer-Encoding: binary ");
		header("Content-Disposition: attachment;filename=$file_name");
		header("Cache-Control: max-age=0");
		$this->_write->save("php://output");

		$this->fin();
	}

	/**
	 * メモリの解放
	 */
	public function fin() {
		$this->_excel->disconnectWorksheets();
		unset($this->_write);
		unset($this->_sheet);
		unset($this->_excel);
	}
}
?>