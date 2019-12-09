<?php

/**
 * CostManageRegistModel
 * @author takanori_gozu
 *
 */
class CostManageRegistModel extends CostManageBaseModel {
	
	/**
	 * バリデーション
	 */
	public function validation($input) {
		
		$exts = array('jpeg', 'jpg', 'pdf', 'JPEG', 'JPG', 'PDF'); //拡張子チェック用
		$msgs = array();
		
		$input_type = $input['input_type'];
		$expenses_ymd = $input['expenses_ymd'];
		$transport = $input['transport'];
		$from_place = $input['from_place'];
		$to_place = $input['to_place'];
		$cost = $input['cost'];
		$expenses_detail = $input['expenses_detail'];
		$receipt_file_name = '';
		$before_len = 0;
		$after_len = 0;
		
		if ($input['action'] == 'regist') {
			$receipt_file_name = mb_convert_encoding($_FILES["receipt_file"]["name"], 'SJIS', 'UTF-8'); //領収書ファイルはSJISに変換してチェックする
			$before_len = mb_strlen($_FILES["receipt_file"]["name"]);
			$after_len = mb_strlen(mb_convert_encoding($receipt_file_name, 'UTF-8', 'SJIS'));
		}
		
		//未入力・未選択チェック
		if ($expenses_ymd == '') $msgs[] = $this->lang->line('err_not_select', array($this->lang->line('expenses_ymd')));
		
		if ($input_type == '1') {
			//交通費
			if (trim($transport) == '') $msgs[] = $this->lang->line('err_required', array($this->lang->line('transport')));
			if (trim($from_place) == '') $msgs[] = $this->lang->line('err_required', array($this->lang->line('from_place')));
			if (trim($to_place) == '') $msgs[] = $this->lang->line('err_required', array($this->lang->line('to_place')));
		}
		
		if (trim($cost) == '') $msgs[] = $this->lang->line('err_required', array($this->lang->line('cost')));
		if (trim($expenses_detail) == '') $msgs[] = $this->lang->line('err_required', array($this->lang->line('expenses_detail')));
		
		if ($msgs != null) return $msgs;
		
		//フォーマットチェック
		if (!preg_match("/^[0-9]+$/", $cost) || $cost == 0) {
			$msgs[] = $this->lang->line('err_regex_match', array($this->lang->line('cost')));
		}
		
		if ($msgs != null) return $msgs;
		
		//長さチェック
		if ($input_type == '1') {
			if (mb_strlen(trim($transport)) > 50) $msgs[] = $this->lang->line('err_max_length', array($this->lang->line('transport'), 50));
			if (mb_strlen(trim($from_place)) > 50) $msgs[] = $this->lang->line('err_max_length', array($this->lang->line('from_place'), 50));
			if (mb_strlen(trim($to_place)) > 50) $msgs[] = $this->lang->line('err_max_length', array($this->lang->line('to_place'), 50));
		}
		
		if ($msgs != null) return $msgs;
		
		if ($receipt_file_name != '') {
			if ($before_len != $after_len) {
				//環境依存文字対応
				$msgs[] = $this->lang->line('err_file_upload_env_character');
			}
			//ファイルサイズチェック
			if ($_FILES["receipt_file"]["error"] == 1 || $_FILES["receipt_file"]["error"] == 2) {
				//1…php.iniで設定されているupload_max_sizeを超えている場合に返される
				//2…htmlのhiddenで持っているMAX_FILE_SIZEを超えている場合に返される
				$msgs[] = $this->lang->line('err_file_bigger', array('3MB'));
			}
			//拡張子チェック
			$arr = explode(".", $receipt_file_name);
			$ext = $arr[1];
			if (!in_array($ext, $exts)) {
				$msgs[] = $this->lang->line('err_unmatch_ext', array(implode(",", $exts)));
			}
			//同一ファイルが存在している場合
			$ym = date('Ym', strtotime(date('Y-m-d')));
			$file_path = $this->lang->line('upload_dir'). $this->get_session('login_id'). "/". $ym. "/". $receipt_file_name;
			if (file_exists($file_path)) {
				$msgs[] = $this->lang->line('err_file_exist');
			}
		}
		
		return $msgs;
	}
	
	/**
	 * 詳細
	 */
	public function get_info($id) {
		
		$this->set_table(ExpensesDao::TABLE_NAME, self::DB_TRAN);
		
		$this->add_select(ExpensesDao::COL_ID);
		$this->add_select(ExpensesDao::COL_EXPENSES_YMD);
		$this->add_select(ExpensesDao::COL_REGIST_YM);
		$this->add_select(ExpensesDao::COL_INPUT_TYPE);
		$this->add_select(ExpensesDao::COL_PAY_TYPE);
		$this->add_select(ExpensesDao::COL_EXPENSES_TYPE);
		$this->add_select(ExpensesDao::COL_ROUND_TRIP_TYPE);
		$this->add_select(ExpensesDao::COL_TRANSPORT);
		$this->add_select(ExpensesDao::COL_FROM_PLACE);
		$this->add_select(ExpensesDao::COL_TO_PLACE);
		$this->add_select(ExpensesDao::COL_EXPENSES_DETAIL);
		$this->add_select(ExpensesDao::COL_COST);
		$this->add_select(ExpensesDao::COL_RECEIPT_FILE_NAME);
		
		$this->add_where(ExpensesDao::COL_ID, $id);
		
		return $this->do_select_info();
	}
	
