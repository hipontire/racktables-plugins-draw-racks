<?php
// Draw Racks to Excel 
// Config file (for Japanese)

// 2016-04-04 - Hipontire <miyoshi@outlook.com>

global $drawracks_conf;
$drawracks_conf = array (
	'templatefile'		=> "../plugins/drawracks/xlsx/drawracks.xlsx",
	'title'			=> "ラック配置図",
	'location_name_label'	=> "設置場所",
	'row_name_label'	=> "列名",
	'name_label'		=> "ラック",
	'front_label'		=> "前面",
	'interior_label'	=> "内部",
	'back_label'		=> "背面",
	'export_button'		=> "配置図ファイルを出力",
	'empty_now'			=> "(登録がありません)",
	'empty_row'			=> "(登録されている列がありません)",
	'file_not_found'	=> "ファイルがありません",
	'not_specified'		=> "ラックが指定されていません",
	'bgstate_F'			=> "afcfcf",
	'bgstate_A'			=> "cfcfcf",
	'bgstate_U'			=> "cfafaf",
	'bgstate_T'			=> "70a0a0",
	'bgcell_border'			=> "707070"
);
$tab['reports']['rack'] = 'ラック配置図';
?>
