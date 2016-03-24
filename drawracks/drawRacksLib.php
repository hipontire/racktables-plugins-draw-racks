<?php
// Draw Racks to Excel 
// Class file

// 2016-04-04 - Hipontire <miyoshi@outlook.com>

error_reporting(E_ERROR | E_PARSE);

/**
 * 
 */
class DrawRacks
{
	/**
	 * Constructor
	 */
	function DrawRacks()
	{
		global $drawracks_conf;
		$this->load_config();
		$this->templatefile = $drawracks_conf['templatefile'];
		$this->check_file( $this->templatefile );
		if( $this->check_file( dirname(__FILE__) . '/Classes/PHPExcel.php' ) )
				require_once dirname(__FILE__) . '/Classes/PHPExcel.php';
		if( $this->check_file( dirname(__FILE__) . '/Classes/PHPExcel/IOFactory.php' ) )
				require_once dirname(__FILE__) . '/Classes/PHPExcel/IOFactory.php';
	}

	/**
	 * Load Configuration Values
	 */
	function load_config()
	{
		global $drawracks_conf;
		if( ! isset( $drawracks_conf['templatefile'] ) )	$drawracks_conf['templatefile'] = dirname(__FILE__) . "/xlsx/drawracks.xlsx";
		if( ! isset( $drawracks_conf['tabname'] ) )		$drawracks_conf['tabname'] = "drawracks";
 		if( ! isset( $drawracks_conf['location_name_label'] ) )	$drawracks_conf['location_name_label'] = "Location";
		if( ! isset( $drawracks_conf['row_name_label'] ) )	$drawracks_conf['row_name_label'] = "Row";
		if( ! isset( $drawracks_conf['rack_name_label'] ) )	$drawracks_conf['rack_name_label'] = "Racks";
		if( ! isset( $drawracks_conf['name_label'] ) )		$drawracks_conf['name_label'] = "Name";
		if( ! isset( $drawracks_conf['front_label'] ) )	$drawracks_conf['front_label'] = "Front";
		if( ! isset( $drawracks_conf['interior_label'] ) )	$drawracks_conf['interior_label'] = "Interior";
		if( ! isset( $drawracks_conf['back_label'] ) )		$drawracks_conf['back_label'] = "Back";
		if( ! isset( $drawracks_conf['export_button'] ) )	$drawracks_conf['export_button'] = "Export rack layout";
		if( ! isset( $drawracks_conf['empty_now'] ) )		$drawracks_conf['empty_now'] = "(empty now)";
		if( ! isset( $drawracks_conf['empty_row'] ) )		$drawracks_conf['empty_row'] = "No rows found";
		if( ! isset( $drawracks_conf['file_not_found'] ) )	$drawracks_conf['file_not_found'] = "Template file not found.";
		if( ! isset( $drawracks_conf['not_specified'] ) )	$drawracks_conf['not_specified'] = "Rack has not been specified.";
		if( ! isset( $drawracks_conf['bgstate_F'] ) )		$drawracks_conf['bgstate_F'] = "8fbfbf";
		if( ! isset( $drawracks_conf['bgstate_A'] ) )		$drawracks_conf['bgstate_A'] = "bfbfbf";
		if( ! isset( $drawracks_conf['bgstate_U'] ) )		$drawracks_conf['bgstate_U'] = "bf8f8f";
		if( ! isset( $drawracks_conf['bgstate_T'] ) )		$drawracks_conf['bgstate_T'] = "408080";
		if( ! isset( $drawracks_conf['bgcell_border'] ) )	$drawracks_conf['bgcell_border'] = "000000";
	}

	/**
	 * Check Template File
	 */
	function check_file( $file )
	{
		if( ! file_exists( $file ) ){
			echo "<p>" . $drawracks_conf['file_not_found'] . " ( " . $file . " )</p>";
			exit(0);
		}
		return true;
	}

	/**
	 * Output Excel File
	 */
	function output_excelfile()
	{
		$this->get_rackdata( $this->get_rackids() );
		if( count( $this->racks ) == 0 ){
			echo "<p>" . $drawracks_conf['not_specified'] . "</p>";
			exit(0);
		}
		$this->put_excel();
	}