	/**
	 * 新規登録
	 */
	public function db_regist($input) {
		
		$this->set_table(ExpensesDao::TABLE_NAME, self::DB_TRAN);
		
		switch ($input['input_type']) {
			case '1':
				//交通費
				$this->add_col_val(ExpensesDao::COL_EMPLOYEE_ID, $this->get_session('user_id'));
				$this->add_col_val(ExpensesDao::COL_EXPENSES_YMD, $input['expenses_ymd']);
				$this->add_col_val(ExpensesDao::COL_REGIST_YM, date('Y-m'));
				$this->add_col_val(ExpensesDao::COL_INPUT_TYPE, $input['input_type']);
				$this->add_col_val(ExpensesDao::COL_ROUND_TRIP_TYPE, $input['round_trip_type']);
				$this->add_col_val(ExpensesDao::COL_TRANSPORT, $input['transport']);
				$this->add_col_val(ExpensesDao::COL_FROM_PLACE, $input['from_place']);
				$this->add_col_val(ExpensesDao::COL_TO_PLACE, $input['to_place']);
				$this->add_col_val(ExpensesDao::COL_EXPENSES_DETAIL, $input['expenses_detail']);
				$this->add_col_val(ExpensesDao::COL_COST, $input['cost']);
				$this->add_col_val(ExpensesDao::COL_RECEIPT_FILE_NAME, $_FILES["receipt_file"]["name"]);
				break;
			case '2':
				//経費
				$this->add_col_val(ExpensesDao::COL_EMPLOYEE_ID, $this->get_session('user_id'));
				$this->add_col_val(ExpensesDao::COL_EXPENSES_YMD, $input['expenses_ymd']);
				$this->add_col_val(ExpensesDao::COL_REGIST_YM, date('Y-m'));
				$this->add_col_val(ExpensesDao::COL_INPUT_TYPE, $input['input_type']);
				$this->add_col_val(ExpensesDao::COL_PAY_TYPE, $input['pay_type']);
				$this->add_col_val(ExpensesDao::COL_EXPENSES_TYPE, $input['expenses_type']);
				$this->add_col_val(ExpensesDao::COL_EXPENSES_DETAIL, $input['expenses_detail']);
				$this->add_col_val(ExpensesDao::COL_COST, $input['cost']);
				$this->add_col_val(ExpensesDao::COL_RECEIPT_FILE_NAME, $_FILES["receipt_file"]["name"]);
				break;
		}
		
		return $this->do_insert_get_id(); //結果行を返す
	}
	
	/**
	 * ファイルのアップロード
	 */
	public function file_upload() {
		
		$this->load->model('common/FileOperationModel', 'file');
		
		$ym = date('Ym', strtotime(date('Y-m')));
		$upload_dir = $this->lang->line('upload_dir'). $this->get_session('login_id'). "/". $ym;
		
		if (!file_exists($upload_dir)) {
			$this->file->make_dir($upload_dir); //ディレクトリ作成
		}
		
		return $this->file->upload($upload_dir, $_FILES["receipt_file"]["tmp_name"], mb_convert_encoding($_FILES["receipt_file"]["name"], 'SJIS', 'UTF-8'));
	}
	
	/**
	 * 更新
	 */
	public function db_modify($input) {
		
		$this->set_table(ExpensesDao::TABLE_NAME, self::DB_TRAN);
		
		switch ($input['input_type']) {
			case '1':
				//交通費
				$this->add_col_val(ExpensesDao::COL_EXPENSES_YMD, $input['expenses_ymd']);
				$this->add_col_val(ExpensesDao::COL_INPUT_TYPE, $input['input_type']);
				$this->add_col_val(ExpensesDao::COL_ROUND_TRIP_TYPE, $input['round_trip_type']);
				$this->add_col_val(ExpensesDao::COL_TRANSPORT, $input['transport']);
				$this->add_col_val(ExpensesDao::COL_FROM_PLACE, $input['from_place']);
				$this->add_col_val(ExpensesDao::COL_TO_PLACE, $input['to_place']);
				$this->add_col_val(ExpensesDao::COL_EXPENSES_DETAIL, $input['expenses_detail']);
				$this->add_col_val(ExpensesDao::COL_COST, $input['cost']);
				break;
			case '2':
				//経費
				$this->add_col_val(ExpensesDao::COL_EXPENSES_YMD, $input['expenses_ymd']);
				$this->add_col_val(ExpensesDao::COL_INPUT_TYPE, $input['input_type']);
				$this->add_col_val(ExpensesDao::COL_PAY_TYPE, $input['pay_type']);
				$this->add_col_val(ExpensesDao::COL_EXPENSES_TYPE, $input['expenses_type']);
				$this->add_col_val(ExpensesDao::COL_EXPENSES_DETAIL, $input['expenses_detail']);
				$this->add_col_val(ExpensesDao::COL_COST, $input['cost']);
				break;
		}
		
		$this->add_where(ExpensesDao::COL_ID, $input['id']);
		
		$this->do_update();
	}
	
	/**
	 * ファイル削除
	 */
	public function file_delete($info) {
		$ym = date('Ym', strtotime($info[ExpensesDao::COL_REGIST_YM]));
		$path = $this->lang->line('upload_dir'). $this->get_session('login_id'). '/'. $ym. '/'. mb_convert_encoding($info[ExpensesDao::COL_RECEIPT_FILE_NAME], 'SJIS', 'UTF-8');
		unlink($path);
	}
	
	/**
	 * 削除
	 */
	public function db_delete($id) {
		
		$this->set_table(ExpensesDao::TABLE_NAME, self::DB_TRAN);
		
		$this->add_where(ExpensesDao::COL_ID, $id);
		
		$this->do_delete();
	}
}
?>