<?php

use PhpOffice\PhpSpreadsheet\Style\Border;

/**
 * CostManageBulkOutputModel
 * @author takanori_gozu
 *
 */
class CostManageBulkOutputModel extends CostManageBaseModel {
	
	/**
	 * 一括DL
	 */
	public function bulk_output($input) {
		
		$employee_id = $input['employee_id'];
		$month = $input['month'];
		
		$employee_info = $this->get_employee_info($employee_id);
		
		//ディレクトリ作成
		$dir = $this->make_dir($month, $employee_info[EmployeeDao::COL_LOGIN_ID]);
		
		//データ取得
		$traffic_list = $this->get_list($employee_id, $month, '1');
		if (count($traffic_list) > 0) {
			$this->make_traffic_excel($month, $employee_info[EmployeeDao::COL_NAME], $traffic_list, $dir);
		}
		
		$expenses_list = $this->get_list($employee_id, $month, '2');
		if (count($expenses_list) > 0) {
			$this->make_expenses_excel($month, $employee_info[EmployeeDao::COL_NAME], $expenses_list, $dir);
		}
		
		//ZipでDLするため、Zipライブラリをロード
		$this->load->library('zip');
		
		//ファイルを読み込む
		if(is_dir($dir) && $handle = opendir($dir)) {
			while(($file = readdir($handle)) !== false) {
				if(filetype($dir. $file ) == "file") {
					$data = read_file($dir. $file);
					$name = $file;
					if (strcmp($name, '交通費精算書.xlsx') === 0 ||
						strcmp($name, '経費精算書.xlsx') === 0) {
							$name = mb_convert_encoding($file, 'SJIS', 'UTF-8'); //文字化け対策
						}
							$this->zip->add_data($name, $data);
				}
			}
		}
		
		//出力
		//ファイル名(YYYYMM_名前.zip)
		$zip_file_name = $month . "_". str_replace(array(" ", "　"), "", $employee_info[EmployeeDao::COL_NAME]). ".zip";
		
		$this->zip->download($zip_file_name);
	}
	
	/**
	 * 社員情報を取得
	 */
	private function get_employee_info($employee_id) {
		
		$this->set_table(EmployeeDao::TABLE_NAME, self::DB_MASTER);
		
		$this->add_where(EmployeeDao::COL_ID, $employee_id);
		
		return $this->do_select_info();
	}
	
	/**
	 * 元ディレクトリの作成
	 */
	private function make_dir($month, $user_id) {
		
		$this->load->helper('file');
		$ym = date('Ym', strtotime($month. '-01'));
		
		$dir_path= $this->lang->line('upload_dir'). $user_id. "/". $ym. "/";
		
		if (!file_exists($dir_path)) {
			//パーミッション755で作成
			mkdir($dir_path, 0755, true);
		}
		
		return $dir_path;
	}
	
	/**
	 * データ取得
	 */
	private function get_list($employee_id, $month, $input_type) {
		
		$this->set_table(ExpensesDao::TABLE_NAME, self::DB_TRAN);
		
		$this->add_select(ExpensesDao::COL_ID);
		$this->add_select(ExpensesDao::COL_EXPENSES_YMD);
		$this->add_select(ExpensesDao::COL_INPUT_TYPE);
		$this->add_select(ExpensesDao::COL_PAY_TYPE);
		$this->add_select(ExpensesDao::COL_EXPENSES_TYPE);
		$this->add_select(ExpensesDao::COL_ROUND_TRIP_TYPE);
		$this->add_select(ExpensesDao::COL_TRANSPORT);
		$this->add_select(ExpensesDao::COL_FROM_PLACE);
		$this->add_select(ExpensesDao::COL_TO_PLACE);
		$this->add_select(ExpensesDao::COL_EXPENSES_DETAIL);
		$this->add_select(ExpensesDao::COL_COST);
		
		$this->add_where(ExpensesDao::COL_EMPLOYEE_ID, $employee_id);
		$this->add_where(ExpensesDao::COL_INPUT_TYPE, $input_type);
		
		$this->add_where_like(ExpensesDao::COL_REGIST_YM, $month, self::WILD_CARD_AFTER);
		$this->add_order(ExpensesDao::COL_EXPENSES_YMD);
		
		return $this->do_select();
	}
	
