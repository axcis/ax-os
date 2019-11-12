<?php

use PhpOffice\PhpSpreadsheet\Style\Alignment;

class WeeklyReportOutputModel extends MY_Model {
	
	/**
	 * 社員のマッピング
	 */
	public function get_employee_map() {
		
		$this->set_table(EmployeeDao::TABLE_NAME, self::DB_MASTER);
		
		$this->add_select(EmployeeDao::COL_ID);
		$this->add_select(EmployeeDao::COL_NAME);
		
		//Adminは除外
		$this->add_where(EmployeeDao::COL_USER_LEVEL, self::LEVEL_ADMINISTRATOR, self::COMP_NOT_EQUAL);
		$this->add_where(EmployeeDao::COL_RETIREMENT, '0'); //退職者は除く
		
		$list = $this->do_select();
		
		$map = array();
		
		$map[''] = '名前を選択';
		$map += $this->key_value_map($list);
		
		return $map;
	}
	
	/**
	 * 一覧
	 */
	public function get_list($input) {
		
		$this->set_table(WeeklyReportDao::TABLE_NAME, self::DB_TRAN);
		
		$this->add_where(WeeklyReportDao::COL_REGIST_USER_ID, $input['employee_id']);
		$this->add_where(WeeklyReportDao::COL_STANDARD_DATE, $input['from_date'], self::COMP_GREATER_EQUAL);
		$this->add_where(WeeklyReportDao::COL_STANDARD_DATE, $input['to_date'], self::COMP_LESS_EQUAL);
		
		$this->add_order(WeeklyReportDao::COL_STANDARD_DATE);
		
		return $this->do_select();
	}
	
	/**
	 * 社員名(出力用)
	 */
	public function get_employee_name($id) {
		
		$this->set_table(EmployeeDao::TABLE_NAME, self::DB_MASTER);
		
		$this->add_select(EmployeeDao::COL_NAME);
		$this->add_where(EmployeeDao::COL_ID, $id);
		
		$info = $this->do_select_info();
		
		return str_replace(array(" ", "　"), "", $info[EmployeeDao::COL_NAME]);
	}
	
	/**
	 * 出力
	 */
	public function excel_output($list, $employee_name) {
		
		$this->load->model('common/PHPExcelModel', 'excel');
		
		$this->excel->init();
		$i = 0;
		
		foreach ($list as $info) {
			
			$this->set_page($i, $info[WeeklyReportDao::COL_STANDARD_DATE]);
			
			$this->make_list($info);
			
			$this->format_arrange();
			
			$i++;
		}
		
		$file_name = '週報_'. $employee_name.'.xlsx';
		
		//ダウンロード
		$this->excel->save($file_name);
	}
	
	/**
	 * ページ設定
	 */
	private function set_page($i, $sheet_name) {
		
		if ($i > 0) $this->excel->add_sheet();
		$this->excel->set_sheet($i);
		$this->excel->set_pagesize_A4();
		$this->excel->set_default_font('Meiryo UI');
		$this->excel->set_title($sheet_name);
	}
	
	/**
	 * Excel書き込み
	 */
	private function make_list($info) {
		
		$start_date = date('Y年n月j日', strtotime($info[WeeklyReportDao::COL_STANDARD_DATE]));
		$end_date = date('Y年n月j日', strtotime('+6 day', strtotime($info[WeeklyReportDao::COL_STANDARD_DATE])));
		
		$this->excel->set_cell_value_A1('A1', '作業週報');
		
		$this->excel->set_cell_value_A1('A3', '期間');
		$this->excel->set_cell_value_A1('C3', $start_date. ' ～ '. $end_date);
		$this->excel->set_cell_value_A1('O3', '参画プロジェクト名');
		$this->excel->set_cell_value_A1('O4', $info[WeeklyReportDao::COL_PROJECT_NAME]);
		
		$this->excel->set_cell_value_A1('A5', '作業内容');
		$this->excel->set_cell_value_A1('A6', $info[WeeklyReportDao::COL_WORK_CONTENT]);
		
		$this->excel->set_cell_value_A1('A17', '作業に対する疑問／不明点／反省等');
		$this->excel->set_cell_value_A1('A18', $info[WeeklyReportDao::COL_REFLECT]);
		
		$this->excel->set_cell_value_A1('A29', 'その他');
		$this->excel->set_cell_value_A1('A30', $info[WeeklyReportDao::COL_OTHER]);
	}
	
	/**
	 * 体裁
	 */
	private function format_arrange() {
		
		//作業週報
		$this->excel->cell_merge('A1:U1');
		$this->excel->set_font_size('A1', 20);
		$this->excel->set_horizon_align('A1');
		
		//期間・参画プロジェクト名
		$this->excel->cell_merge('A3:B4');
		$this->excel->cell_merge('C3:N4');
		$this->excel->cell_merge('O3:U3');
		$this->excel->cell_merge('O4:U4');
		$this->excel->set_row_height('4', 20);
		$this->excel->set_horizon_align('A3');
		$this->excel->set_vertical_align('A3');
		$this->excel->set_horizon_align('C3');
		$this->excel->set_vertical_align('C3');
		
		//作業内容
		$this->excel->cell_merge('A5:U5');
		$this->excel->cell_merge('A6:U16');
		$this->excel->set_vertical_align('A6', Alignment::VERTICAL_TOP);
		$this->excel->set_horizon_align('A6', Alignment::HORIZONTAL_LEFT);
		$this->excel->set_wrap_text('A6');
		
		//作業に対する疑問／不明点／反省等
		$this->excel->cell_merge('A17:U17');
		$this->excel->cell_merge('A18:U28');
		$this->excel->set_vertical_align('A18', Alignment::VERTICAL_TOP);
		$this->excel->set_horizon_align('A18', Alignment::HORIZONTAL_LEFT);
		$this->excel->set_wrap_text('A18');
		
		//その他
		$this->excel->cell_merge('A29:U29');
		$this->excel->cell_merge('A30:U40');
		$this->excel->set_vertical_align('A30', Alignment::VERTICAL_TOP);
		$this->excel->set_horizon_align('A30', Alignment::HORIZONTAL_LEFT);
		$this->excel->set_wrap_text('A30');
		
		//罫線
		$this->excel->set_border('A3:U40');
		
		//列幅
		$this->excel->set_column_width('A', 3.5);
		$this->excel->set_column_width('B', 3.5);
		$this->excel->set_column_width('C', 3.5);
		$this->excel->set_column_width('D', 3.5);
		$this->excel->set_column_width('E', 3.5);
		$this->excel->set_column_width('F', 3.5);
		$this->excel->set_column_width('G', 3.5);
		$this->excel->set_column_width('H', 3.5);
		$this->excel->set_column_width('I', 3.5);
		$this->excel->set_column_width('J', 3.5);
		$this->excel->set_column_width('K', 3.5);
		$this->excel->set_column_width('L', 3.5);
		$this->excel->set_column_width('M', 3.5);
		$this->excel->set_column_width('N', 3.5);
		$this->excel->set_column_width('O', 3.5);
		$this->excel->set_column_width('P', 3.5);
		$this->excel->set_column_width('Q', 3.5);
		$this->excel->set_column_width('R', 3.5);
		$this->excel->set_column_width('S', 3.5);
		$this->excel->set_column_width('T', 3.5);
		$this->excel->set_column_width('U', 3.5);
	}
}
?>