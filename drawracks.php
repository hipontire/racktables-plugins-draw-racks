<?php
// Draw Racks to Excel
// 
// 2016-04-01 - Hipontire <miyoshi@outlook.com>

$tab['reports']['rack'] = 'DrawRacks';				// The title of the report tab
$tabhandler['reports']['rack'] = 'renderDrawRacks';	// register a report rendering function

if( file_exists( dirname(__FILE__) . '/drawracks/drawRacksConfig.php' ) )
	require_once dirname(__FILE__) . '/drawracks/drawRacksConfig.php';
require_once dirname(__FILE__) . '/drawracks/drawRacksLib.php';

function renderDrawRacks()
{
	global $drawracks_conf;

	$rp = new DrawRacks();
	if ( isset($_GET['xlsx']) ) {
		$rp->output_excelfile();
		exit(0);
	}

	// Handle the location filter
	startSession();
	if (isset ($_REQUEST['changeLocationFilter']))
		unset ($_SESSION['locationFilter']);
	if (isset ($_REQUEST['location_id']))
		$_SESSION['locationFilter'] = $_REQUEST['location_id'];
	session_commit();
	$rp->output_form();	
}
?>
