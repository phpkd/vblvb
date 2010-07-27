<?php
/*==================================================================================*\
|| ################################################################################ ||
|| # Product Name: vB Link Verifier Bot 'Ultimate'               Version: 4.0.137 # ||
|| # License Type: Commercial License                            $Revision$ # ||
|| # ---------------------------------------------------------------------------- # ||
|| # 																			  # ||
|| #            Copyright ©2005-2010 PHP KingDom. All Rights Reserved.            # ||
|| #      This product may not be redistributed in whole or significant part.     # ||
|| # 																			  # ||
|| # ---------- "vB Link Verifier Bot 'Ultimate'" IS NOT FREE SOFTWARE ---------- # ||
|| #     http://www.phpkd.net | http://info.phpkd.net/en/license/commercial       # ||
|| ################################################################################ ||
\*==================================================================================*/


// ######################## SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE & ~8192);
if (!is_object($vbulletin->db))
{
	exit;
}

// Bypass PHP INI memory limit!
if (($current_memory_limit = ini_size_to_bytes(@ini_get('memory_limit'))) < 128 * 1024 * 1024 AND $current_memory_limit > 0)
{
	@ini_set('memory_limit', 128 * 1024 * 1024);
}
@set_time_limit(0);


// ########################################################################
// ######################### START MAIN SCRIPT ############################
// ########################################################################


