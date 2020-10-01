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

function human_readable ($bytes, $decimal = false)	{
//return($bytes);
	if ($bytes > 1000)	{
		$BYTE_UNITS = array(" ", "K", "M", "G", "T", "P", "E", "Z", "Y");

    		$BYTE_PRECISION = array(0, 0, 1, 2, 2, 3, 3, 4, 4);
    		if ($decimal) {
        		$BYTE_NEXT = 1000;
    		} else {
        		$BYTE_NEXT = 1024;
    		}

		for ($i = 0; ($bytes / $BYTE_NEXT) >= 0.9 && $i < count($BYTE_UNITS); $i++) $bytes /= $BYTE_NEXT;
        	return round($bytes, $BYTE_PRECISION[$i]) . $BYTE_UNITS[$i];
	}
	elseif ($bytes == 0) {
		return (0);
	}
       	elseif ($bytes < 1)     {
                $BYTE_UNITS = array(" ","m", "µ", "n", "p", "f", "a", "Z", "y");
                $BYTE_PRECISION = array(3,3, 3, 2, 2, 2, 1, 1, 1);
                if ($decimal) {
                        $BYTE_NEXT = 1000;
                } else {
                        $BYTE_NEXT = 1024;
                }

                for ($i = 0; ($bytes * $BYTE_NEXT) <= 10 && $i < count($BYTE_UNITS) ; $i++) {
//                echo "<hr>$i - $bytes<br/>";
                $bytes *= $BYTE_NEXT;
                }

                return round($bytes, $BYTE_PRECISION[$i]) . $BYTE_UNITS[$i];
        }
        else
               return (round($bytes,2));
}

/*
function first_operation ($value, $operation)	{
	
	if (strpos($operation, "/") !== false)	{	// format /number	
	
		$val = (int) substr($operation,strpos($operation,'/')+1);
		$value = $value/$val;
	
	}
	if (strpos($operation, "+") !== false)	{	// format x+y	

	}
	else {	//x=x
		// maydo do nothing
	}
	


	return($value);
}
*/


function final_operation ($value,$final_operation,$final_unit,$final_number) {

	if ( $final_operation == "strip")	{	// only round
		$value = round($value,$final_number) . " " . $final_unit;
	}
	elseif ( $final_operation == "/")	{ // kmgt + time
		$num = explode ("/",$final_number);
		$suf = explode ("/",$final_unit);

		$num = array_reverse ($num,TRUE);
		$suf = array_reverse ($suf, TRUE);

		for ($f = count($num) -1;$f >=0;$f--)	{
			$xvalue = $value/$num[$f];

			if ($xvalue > 1)	{
				$value = round($xvalue,2) . " " . $suf[$f];
				break;
			}
		}
	}
/*	else	{	// empty final operation
		// without round
	}
*/
	return ($value);


}

$ar_age = array ('hour' => 'Last Hour', 'day' => 'Last Day', 'week' => 'Last Week', 'month' => 'Last Month', 'year' => 'Last year');
$ar_topx = array ('5' => 'Top 5', '10' => 'Top 10', '20' => 'Top 20', '50' => 'Top 50', '0' => 'All');
$ar_sort = array ('asc' => 'Normal', 'desc' => 'Reverse');

/* if the user pushed the 'clear' button */
if (get_request_var('clear_x')) {
    unset($_SESSION['age']);
    unset($_SESSION['topx']);
    unset($_SESSION['sort']);
}


/*
$xar_ds = db_fetch_assoc ('SELECT distinct(t1.name) as dsname,t1.id as dsid, count(t1.id) as dscount FROM data_template AS t1 
			    LEFT JOIN data_template_data AS t2 ON t1.id=t2.data_template_id 
			    GROUP BY data_template_id'); 
*/

