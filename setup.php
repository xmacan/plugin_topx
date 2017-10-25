<?php

function plugin_topx_install ()	{
    api_plugin_register_hook('topx', 'poller_output', 'topx_poller_output', 'setup.php');
    api_plugin_register_hook('topx', 'poller_bottom', 'topx_poller_bottom', 'setup.php');
    api_plugin_register_hook('topx', 'top_header_tabs', 'topx_show_tab', 'include/tab.php');
    api_plugin_register_hook('topx', 'top_graph_header_tabs', 'topx_show_tab', 'include/tab.php');

    // muze zapinat topx adminovi a davam moznost ho pridavat ostatnim
    api_plugin_register_realm('topx', 'topx.php,', 'Plugin TopX - view', 1);

    topx_setup_database();
}



function topx_setup_database()	{

    $data = array();
    $data['columns'][] = array('name' => 'sorting', 'type' => "enum('asc','desc')", 'default' => 'asc', 'NULL' => false);
    $data['columns'][] = array('name' => 'dt_name', 'type' => 'varchar(200)', 'NULL' => false, 'default' => '');
    $data['columns'][] = array('name' => 'hash', 'type' => 'varchar(32)', 'NULL' => false, 'default' => '');
    $data['columns'][] = array('name' => 'operation', 'type' => 'varchar(100)', 'NULL' => false, 'default' => '');
    $data['columns'][] = array('name' => 'unit', 'type' => 'varchar(100)', 'NULL' => false, 'default' => '');
    $data['columns'][] = array('name' => 'final_operation', 'type' => 'varchar(100)', 'NULL' => false, 'default' => '');
    $data['columns'][] = array('name' => 'final_unit', 'type' => 'varchar(100)', 'NULL' => false, 'default' => '');
    $data['primary'] = 'hash';
    $data['type'] = 'MyISAM';
    $data['comment'] = 'sources';
    api_plugin_db_table_create ('topx', 'plugin_topx_source', $data);

    db_execute ("INSERT INTO plugin_topx_source (sorting,dt_name,hash,operation,unit,final_operation,final_unit) values ('desc','ucd/net - Load Average - 1 Minute','9b82d44eb563027659683765f92c9757','load_1min=load_1min','load','strip','')");
    db_execute ("INSERT INTO plugin_topx_source (sorting,dt_name,hash,operation,unit,final_operation,final_unit) values ('desc','Host MIB - CPU Utilization','f6e7d21c19434666bbdac00ccef9932f','cpu=cpu','%','','')");
    db_execute ("INSERT INTO plugin_topx_source (sorting,dt_name,hash,operation,unit,final_operation,final_unit) values ('desc','Host MIB - Hard Drive Space','d814fa3b79bd0f8933b6e0834d3f16d0','hdd_total%hdd_used','%','','')");
    db_execute ("INSERT INTO plugin_topx_source (sorting,dt_name,hash,operation,unit,final_operation,final_unit) values ('desc','Interface - Errors/Discards','36335cd98633963a575b70639cd2fdad','discards_in+errors_in','errors+discards (in)','','')");
    db_execute ("INSERT INTO plugin_topx_source (sorting,dt_name,hash,operation,unit,final_operation,final_unit) values ('desc','Mikrotik - System - Uptime','0d8804fbc44a6ab9db89f8d83d050627','uptime/100','seconds','/86400','days')");
    db_execute ("INSERT INTO plugin_topx_source (sorting,dt_name,hash,operation,unit,final_operation,final_unit) values ('desc','APC load','be63bc4946561a274ace2c982548f255','load=load','load','','')");
    db_execute ("INSERT INTO plugin_topx_source (sorting,dt_name,hash,operation,unit,final_operation,final_unit) values ('desc','MultiCPU AVG','9561518147a2423734394410bcd241b4','load=load','%','','')");
    db_execute ("INSERT INTO plugin_topx_source (sorting,dt_name,hash,operation,unit,final_operation,final_unit) values ('desc','Interface - traffic','6632e1e0b58a565c135d7ff90440c335','traffic_in+traffic_out','bits','/1024','kbit')");
    // MARK1


    $data = array();
    $data['columns'][] = array('name' => 'age', 'type' => "enum('quarter','hour','day','week','month')", 'default' => 'quarter', 'NULL' => false);
    $data['columns'][] = array('name' => 'value1', 'type' => 'decimal(20,6)', 'NULL' => false, 'default' => '0');
    $data['columns'][] = array('name' => 'value2', 'type' => 'decimal(20,6)', 'NULL' => false, 'default' => '0');
    $data['columns'][] = array('name' => 'value1_dsname', 'type' => 'varchar(50)', 'NULL' => false, 'default' => '');
    $data['columns'][] = array('name' => 'value2_dsname', 'type' => 'varchar(50)', 'NULL' => false, 'default' => '');
    $data['columns'][] = array('name' => 'result_value', 'type' => 'decimal(20,6)', 'NULL' => false, 'default' => '0');
    $data['columns'][] = array('name' => 'local_data_id', 'type' => 'int(11)', 'NULL' => true, 'default' => NULL);
    $data['columns'][] = array('name' => 'data_template_id', 'type' => 'int(11)', 'NULL' => true, 'default' => NULL);
    $data['columns'][] = array('name' => 'number_of_cycles', 'type' => 'int(11)', 'NULL' => false, 'default' => '0');

    $data['type'] = 'MyISAM';
    $data['comment'] = 'average';
    api_plugin_db_table_create ('topx', 'plugin_topx_average', $data);


    db_execute ("ALTER TABLE plugin_topx_average add index (local_data_id)");
    db_execute ("ALTER TABLE plugin_topx_average add index (data_template_id)");
    db_execute ("ALTER TABLE plugin_topx_average add index (value1_dsname)");
    db_execute ("ALTER TABLE plugin_topx_average add index (value2_dsname)");
}


