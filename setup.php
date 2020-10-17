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

function plugin_topx_install ()	{
//	api_plugin_register_hook('topx', 'poller_output', 'topx_poller_output', 'setup.php');
//	api_plugin_register_hook('topx', 'poller_bottom', 'topx_poller_bottom', 'setup.php');
	api_plugin_register_hook('topx', 'top_header_tabs', 'topx_show_tab', 'setup.php');
	api_plugin_register_hook('topx', 'top_graph_header_tabs', 'topx_show_tab', 'setup.php');
	api_plugin_register_realm('topx', 'topx.php,', 'Plugin topx - view', 1);

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
	$data['columns'][] = array('name' => 'final_number', 'type' => 'varchar(100)', 'NULL' => false, 'default' => '');
	$data['columns'][] = array('name' => 'system', 'type' => 'varchar(20)', 'NULL' => false, 'default' => 'bits');  
	// decimal=1000, bits=1024

//	$data['primary'] = 'hash';
	$data['type'] = 'InnoDB';
	$data['comment'] = 'sources';
	api_plugin_db_table_create ('topx', 'plugin_topx_source', $data);

	db_execute ("INSERT INTO plugin_topx_source (sorting,dt_name,hash,operation,unit,final_operation,final_unit,final_number,system) values 
		('desc','ucd/net - Load Average - 1 Minute','9b82d44eb563027659683765f92c9757','load_1min=load_1min','Load','strip','load','2','decimal')");
	db_execute ("INSERT INTO plugin_topx_source (sorting,dt_name,hash,operation,unit,final_operation,final_unit,final_number,system) values 
		('desc','Host MIB - CPU Utilization','f6e7d21c19434666bbdac00ccef9932f','cpu=cpu','Utilization','strip','%','1','decimal')");
	db_execute ("INSERT INTO plugin_topx_source (sorting,dt_name,hash,operation,unit,final_operation,final_unit,final_number,system) values 
		('desc','Host MIB - Hard Drive Space','d814fa3b79bd0f8933b6e0834d3f16d0','hdd_total%hdd_used','Used','strip','%','1','decimal')");
	db_execute ("INSERT INTO plugin_topx_source (sorting,dt_name,hash,operation,unit,final_operation,final_unit,final_number,system) values 
		('desc','Interface - Errors_in + Discards_in','36335cd98633963a575b70639cd2fdad','discards_in+errors_in','Errors+Discard','/','errors/Kerrors/Merrors','1/1000/1000000','decimal')");
	db_execute ("INSERT INTO plugin_topx_source (sorting,dt_name,hash,operation,unit,final_operation,final_unit,final_number,system) values 
		('desc','Interface - Errors_out + Discards_out','36335cd98633963a575b70639cd2fdad','discards_out+errors_out','Errors+Discard','/','errors/Kerrors/Merrors','1/1000/1000000','decimal')");
	db_execute ("INSERT INTO plugin_topx_source (sorting,dt_name,hash,operation,unit,final_operation,final_unit,final_number,system) values 
		('desc','Mikrotik - System - Uptime','0d8804fbc44a6ab9db89f8d83d050627','uptime/100','Uptime in seconds','/','sec/min/hour/days','1/60/3600/86400','decimal')");
	db_execute ("INSERT INTO plugin_topx_source (sorting,dt_name,hash,operation,unit,final_operation,final_unit,final_number,system) values 
		('desc','APC load','be63bc4946561a274ace2c982548f255','load=load','Load','strip','%','0','decimal')");
	db_execute ("INSERT INTO plugin_topx_source (sorting,dt_name,hash,operation,unit,final_operation,final_unit,final_number,system) values 
		('desc','MultiCPU AVG','9561518147a2423734394410bcd241b4','load=load','Utilization','strip','%','0','decimal')");
	db_execute ("INSERT INTO plugin_topx_source (sorting,dt_name,hash,operation,unit,final_operation,final_unit,final_number,system) values 
		('desc','Interface - Traffic_in+Traffic_out','6632e1e0b58a565c135d7ff90440c335','traffic_in+traffic_out','Bits','/','bit/Kbit/Mbit/Gbit/Tbit','1/1024/1048576/1073741824/1099511627776','bits')");
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
	$data['type'] = 'InnoDB';
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


function topx_show_tab () {
	global $config;
	if (api_user_realm_auth('topx.php')) {
		$cp = false;
		if (basename($_SERVER['PHP_SELF']) == 'topx.php')
		    $cp = true;
		print '<a href="' . $config['url_path'] . 'plugins/topx/topx.php"><img src="' . $config['url_path'] . 'plugins/topx/images/tab_topx' . ($cp ? '_down': '') . '.gif" alt="topx" align="absmiddle" border="0"></a>';
	}
}


?>