$log = '';
if ($vbulletin->options['phpkd_vblvb_active'])
{
	require_once(DIR . '/includes/phpkd/vblvb/functions.php');

	if (!defined('IN_CONTROL_PANEL'))
	{
		global $vbphrase;
	}

	$error_type = (defined('IN_CONTROL_PANEL') ? ERRTYPE_ECHO : ERRTYPE_SILENT);
	require_once(DIR . '/includes/phpkd/vblvb/class_core.php');
	$phpkd_vblvb = new PHPKD_VBLVB($vbulletin, array('vbphrase' => $vbphrase), $error_type);


	// Check license validity
	switch ($phpkd_vblvb->verify_license())
	{
		case 'valid':
			// Do nothing, our license is valid; So proceed.
			break;

		// Paid product with trail key
		case 'VBLVB':
			if (defined('IN_CONTROL_PANEL'))
			{
				print_cp_message('<span class="diff-deleted">Sorry, you can\'t use a <strong>TRAIL</strong> license key with a <strong>PAID</strong> product. <strong>TRAIL license keys can run only with TRAIL products.</strong><br />Please get your paid license key from <a href="http://eshop.phpkd.net/customers/index.php" target="_blank">Customer Area</a> or contact support at <a href="http://www.phpkd.net" target="_blank">www.phpkd.net</a> for assistance!!</span>');
			}
			else
			{
				phpkd_vblvb_cron_kill('<span class="diff-deleted">Sorry, you can\'t use a <strong>TRAIL</strong> license key with a <strong>PAID</strong> product. <strong>TRAIL license keys can run only with TRAIL products.</strong><br />Please get your paid license key from <a href="http://eshop.phpkd.net/customers/index.php" target="_blank">Customer Area</a> or contact support at <a href="http://www.phpkd.net" target="_blank">www.phpkd.net</a> for assistance!!</span>', $nextitem);
			}
			break;

		// Trail product with paid key
		case 'TRAIL':
			if (defined('IN_CONTROL_PANEL'))
			{
				print_cp_message('<span class="diff-deleted">Sorry, you can\'t use a <strong>PAID</strong> license key with a <strong>TRAIL</strong> product. <strong>PAID license keys can run only with PAID products.</strong><br />Please get your trail license key from <a href="http://eshop.phpkd.net/customers/index.php" target="_blank">Customer Area</a> or contact support at <a href="http://www.phpkd.net" target="_blank">www.phpkd.net</a> for assistance!!</span>');
			}
			else
			{
				phpkd_vblvb_cron_kill('<span class="diff-deleted">Sorry, you can\'t use a <strong>PAID</strong> license key with a <strong>TRAIL</strong> product. <strong>PAID license keys can run only with PAID products.</strong><br />Please get your trail license key from <a href="http://eshop.phpkd.net/customers/index.php" target="_blank">Customer Area</a> or contact support at <a href="http://www.phpkd.net" target="_blank">www.phpkd.net</a> for assistance!!</span>', $nextitem);
			}
			break;

		// Invalid key
		case 'invalid':
		default:
			if (defined('IN_CONTROL_PANEL'))
			{
				print_cp_message('<span class="diff-deleted">Sorry, this isn\'t a valid license. Please contact support at <a href="http://www.phpkd.net" target="_blank">www.phpkd.net</a> for a valid license!!</span>');
			}
			else
			{
				phpkd_vblvb_cron_kill('<span class="diff-deleted">Sorry, this isn\'t a valid license. Please contact support at <a href="http://www.phpkd.net" target="_blank">www.phpkd.net</a> for a valid license!!</span>', $nextitem);
			}
			break;
	}


	if (!$vbulletin->options['phpkd_vblvb_checked_existingposts'])
	{
		if (defined('IN_CONTROL_PANEL'))
		{
			print_stop_message('phpkd_vblvb_existing_notchecked');
		}
		else
		{
			phpkd_vblvb_cron_kill($vbphrase['phpkd_vblvb_existing_notchecked'], $nextitem);
		}
	}


	// Required Initialization
	$phpkd_vblvb->initialize(array('hosts' => TRUE, 'protocols' => TRUE, 'bbcodes' => TRUE, 'threadmodes' => TRUE, 'postmodes' => TRUE));
	if (!is_array($phpkd_vblvb->hosts) OR empty($phpkd_vblvb->hosts))
	{
		if (defined('IN_CONTROL_PANEL'))
		{
			print_stop_message('phpkd_vblvb_invalid_hosts');
		}
		else
		{
			phpkd_vblvb_cron_kill($vbphrase['phpkd_vblvb_invalid_hosts'], $nextitem);
		}
	}

	if (!is_array($phpkd_vblvb->protocols) OR empty($phpkd_vblvb->protocols))
	{
		if (defined('IN_CONTROL_PANEL'))
		{
			print_stop_message('phpkd_vblvb_invalid_protocols');
		}
		else
		{
			phpkd_vblvb_cron_kill($vbphrase['phpkd_vblvb_invalid_protocols'], $nextitem);
		}
	}

	if (!is_array($phpkd_vblvb->bbcodes) OR empty($phpkd_vblvb->bbcodes))
	{
		if (defined('IN_CONTROL_PANEL'))
		{
			print_stop_message('phpkd_vblvb_invalid_bbcodes');
		}
		else
		{
			phpkd_vblvb_cron_kill($vbphrase['phpkd_vblvb_invalid_bbcodes'], $nextitem);
		}
	}


	if (is_array($phpkd_vblvb->threadmodes) AND !empty($phpkd_vblvb->threadmodes))
	{
		$rawthreadmodes = array();
		foreach ($phpkd_vblvb->threadmodes AS $threadmode)
		{
			switch ($threadmode)
			{
				case 'OPENED':
					$rawthreadmodes[] = 'thread.open != 1';
					break;
				case 'CLOSED':
					$rawthreadmodes[] = 'thread.open != 0';
					break;
				case 'MODERATED':
					$rawthreadmodes[] = 'thread.visible != 0';
					break;
				case 'STICKY':
					$rawthreadmodes[] = 'thread.sticky != 1';
					break;
				case 'DELETED':
					$rawthreadmodes[] = 'thread.visible != 2';
					break;
				case 'REDIRECTED':
					$rawthreadmodes[] = 'thread.open != 10';
					break;
			}
		}
	}


	if (is_array($phpkd_vblvb->postmodes) AND !empty($phpkd_vblvb->postmodes))
	{
		$rawpostmodes = array();
		foreach ($phpkd_vblvb->postmodes AS $postmode)
		{
			switch ($postmode)
			{
				case 'OPENED':
					$rawpostmodes[] = 'post.visible != 1';
					break;
				case 'MODERATED':
					$rawpostmodes[] = 'post.visible != 0';
					break;
				case 'DELETED':
					$rawpostmodes[] = 'post.visible != 2';
					break;
			}
		}
	}


	$inex_users = $inex_usergroups = $inex_forums = $cutoff = $threadmodes = $postmodes = '';

	// Auto exclude report forums/threads & recycle bin forum from being checked: http://forum.phpkd.net/project.php?issueid=71
	$forced_inex_threads = array($vbulletin->options['phpkd_vblvb_report_tid']);
	$forced_inex_forums = array($vbulletin->options['phpkd_vblvb_report_fid'], $vbulletin->options['phpkd_vblvb_punish_fid']);


	if (is_array($rawthreadmodes) AND !empty($rawthreadmodes))
	{
		$threadmodes = 'AND ' . implode(' AND ', $rawthreadmodes);
	}


	if (is_array($rawpostmodes) AND !empty($rawpostmodes))
	{
		$postmodes = 'AND ' . implode(' AND ', $rawpostmodes);
	}


	switch ($vbulletin->options['phpkd_vblvb_inex_users'])
	{
		case 1:
			$inex_users = ((strlen(trim($vbulletin->options['phpkd_vblvb_inex_users_ids'])) > 0) ? 'AND post.userid IN (' . $vbulletin->db->escape_string(trim($vbulletin->options['phpkd_vblvb_inex_users_ids'])) . ')' : '');
			break;
		case 2:
			$inex_users = ((strlen(trim($vbulletin->options['phpkd_vblvb_inex_users_ids'])) > 0) ? 'AND post.userid NOT IN (' . $vbulletin->db->escape_string(trim($vbulletin->options['phpkd_vblvb_inex_users_ids'])) . ')' : '');
			break;
	}

	switch ($vbulletin->options['phpkd_vblvb_inex_usergroups'])
	{
		case 1:
			$usergroups = @implode(',', unserialize($vbulletin->options['phpkd_vblvb_inex_usergroups_ids']));
			$inex_usergroups = (($usergroups != '' AND strlen($usergroups) > 0) ? 'AND user.usergroupid IN (' . $vbulletin->db->escape_string($usergroups) . ')' : '');
			break;
		case 2:
			$usergroups = @implode(',', unserialize($vbulletin->options['phpkd_vblvb_inex_usergroups_ids']));
			$inex_usergroups = (($usergroups != '' AND strlen($usergroups) > 0) ? 'AND user.usergroupid NOT IN (' . $vbulletin->db->escape_string($usergroups) . ')' : '');
			break;
	}

	switch ($vbulletin->options['phpkd_vblvb_inex_forums'])
	{
		case 1:
			$forums = @implode(',', unserialize($vbulletin->options['phpkd_vblvb_inex_forums_ids']));
			$inex_forums = (($forums != '' AND strlen($forums) > 0) ? 'AND thread.forumid IN (' . $vbulletin->db->escape_string($forums) . ')' : '');
			break;
		case 2:
			$forums = @implode(',', unserialize($vbulletin->options['phpkd_vblvb_inex_forums_ids']));
			$inex_forums = (($forums != '' AND strlen($forums) > 0) ? 'AND thread.forumid NOT IN (' . $vbulletin->db->escape_string($forums) . ')' : '');
			break;
	}

	switch ($vbulletin->options['phpkd_vblvb_cutoff_mode'])
	{
		case 0:
			$cutoff = ((strlen(trim($vbulletin->options['phpkd_vblvb_cutoff_value'])) > 0) ? 'AND post.dateline > UNIX_TIMESTAMP(\'' . $vbulletin->db->escape_string(trim($vbulletin->options['phpkd_vblvb_cutoff_value'])) . '\')' : '');
			break;
		case 1:
			$cutoff = ((intval(trim($vbulletin->options['phpkd_vblvb_cutoff_value'])) > 0) ? 'AND post.dateline > ' . (TIMENOW - (intval(trim($vbulletin->options['phpkd_vblvb_cutoff_value'])) * 86400)) : '');
			break;
	}

	$checked_posts = (($vbulletin->options['phpkd_vblvb_checked_existingposts'] == 2) ? 'AND post.postid = thread.firstpostid' : '');

	$sucperiod = 'AND post.phpkd_vblvb_lastcheck ' . (($vbulletin->options['phpkd_vblvb_succession_period'] > 0) ? '< ' . (TIMENOW - ($vbulletin->options['phpkd_vblvb_succession_period'] * 86400)) : '= 0');

	$limit = (($vbulletin->options['phpkd_vblvb_limit'] > 0) ? 'LIMIT ' . $vbulletin->options['phpkd_vblvb_limit'] : '');


	$post_query = $vbulletin->db->query_read("
		SELECT user.username, user.email, user.languageid, post.userid, post.postid, post.threadid, post.title, post.pagetext, thread.forumid, thread.title AS threadtitle
		FROM " . TABLE_PREFIX . "post AS post
		LEFT JOIN " . TABLE_PREFIX . "user AS user ON (post.userid = user.userid)
		LEFT JOIN " . TABLE_PREFIX . "thread AS thread ON (post.threadid = thread.threadid)
		Where 1 = 1
			AND post.threadid NOT IN (" . @implode(',', $forced_inex_threads) . ")
			AND thread.forumid NOT IN (" . @implode(',', $forced_inex_forums) . ")
			$inex_users
			$inex_usergroups
			$inex_forums
			$cutoff
			$checked_posts
			$sucperiod
			$threadmodes
			$postmodes
		$limit
	");


	$logpunished = '';
	$punished = array();
	$records = array('checked' => 0, 'dead' => 0, 'punished' => 0);

	if ($vbulletin->db->num_rows($post_query))
	{
		$posts = array();
		while ($postitem = $vbulletin->db->fetch_array($post_query))
		{
			$posts[$postitem['postid']] = $postitem;
		}
	}
	else
	{
		$log .= $vbphrase['phpkd_vblvb_nothing_checked'];
		if (defined('IN_CONTROL_PANEL'))
		{
			print_stop_message('phpkd_vblvb_nothing_checked');
			vbflush();
		}
	}
	$vbulletin->db->free_result($post_query);


	if (is_array($posts) AND count($posts) > 0)
	{
		// Required Initialization
		$phpkd_vblvb->initialize(array('masks' => TRUE, 'punishments' => TRUE, 'staff_reports' => TRUE, 'user_reports' => TRUE));
		$colors = unserialize($vbulletin->options['phpkd_vblvb_linkstatus_colors']);
		$records['checked'] = count($posts);

		$log .= '<ol class="smallfont">';
		if (defined('IN_CONTROL_PANEL'))
		{
			echo '<ol class="smallfont">';
			vbflush();
		}

		foreach ($posts AS $postid => $post)
		{
			$log .= '<li><a href="' . $vbulletin->options['bburl'] . '/showthread.php?p=' . $postid . '" target="_blank">' . ($post['title'] ? $post['title'] : $post['threadtitle']) . '</a>';
			if (defined('IN_CONTROL_PANEL'))
			{
				echo '<li><a href="' . $vbulletin->options['bburl'] . '/showthread.php?p=' . $postid . '" target="_blank">' . ($post['title'] ? $post['title'] : $post['threadtitle']) . '</a>';
				vbflush();
			}

			$links = $phpkd_vblvb->dm()->fetch_urls($post['pagetext']);

			$links['ignored'] = $links['all'] - ($links['alive'] + $links['dead'] + $links['down']);
			$log .= $links['log'] . construct_phrase($vbphrase['phpkd_vblvb_log_summery'], $colors[0], $colors[1], $colors[2], $links['all'], $links['checked'], $links['alive'], $links['dead'], $links['down'], $links['ignored']) . '</li>';
			if (defined('IN_CONTROL_PANEL'))
			{
				echo construct_phrase($vbphrase['phpkd_vblvb_log_summery'], $colors[0], $colors[1], $colors[2], $links['all'], $links['checked'], $links['alive'], $links['dead'], $links['down'], $links['ignored']) . '</li>';
				vbflush();
			}


			// Critical Limit/Red Line
			if ($links['dead'] > 0)
			{
				if ($links['checked'] > 0)
				{
					$records['punished']++;
					$critical = ($links['dead'] / $links['checked']) * 100;
					if ($critical > $vbulletin->options['phpkd_vblvb_critical'])
					{
						$logpunished .= '<li><a href="' . $vbulletin->options['bburl'] . '/showpost.php?p=' . $postid . '" target="_blank">' . ($post['title'] ? $post['title'] : $post['threadtitle']) . '</a></li>';
						$punished[$post['userid']][$postid] = array('threadid' => $post['threadid'], 'forumid' => $post['forumid'], 'languageid' => $post['languageid'], 'username' => $post['username'], 'title' => $post['title'], 'threadtitle' => $post['threadtitle']);
					}
				}

				$records['dead']++;
			}
		}


		/**
		 * Fix Bug: "MySQL server gone away" & "Allowed memory exhausted" errors
		 * We've initiated a new DB connection with persistance allowed, so we don't get in such troubles with ongoing queries!!
		 * 
		 * Begin ->
		 */

		// Set persistent connection ON!
		$vbulletin->config['MasterServer']['usepconnect'] = 1;

		// load database class
		switch (strtolower($vbulletin->config['Database']['dbtype']))
		{
			// load standard MySQL class
			case 'mysql':
			case '':
			{
				if ($vbulletin->debug AND ($vbulletin->input->clean_gpc('r', 'explain', TYPE_UINT) OR (defined('POST_EXPLAIN') AND !empty($_POST))))
				{
					// load 'explain' database class
					require_once(DIR . '/includes/class_database_explain.php');
					$db2 = new vB_Database_Explain($vbulletin);
				}
				else
				{
					$db2 = new vB_Database($vbulletin);
				}
				break;
			}

			case 'mysql_slave':
			{
				require_once(DIR . '/includes/class_database_slave.php');
				$db2 = new vB_Database_Slave($vbulletin);
				break;
			}

			// load MySQLi class
			case 'mysqli':
			{
				if ($vbulletin->debug AND ($vbulletin->input->clean_gpc('r', 'explain', TYPE_UINT) OR (defined('POST_EXPLAIN') AND !empty($_POST))))
				{
					// load 'explain' database class
					require_once(DIR . '/includes/class_database_explain.php');
					$db2 = new vB_Database_MySQLi_Explain($vbulletin);
				}
				else
				{
					$db2 = new vB_Database_MySQLi($vbulletin);
				}
				break;
			}

			case 'mysqli_slave':
			{
				require_once(DIR . '/includes/class_database_slave.php');
				$db2 = new vB_Database_Slave_MySQLi($vbulletin);
				break;
			}

			// load extended, non MySQL class
			default:
			{
				// This is not implemented fully yet
				// $db2 = 'vB_Database_' . $vbulletin->config['Database']['dbtype'];
				// $db2 = new $db($vbulletin);
				die('Fatal error: Database class not found');
			}
		}

		// Make a new database connection
		$db2->connect(
			$vbulletin->config['Database']['dbname'],
			$vbulletin->config['MasterServer']['servername'],
			$vbulletin->config['MasterServer']['port'],
			$vbulletin->config['MasterServer']['username'],
			$vbulletin->config['MasterServer']['password'],
			$vbulletin->config['MasterServer']['usepconnect'],
			$vbulletin->config['SlaveServer']['servername'],
			$vbulletin->config['SlaveServer']['port'],
			$vbulletin->config['SlaveServer']['username'],
			$vbulletin->config['SlaveServer']['password'],
			$vbulletin->config['SlaveServer']['usepconnect'],
			$vbulletin->config['Mysqli']['ini_file'],
			(isset($vbulletin->config['Mysqli']['charset']) ? $vbulletin->config['Mysqli']['charset'] : '')
		);

		// vBulletin doesn't work under MySQL strict mode currently, so force mode required!
		$db2->force_sql_mode('');

		// make $db2 a member of $vbulletin
		$vbulletin->db =& $db2;

		/**
		 * -> End
		 * 
		 * Fix Bug: "MySQL server gone away" & "Allowed memory exhausted" errors
		 * We've initiated a new DB connection with persistance allowed, so we don't get in such troubles with ongoing queries!!
		 */

		// Finished, now update 'post.phpkd_vblvb_lastcheck'
		$vbulletin->db->query_write("
			UPDATE " . TABLE_PREFIX . "post
			SET phpkd_vblvb_lastcheck = " . TIMENOW . "
			WHERE postid IN(" . implode(',', array_keys($posts)) . ")
		");
	}

	$log .= '</ol><br />';
	if (defined('IN_CONTROL_PANEL'))
	{
		echo '</ol><br />';
		vbflush();
	}


	if (is_array($punished) AND count($punished) > 0)
	{
		$log .= $vbphrase['phpkd_vblvb_log_punished_posts'] . '<ol class="smallfont">' . $logpunished . '</ol><br />';
		if (defined('IN_CONTROL_PANEL'))
		{
			echo $vbphrase['phpkd_vblvb_log_punished_posts'] . '<ol class="smallfont">' . $logpunished . '</ol><br />';
			vbflush();
		}

		// Punish Dead Posts
		$phpkd_vblvb->dm()->punish($punished);

		// Send User Reports
		$phpkd_vblvb->dm()->user_reports($punished);
	}


	// Send Staff Reports
	switch ($vbulletin->options['phpkd_vblvb_rprts_mode'])
	{
		case 1:
			if ($records['checked'] >= 1)
			{
				$phpkd_vblvb->dm()->staff_reports($log);
			}
			break;
		case 2:
			if ($records['dead'] >= 1)
			{
				$phpkd_vblvb->dm()->staff_reports($log);
			}
			break;
		case 3:
			if ($records['punished'] >= 1)
			{
				$phpkd_vblvb->dm()->staff_reports($log);
			}
			break;
		case 0:
		default:
			$phpkd_vblvb->dm()->staff_reports($log);
			break;
	}


	unset($phpkd_vblvb);
}
else
{
	if (defined('IN_CONTROL_PANEL'))
	{
		print_stop_message('phpkd_vblvb_inactive');
	}
	else
	{
		$log .= $vbphrase['phpkd_vblvb_inactive'];
	}
}

log_cron_action($log, $nextitem, 1);


/*============================================================================*\
|| ########################################################################### ||
|| # Version: 4.0.137
|| # $Revision$
|| # Released: $Date$
|| ########################################################################### ||
\*============================================================================*/
?>