	/**
	 * 交通費精算書
	 */
	private function make_traffic_excel($month, $name, $list, $dir) {
		
		$this->load->model('common/PHPExcelModel', 'excel');
		
		//初期化
		$this->excel_init('交通費精算書');
		
		//タイトル
		$this->excel->set_cell_value_A1('A1', '交通費精算書');
		
		//項目
		$this->set_traffic_title();
		
		//Listの出力
		$list_row_idx = $this->set_traffic_list_value($list);
		
		//体裁を整えるため、30行目までは罫線を引く
		if ($list_row_idx < 30) $list_row_idx = 30;
		
		//合計
		$total_idx = $list_row_idx + 1;
		$this->excel->set_cell_value('G'. $total_idx, '=SUM(G6:G'. $list_row_idx. ')');
		
		//記入及び注意事項欄‥
		$this->set_traffic_other_area($month, $name, $total_idx);
		
		//体裁
		$this->set_traffic_page_format($list_row_idx, $total_idx);
		
		//横を1ページで収めるようにする
		$this->excel->set_page_reap(1, 0);
		
		$file_name = '交通費精算書.xlsx';
		
		//出力
		$this->excel->output($dir. mb_convert_encoding($file_name, 'SJIS', 'UTF-8'));
		return;
	}
	
	/**
	 * 経費精算書
	 */
	private function make_expenses_excel($month, $name, $list, $dir) {
		
		$this->load->model('common/PHPExcelModel', 'excel');
		
		//初期化
		$this->excel_init('経費精算書');
		
		//タイトル
		$this->excel->set_cell_value_A1('A1', '経費精算書');
		
		//項目
		$this->set_expenses_title();
		
		//Listの出力
		$list_row_idx = $this->set_expenses_list_value($list);
		
		//体裁を整えるため、30行目までは罫線を引く
		if ($list_row_idx < 30) $list_row_idx = 30;
		
		//合計
		$total_idx= $list_row_idx + 1;
		$this->excel->set_cell_value('E'. $total_idx, '=SUM(E6:E'. $list_row_idx. ')');
		
		//記入及び注意事項欄‥
		$this->set_expenses_other_area($month, $name, $list_row_idx, $total_idx);
		
		//体裁
		$this->set_expenses_page_format($list_row_idx, $total_idx);
		
		//横を1ページで収めるようにする
		$this->excel->set_page_reap(1, 0);
		
		$file_name = '経費精算書.xlsx';
		
		//出力
		$this->excel->output($dir. mb_convert_encoding($file_name, 'SJIS', 'UTF-8'));
		return;
	}
	
	/**
	 * 初期化
	 */
	private function excel_init($sheet_name) {
		
		$this->excel->init();
		$this->excel->set_sheet();
		//ページ設定(A4横向き)
		$this->excel->set_pagesize_A4();
		$this->excel->set_page_orientation();
		$this->excel->set_default_font('Meiryo UI');
		$this->excel->set_margin(0.5, 0.5, 0.8, 0, 0.5, 0.5);
		$this->excel->set_title($sheet_name);
	}
	
	/**
	 * 交通費精算書の項目名
	 */
	private function set_traffic_title() {
		
		$col = 1;
		$this->excel->set_cell_value_R1C1($col++, 6, '日付');
		$this->excel->set_cell_value_R1C1($col++, 6, '往復路');
		$this->excel->set_cell_value_R1C1($col++, 6, '手段');
		$this->excel->set_cell_value_R1C1($col++, 6, '出発');
		$this->excel->set_cell_value_R1C1($col++, 6, '到着');
		$this->excel->set_cell_value_R1C1($col++, 6, '目的');
		$this->excel->set_cell_value_R1C1($col++, 6, '金額');
		$this->excel->set_cell_value_R1C1($col++, 6, '領収書');
	}
	
	/**
	 * 経費精算書の項目名
	 */
	private function set_expenses_title() {
		
		$col = 1;
		$this->excel->set_cell_value_R1C1($col++, 6, '日付');
		$this->excel->set_cell_value_R1C1($col++, 6, '支払方法');
		$this->excel->set_cell_value_R1C1($col++, 6, '内訳');
		$this->excel->set_cell_value_R1C1($col++, 6, '内容');
		$this->excel->set_cell_value_R1C1($col++, 6, '金額');
		$this->excel->set_cell_value_R1C1($col++, 6, '領収書');
	}
	
