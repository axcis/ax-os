<?php

/**
 * SalaryDetailRegistModel
 * @author takanori_gozu
 *
 */
class SalaryDetailRegistModel extends MY_Model {
	
	/**
	 * バリデーション
	 */
	public function validation($file_name) {
		
		$exts = array('csv', 'CSV'); //拡張子チェック用
		$msgs = array();
		
		//ファイルサイズチェック
		if ($_FILES["up_file"]["error"] == 1 || $_FILES["up_file"]["error"] == 2) {
			//1…php.iniで設定されているupload_max_sizeを超えている場合に返される
			//2…htmlのhiddenで持っているMAX_FILE_SIZEを超えている場合に返される
			$msgs[] = $this->lang->line('err_file_bigger', array('5MB'));
		}
		
		//ファイル名チェック
		if ($_FILES["up_file"]["name"] != 'meisai.csv') {
			$msgs[] = 'ファイル名はmeisai.csvでアップロードしてください。';
		}
		
		//拡張子チェック
		$arr = explode(".", $file_name);
		$ext = $arr[1];
		if (!in_array($ext, $exts)) {
			$msgs[] = $this->lang->line('err_unmatch_ext', array('csv'));
		}
		
		return $msgs;
	}
	
	/**
	 * tmpへアップロードする
	 */
	public function file_upload($file_name) {
		
		$this->load->model('common/FileOperationModel', 'file');
		
		$upload_dir = $this->lang->line('salary_detail_csv_dir');
		
		$result = $this->file->upload($upload_dir, $_FILES["up_file"]["tmp_name"], mb_convert_encoding($file_name, 'SJIS', 'UTF-8'));
		
		if ($result) {
			//パーミッションを変更(最後に消せるようにしておく)
			chmod($upload_dir. $file_name, 0644);
		}
		
		return $result;
	}
	
	/**
	 * csvファイルの読み込み
	 */
	public function read_csv($file_name, &$err) {
		
		$path = $this->lang->line('salary_detail_csv_dir'). $file_name;
		
		$file = new SplFileObject($path);
		$file->setFlags(SplFileObject::READ_CSV);
		
		$csv_data = array();
		
		foreach ($file as $line) {
			$this->line_validation($line, $err); //csvファイルのチェック
			$csv_data[] = $line;
		}
		
		//ファイルそのものは削除する
		unset($file);
		unlink($path);
		
		return $csv_data;
	}
	
