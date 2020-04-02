<?php

/**
 * SalaryDetailListModel
 * @author takanori_gozu
 *
 */
class SalaryDetailListModel extends MY_Model {
	
	/**
	 * 一覧
	 */
	public function get_list() {
		
		$this->set_table(SalaryDetailDao::TABLE_NAME, self::DB_TRAN);
		
		$this->add_select(SalaryDetailDao::COL_SUPPLY_YM);
		$this->add_select(SalaryDetailDao::COL_EMPLOYEE_NO);
		$this->add_select_as("''", 'employee_name');
		
		$this->add_where(SalaryDetailDao::COL_EMPLOYEE_NO, $this->get_session('employee_no'));
		$this->add_order(SalaryDetailDao::COL_SUPPLY_YM, self::ORDER_DESC);
		
		$list = $this->do_select();
		
		foreach ($list as &$row) {
			$row['employee_name'] = $this->get_session('user_name'); //Sessionの値をそのまま入れる(社員マスタから引いてくることはしない)
		}
		
		return $list;
	}
	
	/**
	 * 項目名
	 */
	public function get_list_col() {
		
		$list_col = array();
		
		$list_col[] = array('width' => 300, 'value' => '支給月');
		$list_col[] = array('width' => 300, 'value' => '氏名');
		$list_col[] = array('width' => 120, 'value' => '詳細');
		
		return $list_col;
	}
	
	/**
	 * 明細データ取得
	 */
	public function get_info($supply_ym) {
		
		$this->set_table(SalaryDetailDao::TABLE_NAME, self::DB_TRAN);
		
		$this->add_select(SalaryDetailDao::COL_BASIC_SALARY);
		$this->add_select(SalaryDetailDao::COL_NON_CONTRACT_ALLOWANCE);
		$this->add_select(SalaryDetailDao::COL_TECHNICAL_ALLOWANCE);
		$this->add_select(SalaryDetailDao::COL_DISPATCH_ALLOWANCE);
		$this->add_select(SalaryDetailDao::COL_FULL_SERVICE_ALLOWANCE);
		$this->add_select(SalaryDetailDao::COL_QUALIFICATION_ALLOWANCE);
		$this->add_select(SalaryDetailDao::COL_OTHER_ALLOWANCE);
		$this->add_select(SalaryDetailDao::COL_TRAFFIC_COST);
		$this->add_select(SalaryDetailDao::COL_EXPENSES_COST);
		$this->add_select(SalaryDetailDao::COL_HEALTH_INSURANCE_PREMIUM);
		$this->add_select(SalaryDetailDao::COL_NURSING_CARE_PREMIUM);
		$this->add_select(SalaryDetailDao::COL_EMPLOYEE_PENSION);
		$this->add_select(SalaryDetailDao::COL_EMPLOYEMENT_INSURANCE);
		$this->add_select(SalaryDetailDao::COL_INCOME_TAX);
		$this->add_select(SalaryDetailDao::COL_RESIDENT_TAX);
		$this->add_select(SalaryDetailDao::COL_TAXATION_SALARY);
		$this->add_select(SalaryDetailDao::COL_NON_TAXATION_SALARY);
		$this->add_select(SalaryDetailDao::COL_SALARY_TOTAL);
		$this->add_select(SalaryDetailDao::COL_DEDUCTION_TOTAL);
		$this->add_select(SalaryDetailDao::COL_TRANSFER_TOTAL);
		$this->add_select(SalaryDetailDao::COL_REMARK);
		
		$this->add_where(SalaryDetailDao::COL_SUPPLY_YM, $supply_ym);
		$this->add_where(SalaryDetailDao::COL_EMPLOYEE_NO, $this->get_session('employee_no'));
		
		return $this->do_select_info();
	}
	
	/**
	 * 明細の作成(PDF)
	 */
	public function make_pdf($supply_ym, $data) {
		
		$this->load->model('common/PdfModel', 'pdf');
		$this->pdf->reset();
		$this->pdf->set_font();
		$this->pdf->set_title('給料明細');
		
		$html = $this->make_html($supply_ym, $data);
		
		$this->pdf->write_html($html);
		
		$this->pdf->output('meisai_'. $supply_ym);
	}
	