	/**
	 * Get Rack IDs from cookie
	 */
	function get_rackids()
	{
		$retvals = explode(',',$_COOKIE['rack_ids']);
		array_pop($retvals);
		return $retvals;
	}

	/**
	 * Get Rack Data by rack_id
	 */
	function get_rackdata( $rackids )
	{
		$maxheight = 0;
		$rows = array();
		for( $i = 0; $i < count( $rackids ); $i ++ )
		{
			$rack = spotEntity ('rack', $rackids[$i]);
			array_push( $rows, intval($rack['row_id']) );
			$this->racks[$i] = $rack;
			$height = intval($rack['height']);
			if( $height > $maxheight ) $maxheight = $height;
		}
		$unique = array_unique($rows);
		$this->rows = array_values($unique);
		$this->maxheight = $maxheight;
	}
	 
	/**
	 * Output Excel
	 */
	function put_excel()
	{
		$obj = PHPExcel_IOFactory::createReader('Excel2007');
		$book = $obj->load($this->templatefile);
		$book->setActiveSheetIndex(0);
		$basesheet = $book->getSheet( 0 );
		$this->get_template_data($basesheet);
		$rowinfo = listCells ('row');
		for( $i = 0; $i < count( $this->rows ); $i++ ){
			$basesheet = $book->getSheet( 0 );
			$newsheet = $basesheet->copy();
			$newsheet->setTitle("drawracks" . ( $i + 1 ));
			$book->addSheet( $newsheet );
			$newsheet = $book->getSheetByName("drawracks" . ( $i + 1 ));
			$rowdata = $rowinfo[$this->rows[$i]];
			$rowdata['location_name'] = $this->get_location_name( $rowdata['location_id'] );
			$sheet = $this->set_sheetdata( $newsheet, $rowdata );
		}
		$book->removeSheetByIndex( 0 );
		$this->write_excel( $book );
	}

	/**
	 * Get Location Name
	 */
	function get_location_name( $location_id )
	{
		$locationidx = 0;
		$locationtree = '';
		while ($location_id)
		{
			if ($locationidx == 20) break;
			$parentlocation = spotEntity ('location', $location_id);
			$locationtree = sprintf ('%s %s', $parentlocation['name'], $locationtree);
			$location_id = $parentlocation['parent_id'];
			$locationidx++;
		}
		return $locationtree;
	}

	/**
	 * Excel Cell Posotion 
	 */
	function get_template_data($sheet)
	{
		$this->cellpos = array(
			'title'					=> array(),
			'location_name_label'	=> array(),
			'location_name'			=> array(),
			'row_name_label'		=> array(),
			'row_name'				=> array(),
			'name_label'			=> array(),
			'name'					=> array(),
			'layout_here'			=> array(),
			'front_label'			=> array(),
			'interior_label'		=> array(),
			'back_label'			=> array(),
			'neighbor_here'			=> array(),
			'col_here'				=> array(),
			'front_here'			=> array(),
			'interior_here'			=> array(),
			'back_here'				=> array()
		);
		
		for( $i = 0; $i < 16; $i ++ ){
			for( $j = 0; $j < 16; $j ++ ){
				$val = $sheet->getCellByColumnAndRow( $i, $j )->getValue();
				$sheet->setCellValueByColumnAndRow( $i, $j, "");
				if( preg_match("/\{(.)*\}/", $val )) {
					foreach( $this->cellpos as $key => $value ){
						if( preg_match("/\{" . $key . "\}/", $val )) {
							$this->cellpos[$key] = array( $i, $j );
						}
					}
				}
			}
		}
		$this->bgcolor = array(
			'name_label' => $sheet->getStyleByColumnAndRow($this->cellpos['name_label'][0],$this->cellpos['name_label'][1])->getFill()->getStartColor()->getRGB()
		);
	}

	/**
	 * Copy Sheet
	 */
	 function sheet_copy( $book, $sheet, $sheetname )
	 {
		$sheet = $book->getActiveSheet(0); 
		$newsheet = $sheet->copy();
		$newsheet->setTitle($sheetname); 
		$book->addExternalSheet($newsheet); 
		$newsheet = $book->getSheetByName($sheetname);
		return $newsheet;
	 }

