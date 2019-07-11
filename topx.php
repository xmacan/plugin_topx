<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2015-2019 Petr Macek                                      |
 |                                                                         |
 | This program is free software; you can redistribute it and/or           |
 | modify it under the terms of the GNU General Public License             |
 | as published by the Free Software Foundation; either version 2          |
 | of the License, or (at your option) any later version.                  |
 |                                                                         |
 | This program is distributed in the hope that it will be useful,         |
 | but WITHOUT ANY WARRANTY; without even the implied warranty of          |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the           |
 | GNU General Public License for more details.                            |
 +-------------------------------------------------------------------------+
 | Cacti: The Complete RRDtool-based Graphing Solution                     |
 +-------------------------------------------------------------------------+
 | This code is designed, written, and maintained by the Cacti Group. See  |
 | about.php and/or the AUTHORS file for specific developer information.   |
 +-------------------------------------------------------------------------+
 | https://github.com/xmacan/                                              |
 | http://www.cacti.net/                                                   |
 +-------------------------------------------------------------------------+
*/

chdir('../../');
include_once('./include/auth.php');
include_once('./include/global_arrays.php');

set_default_action();

general_header();

$selectedTheme = get_selected_theme();

print '<link type="text/css" href="' . $config['url_path'] . 'plugins/topx/themes/common.css" rel="stylesheet">';
print '<link type="text/css" href="' . $config['url_path'] . 'plugins/topx/themes/' . $selectedTheme . '.css" rel="stylesheet">';

if (read_config_option('dsstats_enable') != 'on')	{
    print 'Please enable and configure <a href="' . $config['url_path'] .'settings.php?tab=data">DS stats</a>'; 
    bottom_footer();
    die();
}


function human_readable($size) {
    if ($size > 999)	{
	for($i = 0; ($size / 1024) > 0.9; $i++, $size /= 1024) {}
	return round($size, [2,2,2,2,2,3,3,4,4][$i]).[' ','k','M','G','T','P','E','Z','Y'][$i];
    }
    else	{
	return (round($size,3));
    }
}