	/**
	 * HTML生成
	 */
	private function make_html($supply_ym, $data) {
		
		$html = <<< EOF
			<html lang="ja">
				<head>
					<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
					<title>給料明細</title>
				</head>
				<body>
					<div>
						<img alt="株式会社アクシス" src="[%img_url]" style="width: 150px;">
					</div>
					<h2>[%ym] 給料明細書</h2>
					<h3 style="margin-left: 5%;">支給</h3>
					<table style="vertical-align: middle; margin-left: 5%;">
						<thead>
							<tr style="background-color: #9fff99; text-align: center;">
								<td style="border: 1px solid black;">基本給</td>
								<td style="border: 1px solid black;">契約外手当</td>
								<td style="border: 1px solid black;">技術手当</td>
								<td style="border: 1px solid black;">出向手当</td>
								<td style="border: 1px solid black;">皆勤手当</td>
								<td style="border: 1px solid black;">資格手当</td>
								<td style="border: 1px solid black;">その他手当</td>
								<td style="border: 1px solid black;">交通費</td>
								<td style="border: 1px solid black;">立替金</td>
							</tr>
						</thead>
						<tbody>
							<tr style="text-align: right;">
								<td style="padding-right: 20px; border: 1px solid black;">[%0] 円</td>
								<td style="border: 1px solid black;">[%1] 円</td>
								<td style="border: 1px solid black;">[%2] 円</td>
								<td style="border: 1px solid black;">[%3] 円</td>
								<td style="border: 1px solid black;">[%4] 円</td>
								<td style="border: 1px solid black;">[%5] 円</td>
								<td style="border: 1px solid black;">[%6] 円</td>
								<td style="border: 1px solid black;">[%7] 円</td>
								<td style="border: 1px solid black;">[%8] 円</td>
							</tr>
						</tbody>
					</table>
					<h3 style="margin-left: 5%;">控除</h3>
					<table style="vertical-align: middle; margin-left: 5%;">
						<thead>
							<tr style="background-color: #9fff99; text-align: center;">
								<td style="border: 1px solid black;">健康保険料</td>
								<td style="border: 1px solid black;">介護保険料</td>
								<td style="border: 1px solid black;">厚生年金</td>
								<td style="border: 1px solid black;">雇用保険</td>
								<td style="border: 1px solid black;">所得税</td>
								<td style="border: 1px solid black;">住民税</td>
							</tr>
						</thead>
						<tbody>
							<tr style="text-align: right;">
								<td style="border: 1px solid black;">[%9] 円</td>
								<td style="border: 1px solid black;">[%10] 円</td>
								<td style="border: 1px solid black;">[%11] 円</td>
								<td style="border: 1px solid black;">[%12] 円</td>
								<td style="border: 1px solid black;">[%13] 円</td>
								<td style="border: 1px solid black;">[%14] 円</td>
							</tr>
						</tbody>
					</table>
					<h3 style="margin-left: 5%;">合計</h3>
					<table style="vertical-align: middle; margin-left: 5%;">
						<thead>
							<tr style="background-color: #9fff99; text-align: center;">
								<td style="border: 1px solid black;">課税対象額</td>
								<td style="border: 1px solid black;">非課税対象額</td>
								<td style="border: 1px solid black;">総支給額</td>
								<td style="border: 1px solid black;">控除額</td>
								<td style="border: 1px solid black;">振込支給額</td>
							</tr>
						</thead>
						<tbody>
							<tr style="text-align: right;">
								<td style="border: 1px solid black;">[%15] 円</td>
								<td style="border: 1px solid black;">[%16] 円</td>
								<td style="border: 1px solid black;">[%17] 円</td>
								<td style="border: 1px solid black;">[%18] 円</td>
								<td style="border: 1px solid black;">[%19] 円</td>
							</tr>
						</tbody>
					</table>
					<h3 style="margin-left: 5%;">備考</h3>
					<p style="width: 100%; border: 1px solid black;">[%20]</p>
				</body>
			</html>
EOF;
		$i = 0;
		
		$html = str_replace("[%ym]", date('Y年n月', strtotime($supply_ym)). '分', $html);
		$html = str_replace("[%img_url]", base_url(). 'view/_images/logo.png', $html);
		
		//データ部分の置換
		foreach ($data as $key => $value) {
			if ($key == SalaryDetailDao::COL_REMARK) {
				$value = ($value != null) ? nl2br_except_pre($value) : '';
			} else {
				$value = number_format($value);
			}
			$html = str_replace("[%$i]", $value, $html);
			$i++;
		}
		
		return $html;
	}
}
?>