	/**
	 * 交通費精算書の一覧出力
	 */
	private function set_traffic_list_value($list) {
		
		$round_trip_type_map = $this->get_round_trip_type_map();
		
		//出力する項目
		$out_col = array('expenses_ymd', 'round_trip_type', 'transport', 'from_place', 'to_place', 'expenses_detail', 'cost');
		
		$row_idx = 7;
		
		foreach ($list as $row) {
			
			$col = 1;
			
			foreach ($row as $key => $value) {
				if ($key == 'expenses_ymd') {
					//日付
					$value = date('Y年n月j日', strtotime($value));
				}
				if ($key == 'round_trip_type' && $value != null) {
					//往復路区分
					$value = $round_trip_type_map[$value];
				}
				if (in_array($key, $out_col)) {
					$this->excel->set_cell_value_R1C1($col++, $row_idx, $value);
				}
			}
			$row_idx++;
		}
		
		return $row_idx;
	}
	
	/**
	 * 経費精算書の一覧出力
	 */
	private function set_expenses_list_value($list) {
		
		$pay_type_map = $this->get_pay_type_map();
		$expenses_type_map = $this->get_expenses_type_map();
		
		//出力する項目
		$out_col = array('expenses_ymd', 'pay_type', 'expenses_type', 'expenses_detail', 'cost');
		
		$row_idx = 7;
		
		foreach ($list as $row) {
			
			$col = 1;
			
			foreach ($row as $key => $value) {
				if ($key == 'expenses_ymd') {
					//日付
					$value = date('Y年n月j日', strtotime($value));
				}
				if ($key == 'pay_type') {
					//支払方法
					$value = $pay_type_map[$value];
				}
				if ($key == 'expenses_type') {
					//内訳
					$value = $expenses_type_map[$value];
				}
				if (in_array($key, $out_col)) {
					$this->excel->set_cell_value_R1C1($col++, $row_idx, $value);
				}
			}
			$row_idx++;
		}
		
		return $row_idx;
	}
	
	/**
	 * 交通費精算書の押印等の項目
	 */
	private function set_traffic_other_area($month, $name, $total_idx) {
		
		//氏名
		$this->excel->set_cell_value_A1('A2', '氏名');
		$this->excel->set_cell_value_A1('B2', $name);
		
		//精算期間
		$this->excel->set_cell_value_A1('A3', '精算月');
		$this->excel->set_cell_value_A1('B3', date('Y年n月', strtotime($month. '-01')));
		
		//提出日
		$this->excel->set_cell_value_A1('A4', '提出日');
		$this->excel->set_cell_value_A1('B4', date('Y年n月t日', strtotime($month. '-01')));
		
		//交通費合計
		$this->excel->set_cell_value_A1('G2', '交通費合計');
		$this->excel->set_cell_value('G3', '=G'. $total_idx);
	}
	
	/**
	 * 経費精算書の押印欄等の項目
	 */
	private function set_expenses_other_area($month, $name, $list_idx, $total_idx) {
		
		//氏名
		$this->excel->set_cell_value_A1('A2', '氏名');
		$this->excel->set_cell_value_A1('B2', $name);
		
		//精算期間
		$this->excel->set_cell_value_A1('A3', '精算月');
		$this->excel->set_cell_value_A1('B3', date('Y年n月', strtotime($month. '-01')));
		
		//提出日
		$this->excel->set_cell_value_A1('A4', '提出日');
		$this->excel->set_cell_value_A1('B4', date('Y年n月t日', strtotime($month. '-01')));
		
		//経費合計
		$this->excel->set_cell_value_A1('E2', '経費合計');
		$this->excel->set_cell_value('E3', '=E'. $total_idx);
	}
	
