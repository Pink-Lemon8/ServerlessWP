<?php

class Table_Report extends HTML_Table
{
	public $headerRow;
	public $dataRows;

	//init data
	public function __construct($headerRow, $arrFormat, $dataRows)
	{
		$this->headerRow = $headerRow;
		$this->dataRows = $this->orderDataRow($dataRows);
		$this->arrFormats = $arrFormat;
		parent::__construct(null, 'pw_report', 1, 0, 4);
	}
	//order datarow same with order dataheader
	public function orderDataRow($dataRows)
	{
		$arrResult = array();
		if (!empty($dataRows)) {
			foreach ($dataRows as $node) {
				foreach ($this->headerRow as $k => $v) {
					$arrRow[$k] = $node[$k];
				}
				$arrResult[] = $arrRow;
			}
		}
		return $arrResult;
	}
	//check data is exist
	public function haveData()
	{
		return (!empty($this->dataRows) ? 1 : 0);
	}
	//render data table to html
	public function render()
	{

		//add data header to table
		$this->addHeader($this->headerRow);
		//add data row collection to table
		$this->addRows($this->dataRows);
		//add data row total to table
		$this->addRowTotal($this->dataRows);

		return parent::render();
	}
}

class HTML_Table
{
	public $rows = array();
	public $arrFormats;
	public $tableStr = '';

	public function __construct($id = null, $klass = null, $border = 0, $cellspacing = 2, $cellpadding = 0, $attr_ar = array())
	{
		$this->tableStr = "\n<table" . (!empty($id) ? " id=\"$id\"" : '') .
			(!empty($klass) ? " class=\"$klass\"" : '') . $this->addAttribs($attr_ar) .
			" border=\"$border\" cellspacing=\"$cellspacing\" cellpadding=\"$cellpadding\">\n";
	}

	//add data to header
	public function addHeader($arrHeader)
	{
		$this->addRow();
		//add data to header
		foreach ($arrHeader as $k => $v) {
			$class = $this->getCellClass($k, $v);
			$this->addCell($v, $class, 'header');
		}
	}
	//add data to datarow collection
	public function addRows($arrRows)
	{
		//add data to row
		$rowcount = 0;
		foreach ($arrRows as $row) {
			$class = '';
			if ($rowcount % 2) {
				$class = 'alt';
			}
			$this->addRow($class);
			$rowcount++;

			foreach ($row as $k => $v) {
				// if is number or price - total calculator and display data
				$func_format = "_format" . $this->arrFormats[$k];
				$new_value = $this->$func_format($v); //

				// report columns: pmpo_order_id pmpo_destination_city pmpo_destination_country pmpo_line_count pmpo_total_order_amount order_status
				// commission columns: indicator_date order_count total_commission
				$class = $this->getCellClass($k, $v);
				//print $k . '->' . $v . '<br />';

				if ($k === 'pmpo_order_id') {
					$new_value = '<a href="' . ADMIN_URL . 'ReportDetails.php?data=order-information&orderid=' . $v . '"  class="pw-order-info">' . $new_value . '</a>';
				}
				$this->addCell($new_value, $class);
			}
		}
	}
	public function getCellClass($heading, $value)
	{
		switch ($heading) {
			case 'pmpo_order_id':
				$class = "order-number";
				break;
			case 'order_status':
				$func = function ($value) {
					return str_replace(" ", "-", $value);
				};

				$classes = explode(' - ', $value);
				$class = strtolower(implode(" ", array_map($func, $classes))); //remove spaces & lowercase
				break;
			case 'pmpo_total_order_amount':
				$class = 'order-total';
				break;
			case 'pmpo_total_order_amount':
				$class = 'report-total order-total number';
				break;
			case 'pmpo_line_count':
				$class = 'report-total items number';
				break;
			case 'order_count':
				$class = 'order-count number';
				break;
			case 'total_commission':
				$class = 'total-commission number';
				break;
			default:
				$class = 'pw-report';
		}
		return $class;
	}
	//caculate total row and add to table
	public function addRowTotal($arrRows)
	{
		//Caculate total base on format
		$row_total = [];
		foreach ($arrRows as $row) {
			foreach ($row as $k => $v) {
				//check if exist in array fields total
				if ($this->arrFormats[$k] == 'Number' or $this->arrFormats[$k] == 'Price') {
					$row_total[$k] += $v;
				} else {
					$row_total[$k] = '';
				}
			}
		}
		//add row total to table
		$this->addRow();
		$count = 0;
		if (is_array($row_total) && (count($row_total) > 0)
		) {
			foreach ($row_total as $k => $v) {
				$class = $this->getCellClass($k, $v);
				if ($count == 0) {
					$new_value = "Total :";
				} else {
					$func_format = "_format" . $this->arrFormats[$k];
					$new_value = $this->$func_format($v);
				}
				$new_value = $new_value == '0' ? '' : $new_value;
				$class = $class . ' report-total';
				$this->addCell($new_value, $class);

				$count++;
			}
		}
	}
	//add attributes
	private function addAttribs($attr_ar)
	{
		$str = '';
		foreach ($attr_ar as $key => $val) {
			$str .= " $key=\"$val\"";
		}
		return $str;
	}
	//add row to table
	public function addRow($klass = null, $attr_ar = array())
	{
		$row = new HTML_TableRow($klass, $attr_ar);
		array_push($this->rows, $row);
	}
	//add cell to row
	public function addCell($data = '', $klass = null, $type = 'data', $attr_ar = array())
	{
		$cell = new HTML_TableCell($data, $klass, $type, $attr_ar);
		// add new cell to current row's list of cells
		$curRow = &$this->rows[count($this->rows) - 1]; // copy by reference
		array_push($curRow->cells, $cell);
	}
	//render to html
	public function render()
	{
		foreach ($this->rows as $row) {
			$this->tableStr .= !empty($row->klass) ? "  <tr class=\"$row->klass\"" : "  <tr";
			$this->tableStr .= $this->addAttribs($row->attr_ar) . ">\n";
			$this->tableStr .= $this->getRowCells($row->cells);
			$this->tableStr .= "  </tr>\n";
		}
		$this->tableStr .= "</table>\n";
		return $this->tableStr;
	}
	//get row by cells
	public function getRowCells($cells)
	{
		$str = '';
		foreach ($cells as $cell) {
			$tag = ($cell->type == 'data') ? 'td' : 'th';
			$str .= !empty($cell->klass) ? "    <$tag class=\"$cell->klass\"" : "    <$tag";
			$str .= $this->addAttribs($cell->attr_ar) . ">";
			$str .= $cell->data;
			$str .= "</$tag>\n";
		}
		return $str;
	}
	//format field that have type is number
	public function _formatNumber($value)
	{
		return $value;
	}

	// format field that have type is price

	public function _formatPrice($value)
	{
		$result = '';
		if (trim($value) != '' and trim($value) != '0') {
			$result = "$" . $value;
		}
		return $result;
	}

	//format default field

	public function _format($value)
	{
		return $value;
	}
}


class HTML_TableRow
{
	public function __construct($klass = null, $attr_ar = array())
	{
		$this->klass = $klass;
		$this->attr_ar = $attr_ar;
		$this->cells = array();
	}
}

class HTML_TableCell
{
	public function __construct($data, $klass, $type, $attr_ar)
	{
		$this->data = $data;
		$this->klass = $klass;
		$this->type = $type;
		$this->attr_ar = $attr_ar;
	}
}