function plugin_topx_uninstall ()	{

        if (sizeof(db_fetch_assoc("SHOW TABLES LIKE 'plugin_topx_source'")) > 0 )	{
                db_execute("DROP TABLE `plugin_topx_source`");
        }
        if (sizeof(db_fetch_assoc("SHOW TABLES LIKE 'plugin_topx_average'")) >0 )	{
                db_execute("DROP TABLE `plugin_topx_average`");
        }
}


function plugin_topx_version()	{
    global $config;
    $info = parse_ini_file($config['base_path'] . '/plugins/topx/INFO', true);
    return $info['info'];
}



function plugin_topx_check_config () {
	return true;
}


function topx_poller_output ($rrd_update_array)	{
    global $config;
	
	$period = read_config_option("poller_interval");
    
	$logging = read_config_option("log_verbosity");

	if($logging >= POLLER_VERBOSITY_DEBUG) 
	    cacti_log("TopX poller_output: STARTING\n",true,"TopX");


	$in = "";
	// zjistuju, ktera data budu zkoumat
	$result = db_fetch_assoc ("select distinct (data_template.id) as id from plugin_topx_source join data_template  on data_template.hash = plugin_topx_source.hash");

        foreach($result as $row)        {
    	    $in .= $row["id"] . ",";
	}
	$in = substr($in,0,strlen($in)-1);
	    
	$requiredlist = db_fetch_assoc("select DISTINCT t2.data_source_name, t1.data_source_path, 
	t2.local_data_id, t2.data_source_type_id, t2.data_template_id 
	from data_template_data as t1, data_template_rrd as t2 
	where (t1.local_data_id = t2.local_data_id and t1.data_template_id = t2.data_template_id) and  t1.data_template_id in ($in)"); 

	$path_rra = $config["rra_path"];
	
	# code from weathermap plugin
	# especially on Windows, it seems that filenames are not reliable (sometimes \ and sometimes / even though path_rra is always /) .
	# let''s make an index from local_data_id to filename, and then use local_data_id as the key...
	
	foreach (array_keys($rrd_update_array) as $key)		{
	    if(isset( $rrd_update_array[$key]['times']) && is_array($rrd_update_array[$key]['times']) )	{
		if($logging >= POLLER_VERBOSITY_DEBUG) cacti_log("TopX poller_output: Adding $key",true,"TopX");
			$knownfiles[$rrd_update_array[$key]["local_data_id"] ] = $key;
	    }
	}

	foreach ($requiredlist as $required)	{
	    $file = str_replace("<path_rra>", $path_rra, $required['data_source_path']);
	    $dsname = $required['data_source_name'];
	    $local_data_id = $required['local_data_id'];
		
	    if(isset($knownfiles[$local_data_id]))	{
		$file2 = $knownfiles[$local_data_id];			
		if($file2 != '') $file = $file2;
	    }
				
	    if($logging >= POLLER_VERBOSITY_DEBUG) cacti_log("TopX poller_output: Looking for $file ($local_data_id) (".$required['data_source_path'].")\n",true,"TopX");
		
    	    if( isset($rrd_update_array[$file]) && is_array($rrd_update_array[$file]) && isset($rrd_update_array[$file]['times']) && is_array($rrd_update_array[$file]['times']) && isset( $rrd_update_array{$file}['times'][key($rrd_update_array[$file]['times'])]{$dsname} ))  	{

		$value = $rrd_update_array{$file}['times'][key($rrd_update_array[$file]['times'])]{$dsname};
		$time = key($rrd_update_array[$file]['times']);
		if (read_config_option("log_verbosity") >= POLLER_VERBOSITY_DEBUG) 
		    cacti_log("TopX poller_output: Got one ds! $file:$dsname -> $time $value\n",true,"TopX");
			
		// tady ostatni berou last_time, kterou maji ulozenou. Ja takovou hodnotu nemam a proto ji beru z polleru
		// coz by asi melo byt to same, pokud se kaktus nerestartuje		
		// $period = $time - $required['last_time'];
		// zjistuju si to nahore

	// nadava to obcas do logu, ze last_value nezna	
		if (isset ($required['last_value']))	{
			$lastval = $required['last_value'];
		}
		else	{
			$lastval = 0;
			$required['last_time'] = 0;
		}
			
		// if the new value is a NaN, we'll give 0 instead, and pretend it didn't happen from the point
		// of view of the counter etc. That way, we don't get those enormous spikes. Still doesn't deal with
		// reboots very well, but it should improve it for drops.
		if($value == 'U')	{
		    $newvalue = 0;
		    $newlastvalue = $lastval;
		    $newtime = $required['last_time'];
		}
		else	{
		    $newlastvalue = $value;
		    $newtime = $time;
				
		    switch($required['data_source_type_id'])	{
			case 1: //GAUGE
			    $newvalue = $value;
			break;
			case 2: //COUNTER
			    if ($value >= $lastval) {
				// Everything is normal
				$newvalue = $value - $lastval;
			    } 
			    else {
				// Possible overflow, see if its 32bit or 64bit
				if ($lastval > 4294967295) {
				    $newvalue = (18446744073709551615 - $lastval) + $value;
				} 
				else {
				    $newvalue = (4294967295 - $lastval) + $value;
				}
			    }	

			    $newvalue = $newvalue / $period;
			break;
					
			case 3: //DERIVE
			    $newvalue = ($value-$lastval) / $period;
			break;
				
			case 4: //ABSOLUTE
			    $newvalue = $value / $period;
			break;
					
			default: // do something somewhat sensible in case something odd happens
			    $newvalue = $value;
			break;
		    }
		}

		// debug for one DS
		// if ($required['local_data_id'] == 7057)
		//    cacti_log("TopX xdebug2: value is $newvalue" ,true,"TopX");

		topx_average($newtime,$newvalue,$required['local_data_id'],$dsname,$required['data_template_id']);

        	if($logging >= POLLER_VERBOSITY_DEBUG) 
	        	cacti_log("TopX poller_output: (time, value, local_data_id,ds_name, template_id)  VALUES ($newtime,'$newvalue','" . $required['local_data_id'] . "','$dsname','" . $required['data_template_id'] .") \n",true,"TopX");
		}
	    }

	if($logging >= POLLER_VERBOSITY_DEBUG) cacti_log("TopX poller_output: ENDING\n",true,"TopX");
	
	return $rrd_update_array;
}


function topx_poller_bottom () {
    global $config;
	
    //list($micro,$seconds) = split(" ", microtime());
    list($micro,$seconds) = explode(" ", microtime());
    $start = $seconds + $micro;

    $now = time();

    $poller_interval = read_config_option("poller_interval");
    
    $result = db_fetch_assoc ("SELECT * from plugin_topx_average");
    if ($result)        {
        foreach ($result as $item)  {
    	    switch ($item['age'])	{
    	        case 'quarter':
		    $cycle_required = $poller_interval == 300 ? 3 : 15;
    		break;
    		case 'hour':
		    $cycle_required = $poller_interval == 300 ? 12 : 60;
    		break;
    		case 'day':
		    $cycle_required = $poller_interval == 300 ? 288 : 1440;
    		break;
    		case 'week':
		    $cycle_required = $poller_interval == 300 ? 2016 : 10080;
    		break;
    		case 'month':
		    $cycle_required = $poller_interval == 300 ? 8640 : 43200;
    		break;
    	    }

            $cycle_real = $item['number_of_cycles'] < $cycle_required ? $item['number_of_cycles'] : $cycle_required;            
	    $operation = db_fetch_cell ("select operation from data_template_data left join data_template on data_template.id = data_template_data.data_template_id 
					    left join plugin_topx_source on plugin_topx_source.hash=data_template.hash where local_data_id=" . $item['local_data_id']); 
    	    switch ($operation)	{

                case "cpu=cpu":
		case "load_1min=load_1min":
		case "load=load":	// apc load, multiCPU avg
            	    $avg_value = (($cycle_real*$item['result_value'])+$item['value1'])/($cycle_real+1);
		break;

		case "uptime/100":	// mikrotik
		    $avg_value = (($cycle_real*$item['result_value'])+$item['value1'])/($cycle_real+1);
		break;

                case "hdd_total%hdd_used":
			if ($item['value1'] == 0)	
				$act_percent = 0;
			else
		    		$act_percent = ($item['value2']/$item['value1']) * 100 ;
            	    $avg_value = (($cycle_real*$item['result_value'])+$act_percent)/($cycle_real+1);
	    	break;
		case "discards_in+errors_in":
		    $act_value = $item['value1'] + $item['value2'];
            	    $avg_value = (($cycle_real*$item['result_value'])+$act_value)/($cycle_real+1);
		break;
		case "traffic_in+traffic_out":
		    $act_value = $item['value1'] + $item['value2'];
            	    $avg_value = (($cycle_real*$item['result_value'])+$act_value)/($cycle_real+1);
		break;
		// MARK2
		

	    }
            db_execute("UPDATE plugin_topx_average set result_value=$avg_value, number_of_cycles=number_of_cycles+1 where local_data_id=" . $item['local_data_id'] ." and age='" . $item['age'] . "'");
	}
    }
    $dt_count = db_fetch_cell("select count(distinct data_template_id) from plugin_topx_average");
    $ds_count = db_fetch_cell("select count(distinct local_data_id) from plugin_topx_average");
    $cycles = db_fetch_cell("select max(number_of_cycles) from plugin_topx_average");

    /* record the end time */
// tady bylo split (deprecated) 
    list($micro,$seconds) = explode(" ", microtime());
    $end = $seconds + $micro;
         
    /* log statistics */
    $topx_stats = sprintf("Time:%01.4f DT:%s DS:%s CYCLES: %s", $end - $start, $dt_count, $ds_count, $cycles);
    cacti_log('TOPX STATS: ' . $topx_stats, false, 'SYSTEM');
}



function topx_show_tab () {
	global $config;
	if (api_user_realm_auth('topx.php')) {
		$cp = false;
		if (basename($_SERVER['PHP_SELF']) == 'topx.php')
		$cp = true;
		print '<a href="' . $config['url_path'] . 'plugins/topx/topx.php"><img src="' . $config['url_path'] . 'plugins/topx/images/tab_topx' . ($cp ? '_down': '') . '.gif" alt="topx" align="absmiddle" border="0"></a>';
	}
}





function topx_average ($time, $value, $local_data_id, $dsname, $data_template_id)	{

//	!!!! ugly - the same array is in topx.php
    $ar_age = array ("quarter" => "15 minutes", "hour" => "Last Hour", "day" => "Last Day", "week" => "Last Week", "month" => "Last Month");

    if (!is_null($value))	{

	$operation = db_fetch_cell ("select operation from data_template_data left join data_template on data_template.id = data_template_data.data_template_id 
                                     left join plugin_topx_source on plugin_topx_source.hash=data_template.hash where local_data_id=" . $local_data_id);

	$result = db_fetch_assoc ("SELECT * from plugin_topx_average where local_data_id=$local_data_id");

	if ($result)        {
	
    	    foreach ($result as $item) 	{
		    $query = NULL;
        	switch ($operation) {
            	    case "cpu=cpu":
		    case "load_1min=load_1min":
		    case "load=load":	// apc load, multiCPU avg
			$query = "UPDATE plugin_topx_average set value1=$value where local_data_id='$local_data_id' and age='" . $item['age'] . "'";
		    break;

		    case "uptime/100":	// mikrotik
			$query = "UPDATE plugin_topx_average set value1=" . ($value/100) . " where local_data_id='$local_data_id' and age='" . $item['age'] . "'";
		    break;
                
            	    case "hdd_total%hdd_used":
			if ($dsname == "hdd_total")	                        
			    $query = "UPDATE plugin_topx_average set value1=$value,value1_dsname='$dsname' where local_data_id='$local_data_id' and age='" . $item['age'] . "'";		    
			if ($dsname == "hdd_used")	                        
	    		    $query = "UPDATE plugin_topx_average set value2=$value,value2_dsname='$dsname' where local_data_id='$local_data_id' and age='" . $item['age'] . "'";		    
            	    break;
            	    
		    case "discards_in+errors_in":
			if ($dsname == "discards_in")	                        
			    $query = "UPDATE plugin_topx_average set value1=$value,value1_dsname='$dsname' where local_data_id='$local_data_id' and age='" . $item['age'] . "'";		    	
			if ($dsname == "errors_in")	                        
	    		    $query = "UPDATE plugin_topx_average set value2=$value,value2_dsname='$dsname' where local_data_id='$local_data_id' and age='" . $item['age'] . "'";		    
            	    break;
		    case "traffic_in+traffic_out":
			if ($dsname == "traffic_in")	                        
			    $query = "UPDATE plugin_topx_average set value1=$value,value1_dsname='$dsname' where local_data_id='$local_data_id' and age='" . $item['age'] . "'";		    	
			if ($dsname == "traffic_out")	                        
	    		    $query = "UPDATE plugin_topx_average set value2=$value,value2_dsname='$dsname' where local_data_id='$local_data_id' and age='" . $item['age'] . "'";		    
		    break;
		    
            	    // MARK3
            	    
		}
		if ($query)
			db_execute ($query);
    	    }
    	    
	}
	else	{	// first value

		$query = NULL;
            switch ($operation) {
                case "cpu=cpu":
		case "load_1min=load_1min":
		case "load=load":	// apc load, multiCPU avg
		    //if ($dsname == "cpu")	
		    $query = "insert into plugin_topx_average (age,value1,local_data_id,value1_dsname,data_template_id) VALUES ('xxxage',$value,$local_data_id,'$dsname',$data_template_id)";
		break;
	
		case "uptime/100":	// mikrotik
		    $query = "insert into plugin_topx_average (age,value1,local_data_id,value1_dsname,data_template_id) VALUES ('xxxage'," . ($value/100) . ",$local_data_id,'$dsname',$data_template_id)";		
		break;
	
	
                break;
                case "hdd_total%hdd_used":
		    if ($dsname == "hdd_total")	{	                        
		    	$query = "insert into plugin_topx_average (age,value1,local_data_id,value1_dsname,data_template_id) VALUES ('xxxage',$value,$local_data_id,'$dsname',$data_template_id)";
		    }
		    if ($dsname == "hdd_used")	 {
		    	$query = "insert into plugin_topx_average (age,value2,local_data_id,value2_dsname,data_template_id) VALUES ('xxxage',$value,$local_data_id,'$dsname',$data_template_id)";
		    }
                break;
                case "discards_in+errors_in":
		    if ($dsname == "discards_in")	{	                        
		    	$query = "insert into plugin_topx_average (age,value1,local_data_id,value1_dsname,data_template_id) VALUES ('xxxage',$value,$local_data_id,'$dsname',$data_template_id)";
		    }
		    if ($dsname == "errors_in")	 {
		    	$query = "insert into plugin_topx_average (age,value2,local_data_id,value2_dsname,data_template_id) VALUES ('xxxage',$value,$local_data_id,'$dsname',$data_template_id)";
		    }
                break;
                case "traffic_in+traffic_out":
		    if ($dsname == "traffic_in")	{	                        
		    	$query = "insert into plugin_topx_average (age,value1,local_data_id,value1_dsname,data_template_id) VALUES ('xxxage',$value,$local_data_id,'$dsname',$data_template_id)";
		    }
		    if ($dsname == "traffic_out")	 {
		    	$query = "insert into plugin_topx_average (age,value2,local_data_id,value2_dsname,data_template_id) VALUES ('xxxage',$value,$local_data_id,'$dsname',$data_template_id)";
		    }
                break;
		// MARK4
	    }

	    if ($query)	{
    		foreach ($ar_age as $key=>$value)       {
		    $nquery = str_replace ("xxxage", $key, $query);
		    db_execute($nquery);
		}
	    }
	}
    } // values isn't null
}

?>