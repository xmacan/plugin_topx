<?php

function plugin_topx_install ()	{
// old version    api_plugin_register_hook('topx', 'poller_output', 'topx_poller_output', 'setup.php');
// old version   api_plugin_register_hook('topx', 'poller_bottom', 'topx_poller_bottom', 'setup.php');
    api_plugin_register_hook('topx', 'top_header_tabs', 'topx_show_tab', 'setup.php');
    api_plugin_register_hook('topx', 'top_graph_header_tabs', 'topx_show_tab', 'setup.php');
    api_plugin_register_realm('topx', 'topx.php,', 'Plugin topx - view', 1);

    topx_setup_database();
}

function topx_setup_database()	{

        if (sizeof(db_fetch_assoc("SHOW TABLES LIKE 'plugin_topx_source'")) > 0 )	{
                db_execute("DROP TABLE `plugin_topx_source`");
        }
        if (sizeof(db_fetch_assoc("SHOW TABLES LIKE 'plugin_topx_average'")) >0 )	{
                db_execute("DROP TABLE `plugin_topx_average`");
        }
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

