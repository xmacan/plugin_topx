<?php

function topx_show_tab () {
	global $config;
	
	if (api_user_realm_auth('topx.php') && isset($_SESSION['sess_user_id'])) {
//		$lopts = db_fetch_cell_prepared('SELECT topx_opts FROM user_auth WHERE id=?',array($_SESSION['sess_user_id']));  		
//		if ($lopts == 1)	{
			$cp = false;
			if (basename($_SERVER['PHP_SELF']) == 'topx.php')
				$cp = true;
			
			print('<a href="' . $config['url_path'] . 'plugins/topx/topx.php"><img src="' . $config['url_path'] . 'plugins/topx/images/tab_topx' . ($cp ? '_down': '') . '.gif" alt="topx"  align="absmiddle" border="0"></a>');
//		}
	}
}

?>