	/**
	 * 交通費精算書のフォーマット設定
	 */
	private function set_traffic_page_format($list_idx, $total_idx) {
		
		//フォントサイズ
		$this->excel->set_font_size('G3', 18);
		$this->excel->set_font_size('A7:H'. $list_idx, 9);
		
		//Listの罫線
		$this->excel->set_border('A6:H'. $total_idx, 'all', Border::BORDER_HAIR);
		$this->excel->set_horizon_align('A6:H6');
		$this->excel->set_border('A6:H'. $total_idx, 'outer');
		$this->excel->set_border('A'. $total_idx. ':H'. $total_idx, 'outer');
		
		//タイトル
		$this->excel->set_font_size('A1', 18);
		$this->excel->cell_merge('A1:H1');
		
		//列幅
		$this->excel->set_column_width('A', 12);
		$this->excel->set_column_width('B', 12);
		$this->excel->set_column_width('C', 12);
		$this->excel->set_column_width('D', 15);
		$this->excel->set_column_width('E', 15);
		$this->excel->set_column_width('F', 35);
		$this->excel->set_column_width('G', 15);
		$this->excel->set_column_width('H', 7);
		
		//結合
		$this->excel->cell_merge('B2:C2');
		$this->excel->cell_merge('B3:C3');
		$this->excel->cell_merge('B4:C4');
		$this->excel->cell_merge('G2:H2');
		$this->excel->cell_merge('G3:H4');
		
		//罫線
		$this->excel->set_border('A2:C4');
		$this->excel->set_border('G2:H4');
		
		//横位置
		$this->excel->set_horizon_align('A1');
		$this->excel->set_horizon_align('G2');
		$this->excel->set_horizon_align('G3');
		
		//縦位置
		$this->excel->set_vertical_align('A7:H'. $list_idx);
		$this->excel->set_vertical_align('G3');
		$this->excel->set_wrap_text('A6:G'. $list_idx);
		
		//背景色
		$this->excel->set_color('A2', 'dbdbdb');
		$this->excel->set_color('A3', 'dbdbdb');
		$this->excel->set_color('A4', 'dbdbdb');
		$this->excel->set_color('G2', 'dbdbdb');
		$this->excel->set_color('A6:H6', 'dbdbdb');
		
		//フォーマット
		$this->excel->set_number_format('G3', '"\"#,##0');
	}
	
	/**
	 * 経費精算書のフォーマット設定
	 */
	private function set_expenses_page_format($list_idx, $total_idx) {
		
		//フォントサイズ
		$this->excel->set_font_size('A7:F'. $list_idx, 9);
		$this->excel->set_font_size('E3', 18);
		
		//Listの罫線
		$this->excel->set_border('A6:F'. $total_idx, 'all', Border::BORDER_HAIR);
		$this->excel->set_horizon_align('A6:F6');
		$this->excel->set_border('A6:F'. $total_idx, 'outer');
		$this->excel->set_border('A'. $total_idx. ':F'. $total_idx, 'outer');
		
		//タイトル
		$this->excel->set_font_size('A1', 18);
		$this->excel->cell_merge('A1:F1');
		
		$this->excel->cell_merge('B2:C2');
		$this->excel->cell_merge('B3:C3');
		$this->excel->cell_merge('B4:C4');
		$this->excel->cell_merge('E2:F2');
		$this->excel->cell_merge('E3:F4');
		
		//列幅
		$this->excel->set_column_width('A', 12);
		$this->excel->set_column_width('B', 15);
		$this->excel->set_column_width('C', 15);
		$this->excel->set_column_width('D', 35);
		$this->excel->set_column_width('E', 15);
		$this->excel->set_column_width('F', 7);
		
		//罫線
		$this->excel->set_border('A2:C4');
		$this->excel->set_border('E2:F4');
		
		//横位置
		$this->excel->set_horizon_align('A1');
		$this->excel->set_horizon_align('E2:F4');
		
		//縦位置
		$this->excel->set_vertical_align('A7:F'. $list_idx);
		$this->excel->set_vertical_align('E3');
		$this->excel->set_wrap_text('A6:F'. $list_idx);
		
		//背景色
		$this->excel->set_color('A2:A4', 'dbdbdb');
		$this->excel->set_color('E2', 'dbdbdb');
		$this->excel->set_color('A6:F6', 'dbdbdb');
		
		//フォーマット
		$this->excel->set_number_format('E3', '"\"#,##0');
	}
}
?>