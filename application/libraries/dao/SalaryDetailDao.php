<?php

/**
 * 給料明細データテーブル定義ファイル
 * @author takanori_gozu
 *
 */
class SalaryDetailDao {
	
	const TABLE_NAME = 'salary_detail';
	
	const COL_SUPPLY_YM = 'supply_ym';
	const COL_EMPLOYEE_NO = 'employee_no';
	const COL_BASIC_SALARY = 'basic_salary';
	const COL_NON_CONTRACT_ALLOWANCE = 'non_contract_allowance';
	const COL_TECHNICAL_ALLOWANCE = 'technical_allowance';
	const COL_DISPATCH_ALLOWANCE = 'dispatch_allowance';
	const COL_FULL_SERVICE_ALLOWANCE = 'full_service_allowance';
	const COL_QUALIFICATION_ALLOWANCE = 'qualification_allowance';
	const COL_OTHER_ALLOWANCE = 'other_allowance';
	const COL_TRAFFIC_COST = 'traffic_cost';
	const COL_EXPENSES_COST = 'expenses_cost';
	const COL_HEALTH_INSURANCE_PREMIUM = 'health_insurance_premium';
	const COL_NURSING_CARE_PREMIUM = 'nursing_care_premium';
	const COL_EMPLOYEE_PENSION = 'employee_pension';
	const COL_EMPLOYEMENT_INSURANCE = 'employement_insurance';
	const COL_INCOME_TAX = 'income_tax';
	const COL_RESIDENT_TAX = 'resident_tax';
	const COL_TAXATION_SALARY = 'taxation_salary';
	const COL_NON_TAXATION_SALARY = 'non_taxation_salary';
	const COL_SALARY_TOTAL = 'salary_total';
	const COL_DEDUCTION_TOTAL = 'deduction_total';
	const COL_TRANSFER_TOTAL = 'transfer_total';
	const COL_REMARK = 'remark';
}
?>