	/**
	 * csvデータチェック
	 */
	private function line_validation(&$line, &$err) {
		
		//TODO 要仕様検討
		if (count($line) != 18) {
			$err = '1';
			return;
		}
		
		$supply_ym = $line[0];
		$employee_no = $line[1];
		$basic_salary = $line[2];
		$non_contract_allowance = $line[3];
		$technical_allowance = $line[4];
		$dispatch_allowance = $line[5];
		$full_service_allowance = $line[6];
		$qualification_allowance = $line[7];
		$other_allowance = $line[8];
		$traffic_cost = $line[9];
		$expenses_cost = $line[10];
		$health_insurance_premium = $line[11];
		$nursing_care_premium = $line[12];
		$employee_pension = $line[13];
		$employement_insurance = $line[14];
		$income_tax = $line[15];
		$resident_tax = $line[16];
		$remark = isset($line[17]) && $line[17] != '' ? $line[17] : '';
		
		$line[18] = '';
		
		//未入力チェック
		if (trim($supply_ym) == '') $line[18] = $this->lang->line('err_required', array($this->lang->line('supply_ym')));
		if (trim($employee_no) == '') $line[18] = $this->lang->line('err_required', array($this->lang->line('employee_no')));
		if (trim($basic_salary) == '') $line[18] = $this->lang->line('err_required', array($this->lang->line('basic_salary')));
		if (trim($non_contract_allowance) == '') $line[18] = $this->lang->line('err_required', array($this->lang->line('non_contract_allowance')));
		if (trim($technical_allowance) == '') $line[18] = $this->lang->line('err_required', array($this->lang->line('technical_allowance')));
		if (trim($dispatch_allowance) == '') $line[18] = $this->lang->line('err_required', array($this->lang->line('dispatch_allowance')));
		if (trim($full_service_allowance) == '') $line[18] = $this->lang->line('err_required', array($this->lang->line('full_service_allowance')));
		if (trim($qualification_allowance) == '') $line[18] = $this->lang->line('err_required', array($this->lang->line('qualification_allowance')));
		if (trim($other_allowance) == '') $line[18] = $this->lang->line('err_required', array($this->lang->line('other_allowance')));
		if (trim($traffic_cost) == '') $line[18] = $this->lang->line('err_required', array($this->lang->line('traffic_cost')));
		if (trim($expenses_cost) == '') $line[18] = $this->lang->line('err_required', array($this->lang->line('expenses_cost')));
		if (trim($health_insurance_premium) == '') $line[18] = $this->lang->line('err_required', array($this->lang->line('health_insurance_premium')));
		if (trim($nursing_care_premium) == '') $line[18] = $this->lang->line('err_required', array($this->lang->line('nursing_care_premium')));
		if (trim($employee_pension) == '') $line[18] = $this->lang->line('err_required', array($this->lang->line('employee_pension')));
		if (trim($employement_insurance) == '') $line[18] = $this->lang->line('err_required', array($this->lang->line('employement_insurance')));
		if (trim($income_tax) == '') $line[18] = $this->lang->line('err_required', array($this->lang->line('income_tax')));
		if (trim($resident_tax) == '') $line[18] = $this->lang->line('err_required', array($this->lang->line('resident_tax')));
		
		if ($line[18] != '') {
			$err = '1';
			return;
		}
		
		//フォーマットチェック
		if (!preg_match("/^[0-9]+$/", $basic_salary)) $line[18] = $this->lang->line('err_regex_match', array($this->lang->line('basic_salary')));
		if (!preg_match("/^[0-9]+$/", $non_contract_allowance)) $line[18] = $this->lang->line('err_regex_match', array($this->lang->line('non_contract_allowance')));
		if (!preg_match("/^[0-9]+$/", $technical_allowance)) $line[18] = $this->lang->line('err_regex_match', array($this->lang->line('technical_allowance')));
		if (!preg_match("/^[0-9]+$/", $dispatch_allowance)) $line[18] = $this->lang->line('err_regex_match', array($this->lang->line('dispatch_allowance')));
		if (!preg_match("/^[0-9]+$/", $full_service_allowance)) $line[18] = $this->lang->line('err_regex_match', array($this->lang->line('full_service_allowance')));
		if (!preg_match("/^[0-9]+$/", $qualification_allowance)) $line[18] = $this->lang->line('err_regex_match', array($this->lang->line('qualification_allowance')));
		if (!preg_match("/^[0-9]+$/", $other_allowance)) $line[18] = $this->lang->line('err_regex_match', array($this->lang->line('other_allowance')));
		if (!preg_match("/^[0-9]+$/", $traffic_cost)) $line[18] = $this->lang->line('err_regex_match', array($this->lang->line('traffic_cost')));
		if (!preg_match("/^[0-9]+$/", $expenses_cost)) $line[18] = $this->lang->line('err_regex_match', array($this->lang->line('expenses_cost')));
		if (!preg_match("/^[0-9]+$/", $health_insurance_premium)) $line[18] = $this->lang->line('err_regex_match', array($this->lang->line('health_insurance_premium')));
		if (!preg_match("/^[0-9]+$/", $nursing_care_premium)) $line[18] = $this->lang->line('err_regex_match', array($this->lang->line('nursing_care_premium')));
		if (!preg_match("/^[0-9]+$/", $employee_pension)) $line[18] = $this->lang->line('err_regex_match', array($this->lang->line('employee_pension')));
		if (!preg_match("/^[0-9]+$/", $employement_insurance)) $line[18] = $this->lang->line('err_regex_match', array($this->lang->line('employement_insurance')));
		if (!preg_match("/^[0-9]+$/", $income_tax)) $line[18] = $this->lang->line('err_regex_match', array($this->lang->line('income_tax')));
		if (!preg_match("/^[0-9]+$/", $resident_tax)) $line[18] = $this->lang->line('err_regex_match', array($this->lang->line('resident_tax')));
		
		if ($line[18] != '') {
			$err = '1';
			return;
		}
		
		//基本給0はエラー
		if ($basic_salary == 0) {
			$line[18] = $this->lang->line('err_bigger', array($this->lang->line('basic_salary'), 0));
			$err = '1';
		}
		
		return;
	}
	
