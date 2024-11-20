<?php
class Page_Report extends Utility_PageBase
{
	public $report_type;

	public function _process()
	{
		$this->setTemplate("page_report");
		// get report type that send from browser ;
		$this->report_type = trim($this->_getRequest('hdReportType', 'recent_orders'));

		/*base on report type, get apporiate information from pharmacy system */
		$mReport  = new Model_Report();
		$reply = $mReport->getDataByType($this->report_type);

		// make report with table trucuture by use report_table
		$arrHeader	= $reply->dataheader;
		//$arrFormat  = $this->_reFormatRows($reply->datatype) ;
		$arrFormat	= $this->_getFormatRows();
		$dataCollections = $reply->datarows;

		// make html content for report base on dataCollection,Array Header and Array format for column
		$tbl = new Table_Report($arrHeader, $arrFormat, $dataCollections);
		//check if report hava data
		if ($tbl->haveData()) {
			$html = $tbl->render();
		} else {
			$html = 'No data to display!';
		}
		$this->assign('REPORT_TABLE', $html);

		// make button and assign to templete
		$this->_makeTitleButton();
	}


	//Set format for each field of report
	public function _reFormatRows($typeRows)
	{
		$retArr = array();
		if (is_null($typeRows)) {
			return $retArr;
		}

		foreach ($typeRows as $k => $v) {
			$type = "";
			switch ($v) {
				case "int":
					$type = "Number";
					break;
				case "decimal":
					$type = "Price";
					break;
				default:
					break;
			}
			$retArr[$k] = $type;
		}
		return $retArr;
	}

	//Set format for each field of report
	public function _getFormatRows()
	{
		$retArr = array();
		switch ($this->report_type) {
			case 'recent_orders':
				$retArr =  array('pmpo_line_count' => 'Number', 'pmpo_total_order_amount' => 'Price');
				break;
			case 'commission_last_month':
				$retArr = array('total_commission' => 'Price', 'order_count' => 'Number', 'total_order_amount' => 'Price');
				break;
		}
		return	$retArr;
	}

	// Make lable and button foreach report
	public function _makeTitleButton()
	{
		$report_type_switch = '';
		$button_title = '';
		switch ($this->report_type) {
			case 'recent_orders':
				$report_type_switch = 'commission_last_month';
				$button_title = 'Sales';
				break;
			case 'commission_last_month':
				$report_type_switch = 'recent_orders';
				$button_title = 'Orders';
				break;
		}
		$this->assign('report_type', $report_type_switch);
		$this->assign('REPORT_BUTTON', $button_title);
	}
}