	/**
	 * Set Sheet Data
	 */
	function set_sheetdata( $sheet, $rowdata )
	{
		global $drawracks_conf;
		$setvals = array (
			'title',
			'location_name_label',
			'location_name',
			'row_name_label',
			'row_name'
		);
		$rackdata = $drawracks_conf;
		$rackdata['location_name'] = $rowdata['location_name'];
		$rackdata['row_name'] = $rowdata['name'];
		foreach( $setvals as $val ){
			$sheet->setCellValueByColumnAndRow( $this->cellpos[$val][0], $this->cellpos[$val][1], $rackdata[$val] );
		}
		$distance = $this->cellpos['neighbor_here'][0] - $this->cellpos['layout_here'][0];
		$c = 0;
		foreach( $this->racks as $rack ){
			if( $rack['row_id'] != $rowdata['id'] ) continue;
			$rack['distance'] = $this->cellpos['layout_here'][0] + $c * $distance;
			$this->set_rackdata( $sheet, $rack );
			$c ++;
		}
		return $sheet;
	}


	/**
	 * Set Sheet Data
	 */
	function set_rackdata( $sheet, $rackdata )
	{
		global $drawracks_conf;
		// Set Rack Name
		$xposl = $this->cellpos['name_label'][0] + $rackdata['distance'] - 1;
		$yposl = $this->cellpos['name_label'][1];
		$sheet->mergeCellsByColumnAndRow( $xposl, $yposl, $xposl + 2, $yposl );
		$sheet->setCellValueByColumnAndRow( $xposl, $yposl, $drawracks_conf['name_label'] );
		$sheet->getStyleByColumnAndRow( $xposl, $yposl )->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
		$sheet->getStyleByColumnAndRow( $xposl, $yposl )->getFill()->getStartColor()->setRGB($this->bgcolor['name_label']);
		$xposn = $this->cellpos['name'][0] + $rackdata['distance'] - 1;
		$yposn = $this->cellpos['name'][1];
		$sheet->mergeCellsByColumnAndRow( $xposn, $yposn, $xposn + 2, $yposn );
		$sheet->setCellValueByColumnAndRow( $xposn, $yposn, $rackdata['name'] );
		$a1 = sprintf("%s%d:%s%d", PHPExcel_Cell::stringFromColumnIndex($xposl), $yposl, PHPExcel_Cell::stringFromColumnIndex($xposn + 2), $yposn); 
		$sheet->getStyle( $a1 )->getBorders()->getAllBorders()->setBorderStyle( PHPExcel_Style_Border::BORDER_THIN );
		// Set Rack Label
		$setvals = array (
			'front_label',
			'interior_label',
			'back_label'
		);
		$ypos = $this->cellpos['col_here'][1] + $this->maxheight;
		foreach( $setvals as $val ){
			$xpos = $this->cellpos[$val][0] + $rackdata['distance'] - 1;
			$ypos = $this->cellpos[$val][1] + $this->maxheight - $rackdata['height'];
			$sheet->setCellValueByColumnAndRow( $xpos, $ypos, $drawracks_conf[$val] );
			$sheet->getStyleByColumnAndRow( $xpos, $ypos )->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		}
		// Set Rack Number
		$xpos = $this->cellpos['col_here'][0] + $rackdata['distance'] - 1;
		for($i=0; $i<$rackdata['height']; $i++){
			$ypos = $this->cellpos['col_here'][1] + $rackdata['height'] - $i - 1;
			$sheet->setCellValueByColumnAndRow( $xpos, $ypos, $i + 1 );
			$sheet->getStyleByColumnAndRow( $xpos, $ypos )->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		}
		$a1 = sprintf("%s%d:%s%d", PHPExcel_Cell::stringFromColumnIndex($xpos), $this->cellpos['col_here'][1], PHPExcel_Cell::stringFromColumnIndex($xpos), $this->cellpos['col_here'][1] + $rackdata['height'] - 1); 
		$sheet->getStyle( $a1 )->getBorders()->getAllBorders()->setBorderStyle( PHPExcel_Style_Border::BORDER_THIN );
		$a1 = sprintf("%s%d:%s%d", PHPExcel_Cell::stringFromColumnIndex($xpos + 2), $this->cellpos['col_here'][1], PHPExcel_Cell::stringFromColumnIndex($xpos + 4), $this->cellpos['col_here'][1] + $rackdata['height'] - 1); 
		$sheet->getStyle( $a1 )->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
		$sheet->getStyle( $a1 )->getBorders()->getAllBorders()->getColor()->setRGB($drawracks_conf['bgcell_border']);
		// Set Rack Data
		amplifyCell ($rackdata);
		markAllSpans ($rackdata);
		for($i=$rackdata['height']; $i>0; $i--){
			$xposo = $this->cellpos['col_here'][0] + $rackdata['distance'] + 1;
			$ypos = $this->cellpos['col_here'][1] + $rackdata['height'] - $i;
			for($j=0; $j<3; $j++){
				if (isset ($rackdata[$i][$j]['skipped'])) continue;
				$xpos = $xposo + $j;
				$state = $rackdata[$i][$j]['state'];
				$colspan = (isset ($rackdata[$i][$j]['colspan'])) ? $rackdata[$i][$j]['colspan'] - 1 : 0;
				$rowspan = (isset ($rackdata[$i][$j]['rowspan'])) ? $rackdata[$i][$j]['rowspan'] - 1 : 0;
				switch ($state)
				{
					case 'T':
						$objectdata = spotEntity ('object', $rackdata[$i][$j]['object_id']);
						$sheet->mergeCellsByColumnAndRow( $xpos, $ypos, $xpos + $colspan, $ypos + $rowspan );
						$sheet->setCellValueByColumnAndRow( $xpos, $ypos, $objectdata['name'] );
						$sheet->getStyleByColumnAndRow( $xpos, $ypos )->getAlignment()->setWrapText(true);
						$sheet->getStyleByColumnAndRow( $xpos, $ypos )->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
						$a1 = sprintf("%s%d:%s%d", PHPExcel_Cell::stringFromColumnIndex($xpos), $ypos, PHPExcel_Cell::stringFromColumnIndex($xpos + $colspan), $ypos + $rowspan); 
						$sheet->getStyle( $a1 )->getBorders()->getOutline()->setBorderStyle( PHPExcel_Style_Border::BORDER_THIN );
						$sheet->getStyle( $a1 )->getBorders()->getOutline()->getColor()->setRGB('000000');
						$bgcolor = $drawracks_conf['bgstate_T'];
						break;
					case 'A':	// This rackspace does not exist
						$bgcolor = $drawracks_conf['bgstate_A'];
						break;
					case 'F':	// Free rackspace
						$bgcolor = $drawracks_conf['bgstate_F'];
						break;
					case 'U':	// Problematic rackspace, you CAN'T mount here
						$bgcolor = $drawracks_conf['bgstate_U'];
						break;
					default:	// No data
						break;
				}
				$sheet->getStyleByColumnAndRow( $xpos, $ypos )->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
				$sheet->getStyleByColumnAndRow( $xpos, $ypos )->getFill()->getStartColor()->setRGB($bgcolor);
			}
		}
		// Set Boader Frame
		$xpos = $this->cellpos['col_here'][0] + $rackdata['distance'];
		$ypos = $this->cellpos['col_here'][1];
		$a1 =  sprintf("%s%d:%s%d", PHPExcel_Cell::stringFromColumnIndex($xpos + 1), $ypos, PHPExcel_Cell::stringFromColumnIndex($xpos + 3), $ypos + $rackdata['height'] - 1); 
		$sheet->getStyle( $a1 )->getBorders()->getOutline()->setBorderStyle( PHPExcel_Style_Border::BORDER_THIN );
		$sheet->getStyle( $a1 )->getBorders()->getOutline()->getColor()->setRGB('000000');
		$a1 =  sprintf("%s%d:%s%d", PHPExcel_Cell::stringFromColumnIndex($xpos), $ypos - 1, PHPExcel_Cell::stringFromColumnIndex($xpos + 4), $ypos + $rackdata['height']); 
		$sheet->getStyle( $a1 )->getBorders()->getOutline()->setBorderStyle( PHPExcel_Style_Border::BORDER_MEDIUM );
		// Set Column Widths
		for($i=$this->cellpos['layout_here'][0]; $i<$this->cellpos['neighbor_here'][0]; $i++){
			$width = $sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($i))->getWidth();
			$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($i + $rackdata['distance'] - 1))->setWidth($width);
		}
		return $sheet;
	}
	
	/**
	 * Output Excel Book
	 */
	function write_excel( $book )
	{
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment;filename=drawracks_'.date("YmdHis").'.xlsx');
		header('Pragma: no-cache');
		header('Expires: 0');
		$writer = PHPExcel_IOFactory::createWriter($book, "Excel2007");
		$writer->save('php://output');
	}

	/**
	 * Output Form
	 */
	function output_form()
	{
		global $drawracks_conf;

		startPortlet($drawracks_conf['title']);
		echo "<table class=objview border=0 width='100%'><tr><td class=pcleft>";

		$found_racks = array();
		$cellfilter = getCellFilter();
		if (! ($cellfilter['is_empty'] && !isset ($_SESSION['locationFilter']) && renderEmptyResults ($cellfilter, 'racks', getEntitiesCount ('rack'))))
		{
			$rows = array();
			$rackCount = 0;
			foreach (listCells ('row') as $row_id => $rowInfo)
			{
				$rackList = applyCellFilter ('rack', $cellfilter, $row_id);
				$found_racks = array_merge ($found_racks, $rackList);
				$location_id = $rowInfo['location_id'];
				$locationIdx = 0;
				// contains location names in the form of 'grandparent parent child', used for sorting 
				$locationTree = '';
				// contains location names as well as links
				$hrefLocationTree = '';
				while ($location_id)
				{
					if ($locationIdx == 20)
					{
						showWarning ("Warning: There is likely a circular reference in the location tree.  Investigate location ${location_id}.");
						break;
					}
					$parentLocation = spotEntity ('location', $location_id);
					$locationTree = sprintf ('%s %s', $parentLocation['name'], $locationTree);
					$hrefLocationTree = "&raquo; " .
						"${parentLocation['name']} " .
						$hrefLocationTree;
					$location_id = $parentLocation['parent_id'];
					$locationIdx++;
				}
				$hrefLocationTree = substr ($hrefLocationTree, 8);
				$rows[] = array (
					'location_id' => $rowInfo['location_id'],
					'location_tree' => $locationTree,
					'href_location_tree' => $hrefLocationTree,
					'row_id' => $row_id,
					'row_name' => $rowInfo['name'],
					'racks' => $rackList
				);
				$rackCount += count($rackList);
			}

			// sort by location, then by row
			usort ($rows, 'rackspaceCmp');

			if (! renderEmptyResults($cellfilter, 'racks', $rackCount))
			{
				global $nextorder;
				// Zero value effectively disables the limit.
				$maxPerRow = getConfigVar ('RACKS_PER_ROW');
				$order = 'odd';
				if (! count ($rows))
					echo "<h2>" . $drawracks_conf['empty_row'] . "</h2>\n";
				else
				{
					echo "<form id=\"exportlayout\"><div style=\"float:left\">\n";
					echo '<table border=0 cellpadding=10 class=cooltable>';
					echo '<tr><th class=tdleft>' . $drawracks_conf['location_name_label'] . '</th><th class=tdleft>' . $drawracks_conf['row_name_label'] . '</th><th class=tdleft>' . $drawracks_conf['name_label'] . '</th></tr>';
					foreach ($rows as $row)
					{
						$rackList = $row['racks'];

						if (
							$location_id != '' and isset ($_SESSION['locationFilter']) and !in_array ($location_id, $_SESSION['locationFilter']) or
							empty ($rackList) and ! $cellfilter['is_empty']
						)
							continue;
						$rackListIdx = 0;
						echo "<tr class=\"row_${order} rackrow\"><th class=tdleft>${row['href_location_tree']}</th>";
						if (! count ($rackList))
						{
							echo "<th class=tdleft>${row['row_name']}</th>";
							echo "<th class=tdleft><table border=0 cellspacing=5><tr>";
							echo '<td>' . $drawracks_conf['empty_now'] . '</td>';
						} else {
							echo "<th class=tdleft><input type=checkbox id=row". $row['row_id'] ." class=rowcheck /><label for=row". $row['row_id'] ." class=rowlabel>${row['row_name']}</label></th>";
							echo "<th class=tdleft><table border=0 cellspacing=5><tr>";
							foreach ($rackList as $rack)
							{
								if ($rackListIdx > 0 and $maxPerRow > 0 and $rackListIdx % $maxPerRow == 0)
								{
									echo '</tr></table></th></tr>';
									echo "<tr class=row_${order}><th class=tdleft></th><th class=tdleft>${row['row_name']} (continued)";
									echo "</th><th class=tdleft><table border=0 cellspacing=5><tr>";
								}
								echo '<td align=center valign=bottom>' . $this->getRackThumbImg ($rack);
								echo '<br><input type=checkbox name=rackout id=rack' . $rack['id'] . ' value='. $rack['id'] . ' class=rackcheck />';
								echo  '<label for=rack' . $rack['id'] . '>' . $rack['name']. '</label></td>';
								$rackListIdx++;
							}
						}
						$order = $nextorder[$order];
						echo "</tr></table></th></tr>\n";
					}
					echo "</table>\n";
					echo "<div style=\"padding:20px 0 20px 0; text-align:right;\">\n";
					echo "<a href=\"index.php?page=reports&tab=rack&xlsx\" class=\"msg_neutral expbtn\" style=\"padding:12px 32px 12px 32px\">" . $drawracks_conf['export_button'] . "</a>\n";
					echo "</div>\n";
					echo "</div></form>\n";
				}
			}
		}
		echo '</td><td class=pcright width="25%">';
		renderCellFilterPortlet ($cellfilter, 'rack', $found_racks);
		echo "<br>\n";
		echo "</td></tr></table>\n";
		finishPortlet();
		
		if (count ($rows)) {
			echo <<<EOM
				<script type="text/javascript">
				$('.rowcheck').click(function() {
					if ($(this).is(':checked')) {
						$(this).parents('.rackrow').find('.rackcheck').attr('checked', 'checked');
					} else {
						$(this).parents('.rackrow').find('.rackcheck').removeAttr('checked');
					}
				});
				$('.rackcheck').click(function() {
					checknum = 0;
					$(this).parents('.rackrow').find('.rackcheck').each(function() {
						if($(this).is(':checked')){
							checknum ++;
						}
					});
					if(checknum == $(this).parent().parent().find('.rackcheck').length){
						$(this).parents('.rackrow').find('.rowcheck').attr('checked', 'checked')
					} else {
						$(this).parents('.rackrow').find('.rowcheck').removeAttr('checked')
					}
				});
				$('.rackimg').click(function() {
					if (!$(this).parent().find('input').is(':checked')) {
						$(this).parent().find('input').attr('checked', 'checked')
					} else {
						$(this).parent().find('input').removeAttr('checked')
					}
					checknum = 0;
					$(this).parents('.rackrow').find('.rackcheck').each(function() {
						if($(this).is(':checked')){
							checknum ++;
						}
					});
					if(checknum == $(this).parents('.rackrow').find('.rackcheck').length){
						$(this).parents('.rackrow').find('.rowcheck').attr('checked', 'checked')
					} else {
						$(this).parents('.rackrow').find('.rowcheck').removeAttr('checked')
					}
				});
				$('.expbtn').click(function() {
					checks = '';
					$('.rackcheck:checked').each(function(index, checkbox) {
						checks = checks + $(checkbox).val() + ',';
					});
					document.cookie = 'rack_ids=' + escape(checks) + '; path=/; ';
				});
				</script>
EOM;
		}
	}
	/**
	 * Display Rack Thumnails
	 */
	function getRackThumbImg ($rack, $scale = 1)
	{
		if (! is_int ($scale) || $scale <= 0)
			throw new InvalidArgException ('scale', $scale, 'must be a natural number');
		$width = getRackImageWidth() * $scale;
		$height = getRackImageHeight ($rack['height']) * $scale;
		$title = "${rack['height']} units";
		$src = '?module=image' .
			($scale == 1 ? '&img=minirack' : "&img=midirack&scale=${scale}") .
			"&rack_id=${rack['id']}";
		return "<img border=0 width=${width} height=${height} title='${title}' src='${src}' class=rackimg>";
	}
}
?>