	/**
	 * 項目名
	 */
	public function get_list_col() {
		
		//TODO 要仕様検討
		$list_col = array();
		
		$list_col[] = array('width' => 100, 'value' => '支給月');
		$list_col[] = array('width' => 100, 'value' => '社員番号');
		$list_col[] = array('width' => 100, 'value' => '基本給');
		$list_col[] = array('width' => 100, 'value' => '契約外手当');
		$list_col[] = array('width' => 100, 'value' => '技術手当');
		$list_col[] = array('width' => 100, 'value' => '出向手当');
		$list_col[] = array('width' => 100, 'value' => '皆勤手当');
		$list_col[] = array('width' => 100, 'value' => '資格手当');
		$list_col[] = array('width' => 100, 'value' => 'その他手当');
		$list_col[] = array('width' => 100, 'value' => '交通費');
		$list_col[] = array('width' => 100, 'value' => '立替金');
		$list_col[] = array('width' => 100, 'value' => '健康保険料');
		$list_col[] = array('width' => 100, 'value' => '介護保険料');
		$list_col[] = array('width' => 100, 'value' => '厚生年金');
		$list_col[] = array('width' => 100, 'value' => '雇用保険');
		$list_col[] = array('width' => 100, 'value' => '所得税');
		$list_col[] = array('width' => 100, 'value' => '住民税');
		$list_col[] = array('width' => 100, 'value' => '備考');
		$list_col[] = array('width' => 200, 'value' => 'エラー');
		
		return $list_col;
	}
	
	/**
	 * データの一括登録
	 */
	public function bulk_regist($data) {
		
		$insert_datas = array();
		
		foreach ($data as $info) {
			$this->make_data_map($insert_datas, $info);
		}
		
		$this->set_table(SalaryDetailDao::TABLE_NAME, self::DB_TRAN);
		$this->do_bulk_insert($insert_datas);
	}
	
	/**
	 * 計算しながらマップ生成
	 */
	private function make_data_map(&$datas, $info) {
		
		//TODO 要仕様検討
		$basic_salary = $info[2];
		$non_contract_allowance = $info[3];
		$technical_allowance = $info[4];
		$dispatch_allowance = $info[5];
		$full_service_allowance = $info[6];
		$qualification_allowance = $info[7];
		$other_allowance = $info[8];
		$traffic_cost = $info[9];
		$expenses_cost = $info[10];
		$health_insurance_premium = $info[11];
		$nursing_care_premium = $info[12];
		$employee_pension = $info[13];
		$employement_insurance = $info[14];
		$income_tax = $info[15];
		$resident_tax = $info[16];
		
		//計算
		$taxation_salary = $basic_salary + $non_contract_allowance + $technical_allowance + $dispatch_allowance + $full_service_allowance + $qualification_allowance + $other_allowance;
		$non_taxation_salary = $traffic_cost + $expenses_cost;
		$salary_total = $taxation_salary + $non_taxation_salary;
		$deduction_total = $health_insurance_premium + $nursing_care_premium + $employee_pension + $employement_insurance + $income_tax + $resident_tax;
		$transfer_total = $salary_total - $deduction_total;
		
		$datas[] = array(
				SalaryDetailDao::COL_SUPPLY_YM => $info[0],
				SalaryDetailDao::COL_EMPLOYEE_NO => $info[1],
				SalaryDetailDao::COL_BASIC_SALARY => $basic_salary,
				SalaryDetailDao::COL_NON_CONTRACT_ALLOWANCE => $non_contract_allowance,
				SalaryDetailDao::COL_TECHNICAL_ALLOWANCE => $technical_allowance,
				SalaryDetailDao::COL_DISPATCH_ALLOWANCE => $dispatch_allowance,
				SalaryDetailDao::COL_FULL_SERVICE_ALLOWANCE => $full_service_allowance,
				SalaryDetailDao::COL_QUALIFICATION_ALLOWANCE => $qualification_allowance,
				SalaryDetailDao::COL_OTHER_ALLOWANCE => $other_allowance,
				SalaryDetailDao::COL_TRAFFIC_COST => $traffic_cost,
				SalaryDetailDao::COL_EXPENSES_COST => $expenses_cost,
				SalaryDetailDao::COL_HEALTH_INSURANCE_PREMIUM => $health_insurance_premium,
				SalaryDetailDao::COL_NURSING_CARE_PREMIUM => $nursing_care_premium,
				SalaryDetailDao::COL_EMPLOYEE_PENSION => $employee_pension,
				SalaryDetailDao::COL_EMPLOYEMENT_INSURANCE => $employement_insurance,
				SalaryDetailDao::COL_INCOME_TAX => $income_tax,
				SalaryDetailDao::COL_RESIDENT_TAX => $resident_tax,
				SalaryDetailDao::COL_TAXATION_SALARY => $taxation_salary,
				SalaryDetailDao::COL_NON_TAXATION_SALARY => $non_taxation_salary,
				SalaryDetailDao::COL_SALARY_TOTAL => $salary_total,
				SalaryDetailDao::COL_DEDUCTION_TOTAL => $deduction_total,
				SalaryDetailDao::COL_TRANSFER_TOTAL => $transfer_total,
				SalaryDetailDao::COL_REMARK => $info[17]
		);
	}
}
?>