<?php

/**
 * NoticeListModel
 * @author takanori_gozu
 *
 */
class NoticeListModel extends MY_Model {
	
	/**
	 * 一覧
	 */
	public function get_list($search = null) {
		
		$this->set_table(NoticeDao::TABLE_NAME, self::DB_TRAN);
		
		$this->add_select(NoticeDao::COL_ID);
		$this->add_select(NoticeDao::COL_NOTICE_NAME);
		$this->add_select(NoticeDao::COL_REGIST_DATE);
		$this->add_select(NoticeDao::COL_PUBLISHED_DATE);
		$this->add_select(NoticeDao::COL_PRESENCE_CHK_FLG);
		$this->add_select_as("''", 'show_presence');
		
		//掲載期日を過ぎているものは表示しない
		$this->add_where(NoticeDao::COL_PUBLISHED_DATE, date('Y-m-d'), self::COMP_GREATER_EQUAL);
		
		$this->add_order(NoticeDao::COL_REGIST_DATE, self::ORDER_DESC);
		
		$list = $this->do_select();
		
		foreach ($list as &$infos) {
			if ($infos[NoticeDao::COL_PRESENCE_CHK_FLG] == '1') $infos['show_presence'] = '1';
		}
		
		return $list;
	}
	
	/**
	 * 項目名
	 */
	public function get_list_col() {
		
		$list_cols = array();
		
		$list_cols[] = array('width' => 70, 'value' => ''); //編集
		$list_cols[] = array('width' => 70, 'value' => 'ID');
		$list_cols[] = array('width' => 350, 'value' => 'お知らせタイトル');
		$list_cols[] = array('width' => 150, 'value' => '登録日');
		$list_cols[] = array('width' => 150, 'value' => '掲載期日');
		$list_cols[] = array('width' => 100, 'value' => '出欠確認');
		
		return $list_cols;
	}
	
	/**
	 * リンク
	 */
	public function get_link() {
		
		$link_list = array();
		
		$link_list[] = array('url' => 'notice/NoticeRegist/regist_input', 'class' => 'far fa-edit', 'value' => '登録');
		
		return $link_list;
	}
	
	/**
	 * 詳細
	 */
	public function get_notice_info($id) {
		
		$this->set_table(NoticeDao::TABLE_NAME, self::DB_TRAN);
		
		$this->add_where(NoticeDao::COL_ID, $id);
		
		return $this->do_select_info();
	}
	
	/**
	 * 出欠状況
	 */
	public function get_presence_list($notice_id) {
		
		//社員情報
		$this->set_table(EmployeeDao::TABLE_NAME, self::DB_MASTER);
		
		$this->add_select(EmployeeDao::COL_ID);
		$this->add_select(EmployeeDao::COL_NAME);
		
		//管理者および退職者は除く
		$this->add_where(EmployeeDao::COL_USER_LEVEL, self::LEVEL_ADMINISTRATOR, self::COMP_GREATER_THAN);
		$this->add_where(EmployeeDao::COL_RETIREMENT, '0');
		
		$user_list = $this->do_select();
		
		$this->set_table(PresenceDao::TABLE_NAME, self::DB_TRAN);
		
		$this->add_select(PresenceDao::COL_EMPLOYEE_ID);
		$this->add_select(PresenceDao::COL_ATTENDANT);
		$this->add_select(PresenceDao::COL_REASON);
		
		$this->add_where(PresenceDao::COL_NOTICE_ID, $notice_id);
		
		$presence_list = $this->do_select();
		
		$presence_value_map = $this->get_presence_map();
		$presence_info = $this->list_to_map($presence_list, 'employee_id');
		
		$list = array();
		
		foreach ($user_list as $user_info) {
			$id = $user_info['id'];
			$attendant = array_key_exists($id, $presence_info) ? $presence_value_map[$presence_info[$id]['attendant']] : $presence_value_map[0];
			$reason = array_key_exists($id, $presence_info) ? $presence_info[$id]['reason'] : '';
			$list[] = array('id' => $id, 'name' => $user_info['name'], 'presence' => $attendant, 'reason' => $reason);
		}
		
		//ソート
		$sort = array();
		foreach ($list as $key => $value) {
			$sort[$key] = $value['presence'];
		}
		array_multisort($sort, SORT_ASC, $list);
		
		return $list;
	}
	
	/**
	 * 出欠状況一覧の項目名
	 */
	public function get_presence_list_col() {
		
		$list_cols = array();
		
		$list_cols[] = array('width' => 150, 'value' => '名前');
		$list_cols[] = array('width' => 150, 'value' => '出欠状況');
		$list_cols[] = array('width' => 300, 'value' => '理由等');
		
		return $list_cols;
	}
	
	/**
	 * 参加・不参加のマッピング
	 */
	public function get_presence_map() {
		
		$map = array();
		
		$map[0] = "未回答";
		$map[1] = "参加";
		$map[2] = "不参加";
		
		return $map;
	}
	
	/**
	 * Excelのダウンロード
	 */
	public function make_excel($list, $file_name) {
		
		$this->load->model('common/PHPExcelModel', 'excel');
		
		$this->excel->init();
		
		$this->excel->set_sheet();
		$this->excel->set_pagesize_A4();
		$this->excel->set_title('出欠一覧');
		
		//項目
		$this->set_item_value();
		
		//一覧
		$row_idx = $this->set_list_value($list);
		
		$end_row = $row_idx - 1;
		
		//体裁
		$this->format_arrange($end_row);
		
		//ダウンロード
		$this->excel->save($file_name);
	}
	
	/**
	 * 項目部分
	 */
	private function set_item_value() {
		
		$col = 1;
		
		$this->excel->set_cell_value_R1C1($col++, 2, '社員名');
		$this->excel->set_cell_value_R1C1($col++, 2, '出欠状況');
		$this->excel->set_cell_value_R1C1($col++, 2, '備考（理由等）');
	}
	
	/**
	 * 一覧部分の出力
	 */
	private function set_list_value($list) {
		
		$row = 3;
		
		foreach ($list as $info) {
			
			$col = 1;
			
			foreach ($info as $key => $value) {
				if ($key != 'id') {
					$this->excel->set_cell_value_R1C1($col++, $row, $value);
				}
			}
			$row++;
		}
		
		return $row;
	}
	
	/**
	 * 体裁を整える
	 */
	private function format_arrange($end_row) {
		
		//セルの幅設定
		$this->excel->set_column_width('A', 20);
		$this->excel->set_column_width('B', 10);
		$this->excel->set_column_width('C', 30);
		
		//項目の横位置
		$this->excel->set_horizon_align('A2:C2');
		
		//罫線
		$this->excel->set_border('A2:C'. $end_row);
		
		//着色
		$this->excel->set_color('A2:C2', 'FFFF00');
		
		$total_row = $end_row + 1;
		
		//参加人数
		$this->excel->set_cell_value_A1('A'. $total_row, '参加人数');
		$this->excel->set_cell_value('B'. $total_row, '=COUNTIF(B3:B'. $end_row. ', "参加") & "人"');
	}
}
?>