// nejprve podporovane
$ds_sup = db_fetch_assoc ('SELECT DISTINCT(CONCAT("supported - ",t1.name)) AS dsname, t1.id AS dsid, count(t1.id) AS dscount, "true" AS sup 
 FROM data_template AS t1
 JOIN data_template_data AS t2 ON t1.id=t2.data_template_id
 JOIN plugin_topx_source AS t3 on t1.hash = t3.hash
 GROUP BY data_template_id ORDER BY dsname');


$ds_unsup = db_fetch_assoc ('SELECT DISTINCT(CONCAT("unsupported - ",t1.name)) AS dsname, t1.id AS dsid, count(t1.id) AS dscount, "false" AS sup 
 FROM data_template AS t1
 JOIN data_template_data AS t2 ON t1.id=t2.data_template_id
 GROUP BY data_template_id ORDER BY dsname'
	);
	
	
$ds_all = array_merge ($ds_sup,$ds_unsup);

foreach ($ds_all as $ds)	{
	if ($ds['sup'] == 'true') {
		$tmp = -1 * $ds['dsid'];

	} else {
	   $tmp = $ds['dsid'];

	}
    $ar_ds[$tmp]['key']   = $tmp;
    $ar_ds[$tmp]['name']  = $ds['dsname'];
    $ar_ds[$tmp]['count'] = $ds['dscount'];
    $ar_ds[$tmp]['supported'] = $ds['sup'];
}


//var_dump($ar_ds);




if ( isset_request_var ('ds') && array_key_exists (get_request_var ('ds'), $ar_ds))	
    $_SESSION['ds'] = get_filter_request_var('ds');
if (!isset($_SESSION['ds']))	{
    $_SESSION['ds'] = key($ar_ds);
}

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
if (!isset($_SESSION['sort']))
	$_SESSION['sort'] = 'desc';


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
    
$id = $_SESSION['ds'];
if ($ar_ds[$_SESSION['ds']]['supported'] == 'true')
	$id *= -1;     
    
$query .= ' AS t2 ON t1.local_data_id = t2.local_data_id 
    WHERE t1.data_template_id = ' . $id .
    ' ORDER BY t2.average ';
    
$query .= $_SESSION['sort'] . ' ' ;

if ($_SESSION['topx'] > 0)    
    $query .= 'LIMIT ' . $_SESSION['topx'];

print '<br/><br/>SELECT ' . $columns . ' ' . $query . '<br/><br/>';


$graph = array();
$label = array();



if ($ar_ds[$_SESSION['ds']]['supported'] == 'true')	{

	$source = db_fetch_row_prepared('SELECT plugin_topx_source.* FROM plugin_topx_source JOIN data_template 
		ON plugin_topx_source.hash = data_template.hash  WHERE data_template.id = ?',
		array($id));
	
//	if (strpos($source['operation'],'=') !== false)	{	// only one value

		$avg = db_fetch_cell ('SELECT avg(average)' . $query);

		$pie_title = $source['unit'] . ' [ ' . $source['final_unit'] . ' ] ';

		$result = db_fetch_assoc("SELECT $columns $query");

		print '<table  class="topx_table">';
		print '<tr><th>Data source</th><th>Avg. value [' . $source['final_unit'] . ']</th><th>Peak [' . $source['final_unit'] . ']</th></tr>';

		$avg_count = 0;

		foreach ($result as $row)	{
    			$graph_id = db_fetch_cell ('SELECT DISTINCT(local_graph_id) FROM graph_templates_item
                                        LEFT JOIN data_template_rrd ON (graph_templates_item.task_item_id=data_template_rrd.id)
                                        LEFT JOIN data_local ON (data_template_rrd.local_data_id=data_local.id)
                                        LEFT JOIN data_template_data ON (data_local.id=data_template_data.local_data_id)
                                        WHERE data_template_data.local_data_id=' . $row['local_data_id']);

			if (strpos($source['operation'],'/') !== false)	{	// /number
				$val = (int) substr($source['operation'],strpos($source['operation'],'/')+1);
				$row['value'] = $row['value']/$val;
				$row['peak'] = $row['peak']/$val;

				if ($avg_count == 0)	{ // ugly, i know ... i need call this only one
					$avg = $avg/$val;
					$avg_count++;
				}
			}
			if (strpos($source['operation'],'+') !== false)	{	// x+y

			}
			else	{ // x=x
			
			}

	//		$row['value'] = first_operation ($row['value'],$source['operation']);

	    		array_push ($graph,$row['value']);
    			array_push ($label,$row['name']);



/*
			print '<tr><td><a href="' .  htmlspecialchars($config['url_path']) . 'graphs.php?action=graph_edit&id=' . $graph_id . '">' . $row['name'] . '</a></td>' .
				'<td>' . human_readable($row['value'],$source['system']) . '</td>' .
				'<td>' . human_readable($row['peak'],$source['system'])  . '</td>';

function final_operation ($value,$final_operation,$final_unit,$final_number) {

*/
			print '<tr><td><a href="' .  htmlspecialchars($config['url_path']) . 'graphs.php?action=graph_edit&id=' . $graph_id . '">' . $row['name'] . '</a></td>' .
				'<td>' . final_operation($row['value'],$source['final_operation'],$source['final_unit'],$source['final_number']) . '</td>' .
				'<td>' . final_operation($row['peak'],$source['final_operation'],$source['final_unit'],$source['final_number'])  . '</td>';


		}

		array_push($graph,$avg);
		array_push($label,'Average all');

	
		echo '<tr><td>Average all DS</td><td colspan="2">' . final_operation($avg,$source['final_operation'],$source['final_unit'],$source['final_number']) . '</td></tr>';
		print '</table>';
	
}
else	{	// unsupported

	print 'Unsupported = plain data without units only with decimal unit conversion';

	$avg = db_fetch_cell ('SELECT avg(average)' . $query);
		
	array_push($label,'Average all');
	array_push($graph,$avg);
	$pie_title = ''; 

	$result = db_fetch_assoc("SELECT $columns $query");

	print '<table  class="topx_table">';
	print '<tr><th>Data source</th><th>Avg. value</th><th>Peak</th></tr>';

	foreach ($result as $row)	{
    		$graph_id = db_fetch_cell ('SELECT DISTINCT(local_graph_id) FROM graph_templates_item
                                        LEFT JOIN data_template_rrd ON (graph_templates_item.task_item_id=data_template_rrd.id)
                                        LEFT JOIN data_local ON (data_template_rrd.local_data_id=data_local.id)
                                        LEFT JOIN data_template_data ON (data_local.id=data_template_data.local_data_id)
                                        WHERE data_template_data.local_data_id=' . $row['local_data_id']);

    		array_push ($graph,$row['value']);
    		array_push ($label,$row['name']);


		print '<tr><td><a href="' .  htmlspecialchars($config['url_path']) . 'graphs.php?action=graph_edit&id=' . $graph_id . '">' . $row['name'] . '</a></td>' .
			'<td>' . human_readable($row['value']) . '</td>' .
			'<td>' . human_readable($row['peak']) . '</td>';

	}
	echo '<tr><td>Average all DS</td><td colspan="2">' . human_readable($avg,'decimal') . '</td></tr>';

	print '</table>';

}

$xid = 'x' . uniqid();

print '<div class="topx_graph"><br/><br/><canvas id="pie_' . $xid . '" width="700" height="' . (20+$_SESSION['topx']*25 ). '"></canvas>';
print "<script type='text/javascript' src='js/chartjs-plugin-annotation.min.js'></script>";

print "<script type='text/javascript'>";

$pie_labels = implode('","',$label);
$pie_values = implode(',',$graph);
//$pie_title = 'todo :-)';


print <<<EOF
var $xid = document.getElementById("pie_$xid").getContext("2d");
new Chart($xid, {
    type: 'horizontalBar',
    data: {
        labels: ["$pie_labels"],
        datasets: [{
            backgroundColor: [ "#555555"],
            data: [$pie_values]
        }],
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
			    labelString: "$pie_title"
			}
		    }], 
		    },        
         
         
        tooltipTemplate: "<%= label %>",
      annotation: {
        annotations: [
          {
            type: "line",
            mode: "vertical",
            scaleID: "x-axis-0",
            value: $avg,
            borderColor: "red",
          }
        ]
      },

        
    },
});
EOF;

print "</script></div>";
// end of graph	     



print '<br/><br/>';
print 'DS stats last major run time: ' .  read_config_option('dsstats_last_major_run_time') . '<br/>';    
print 'DS stats last daily run time: ' .  read_config_option('dsstats_last_daily_run_time') . '<br/>';    


bottom_footer();