$xar_ds = db_fetch_assoc ('SELECT distinct(t1.name) as dsname,t1.id as dsid, count(t1.id) as dscount FROM data_template AS t1 
			    LEFT JOIN data_template_data AS t2 ON t1.id=t2.data_template_id 
			    GROUP BY data_template_id'); 

foreach ($xar_ds as $ds)	{
    $tmp = $ds['dsid'];
    $ar_ds[$tmp]['key']   = $ds['dsid'];
    $ar_ds[$tmp]['name']  = $ds['dsname'];
    $ar_ds[$tmp]['count'] = $ds['dscount'];
}

$ar_age = array ('hour' => 'Last Hour', 'day' => 'Last Day', 'week' => 'Last Week', 'month' => 'Last Month', 'year' => 'Last year');
$ar_topx = array ('5' => 'Top 5', '10' => 'Top 10', '20' => 'Top 20', '50' => 'Top 50', '0' => 'All');
$ar_sort = array ('normal' => 'normal', 'reverse' => 'reverse');

/* if the user pushed the 'clear' button */
if (get_request_var('clear_x')) {
    unset($_SESSION['age']);
    unset($_SESSION['topx']);
    unset($_SESSION['sort']);
}

if ( isset_request_var ('ds') && array_key_exists (get_request_var ('ds'), $ar_ds))
    $_SESSION['ds'] = get_filter_request_var('ds');
if (!isset($_SESSION['ds']))
    $_SESSION['ds'] = key($ar_ds);

if ( isset_request_var ('age') && array_key_exists (get_request_var ('age'), $ar_age))
    $_SESSION['age'] = get_request_var ('age');
if (!isset($_SESSION['age']))
    $_SESSION['age'] = 'hour';

if ( isset_request_var ('topx') && array_key_exists (get_request_var ('topx'), $ar_topx))
    $_SESSION['topx'] = get_filter_request_var('topx');
if (!isset($_SESSION['topx']))
    $_SESSION['topx'] = 5;

if ( isset_request_var ('sort') && array_key_exists (get_request_var ('sort'), $ar_sort))
    $_SESSION['sort'] = get_request_var ('sort');
if (!isset($_SESSION['sort']) || !is_string($_SESSION['sort']))
    $_SESSION['sort'] = 'normal';
?>

<script type="text/javascript">
<!--
function applyViewAgeFilterChange(objForm) {
	strURL = '?ds=' + objForm.ds.value;
	strURL = strURL + '&age=' + objForm.age.value;
	strURL = strURL + '&topx=' + objForm.topx.value;
	strURL = strURL + '&sort=' + objForm.sort.value;
	document.location = strURL;
}
-->
</script>
<?php

html_start_box('<strong>TopX</strong>', '100%', '', '3', 'center', '');

?>

<tr>
 <td>
  <form name='form_topx' action='topx.php'>
   <table width='100%' cellpadding='0' cellspacing='0'>
    <tr class='navigate_form'>
     <td nowrap style='white-space: nowrap;' width='50'>
      Data source:&nbsp;
     </td>
     <td width='1'>
      <select name='ds' onChange='applyViewAgeFilterChange(document.form_topx)' id='ds'>

<?php

foreach ($ar_ds as $ds)	{
    if ($_SESSION['ds'] == $ds['key'])
	print '<option value="' . $ds['key'] . '" selected="selected">' . $ds['name'] . ' (' . $ds['count'] . ')</option>';
    else    
	print '<option value="' . $ds['key'] . '">' . $ds['name'] . ' (' . $ds['count'] . ')</option>';
}
?>

      </select>
     </td>
     <td nowrap style='white-space: nowrap;' width='50'>
      Age:&nbsp;
     </td>
     <td width='1'>
      <select name='age' onChange='applyViewAgeFilterChange(document.form_topx)'>

<?php
foreach ($ar_age as $key=>$value)	{
    if ($_SESSION['age'] == $key)
	print '<option value="' . $key . '" selected="selected">' . $value . '</option>';
    else    
	print '<option value="' . $key . '">' . $value .'</option>';
}
?>

      </select>
     </td>
     <td nowrap style='white-space: nowrap;' width='50'>
      &nbsp;Number of records:&nbsp;
     </td>
     <td width='1'>
      <select name='topx' onChange='applyViewAgeFilterChange(document.form_topx)'>

<?php
foreach ($ar_topx as $key=>$value)	{
    if ($_SESSION['topx'] == $key)
	print '<option value="' . $key . '" selected="selected">' . $value . '</option>';
    else
	print '<option value="' . $key . '">' . $value . '</option>';
}
?>

      </select>
     </td>
     <td nowrap style='white-space: nowrap;' width='20'>
      &nbsp;Order:&nbsp;
     </td>
     <td width='1'>
      <select name='sort' onChange='applyViewAgeFilterChange(document.form_topx)'>
<?php
foreach ($ar_sort as $key=>$value)	{
    if ($_SESSION['sort'] == $key)
	print '<option value="' . $key . '" selected="selected">' . $value . '</option>';
    else
	print '<option value="' . $key . '">' . $value . '</option>';
}
?>
      </select>
     </td>
     <td nowrap>
      &nbsp;<input type='submit' value='Go' title='Set/Refresh Filters'>
      <input type='submit' name='clear_x' value='Clear' title='Clear Filters'>
     </td>
    </tr>
  </table>
 </form>
</td>
</tr>

<?php

html_end_box();

print '<h3 class="topx_h3">' . db_fetch_cell ('SELECT name FROM data_template WHERE id=' . $_SESSION['ds']) . '</h3>';

$columns = " t1.local_data_id as local_data_id, concat(t1.name_cache,' - ', t2.rrd_name) as name, t2.average as value, t2.peak as peak ";
$query = ' FROM data_template_data AS t1 LEFT JOIN ';

switch ($_SESSION['age'])	{
    case 'hour':
	$query .= 'data_source_stats_hourly ';
    break;
    case 'day':
	$query .= 'data_source_stats_daily ';
    break;
    case 'week':
	$query .= 'data_source_stats_weekly ';
    break;
    case 'month':
	$query .= 'data_source_stats_monthly ';
    break;
    case 'year':
	$query .= 'data_source_stats_yearly ';
    break;
}  
    
$query .= ' AS t2 ON t1.local_data_id = t2.local_data_id 
    WHERE t1.data_template_id = ' . $_SESSION['ds'] .
    ' ORDER BY t2.average ';
    
$query .= $_SESSION['sort'] == 'normal' ? 'ASC ' : 'DESC ';
if ($_SESSION['topx'] > 0)    
    $query .= 'LIMIT ' . $_SESSION['topx'];

// print 'SELECT ' . $columns . ' ' . $query;

$graph = array();
$label = array();

$avg = round(db_fetch_cell ('SELECT avg(average)' . $query),2);
		
array_push($label,'Average all');
array_push($graph,$avg);

$result = db_fetch_assoc("SELECT $columns $query");

print '<table  class="topx_table">';
print '<tr><th>Data source</th><th>Avg. value</th><th>Peak</th></tr>';

foreach ($result as $row)	{
    $graph_id = db_fetch_cell ('SELECT DISTINCT(local_graph_id) FROM graph_templates_item
                                        LEFT JOIN data_template_rrd ON (graph_templates_item.task_item_id=data_template_rrd.id)
                                        LEFT JOIN data_local ON (data_template_rrd.local_data_id=data_local.id)
                                        LEFT JOIN data_template_data ON (data_local.id=data_template_data.local_data_id)
                                        WHERE data_template_data.local_data_id=' . $row['local_data_id']);

    print '<tr><td><a href="' .  htmlspecialchars($config['url_path']) . 'graphs.php?action=graph_edit&id=' . $graph_id . '">' . $row['name'] . '</a></td>' .
	 '<td>' . human_readable($row['value']) . '</td>' .
	 '<td>' . human_readable($row['peak']) . '</td>';

    array_push ($graph,$row['value']);
    array_push ($label,$row['name']);
}

print '</table>';

$xid = 'x' . uniqid();

print '<div class="topx_graph"><br/><br/><canvas id="pie_' . $xid . '" width="700" height="' . (20+$_SESSION['topx']*25 ). '"></canvas>';
print "<script type='text/javascript'>";

$pie_labels = implode('","',$label);
$pie_values = implode(',',$graph);
$pie_title = 'todo :-)';

print <<<EOF
var $xid = document.getElementById("pie_$xid").getContext("2d");
new Chart($xid, {
    type: 'horizontalBar',
    data: {
        labels: ["$pie_labels"],
        datasets: [{
            backgroundColor: [ "#555555"],
            data: [$pie_values]
        }]
    },
    options: {
        responsive: false,
        legend: {
            display: false
         },
scales: {
		    xAxes: [{
			display: true,
			scaleLabel: {
			    display: true,
			    labelString: 'todo'
			}
		    }], 
		    },        
         
         
        tooltipTemplate: "<%= label %>",
    },
});
EOF;

print "</script></div>";
// end of graph	     

print '<br/><br/>';
print 'DS stats last major run time: ' .  read_config_option('dsstats_last_major_run_time') . '<br/>';    
print 'DS stats last daily run time: ' .  read_config_option('dsstats_last_daily_run_time') . '<br/>';    

bottom_footer();
