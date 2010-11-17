<?php
/*==================================================================================*\
|| ################################################################################ ||
|| # Product Name: vB Link Verifier Bot 'Ultimate'               Version: 4.0.200 # ||
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


// ############################### start do update thread subscriptions ###############################
function phpkd_vblvb_update_subscriptions($criteria)
{
	global $vbulletin;

	$sql = array();
	if (!empty($criteria['threadids']))
	{
		$sql[] = "subscribethread.threadid IN(" . implode(', ', $criteria['threadids']) . ")";
	}
	if (!empty($criteria['userids']))
	{
		$sql[] = "subscribethread.userid IN (" . implode(', ', $criteria['userids']) . ")";
	}

	if (empty($sql))
	{
		return;
	}

	// unsubscribe users who can't view the forum the threads are now in
	$users = $vbulletin->db->query_read("
		SELECT user.userid, usergroupid, membergroupids, infractiongroupids, IF(options & " . $vbulletin->bf_misc_useroptions['hasaccessmask'] . ", 1, 0) AS hasaccessmask,
			thread.postuserid, subscribethread.canview, subscribethreadid, thread.forumid
		FROM " . TABLE_PREFIX . "subscribethread AS subscribethread
		INNER JOIN " . TABLE_PREFIX . "user AS user ON (subscribethread.userid = user.userid)
		INNER JOIN " . TABLE_PREFIX . "thread AS thread ON (subscribethread.threadid = thread.threadid)
		WHERE
			" . implode(" AND ", $sql) . "
	");
	$deleteuser = array();
	$adduser = array();
	while ($thisuser = $vbulletin->db->fetch_array($users))
	{
		cache_permissions($thisuser, true, true);
		if (($thisuser['forumpermissions']["$thisuser[forumid]"] & $vbulletin->bf_ugp_forumpermissions['canview']) AND ($thisuser['forumpermissions']["$thisuser[forumid]"] & $vbulletin->bf_ugp_forumpermissions['canviewthreads']) AND ($thisuser['postuserid'] == $thisuser['userid'] OR ($thisuser['forumpermissions']["$thisuser[forumid]"] & $vbulletin->bf_ugp_forumpermissions['canviewothers'])))
		{
			// this user can now view this subscription
			if ($thisuser['canview'] == 0)
			{
				$adduser[] = $thisuser['subscribethreadid'];
			}
		}
		else
		{
			// this user can no longer view this subscription
			if ($thisuser['canview'] == 1)
			{
				$deleteuser[] = $thisuser['subscribethreadid'];
			}
		}
	}

	if (!empty($deleteuser) OR !empty($adduser))
	{
		$vbulletin->db->query_write("
			UPDATE " . TABLE_PREFIX . "subscribethread
			SET canview =
			CASE
				" . (!empty($deleteuser) ? " WHEN subscribethreadid IN (" . implode(', ', $deleteuser) . ") THEN 0" : "") . "
				" . (!empty($adduser) ? " WHEN subscribethreadid IN (" . implode(', ', $adduser) . ") THEN 1" : "") . "
			ELSE canview
			END
			WHERE subscribethreadid IN (" . implode(', ', array_merge($deleteuser, $adduser)) . ")
		");
	}
}


/**
* Fetches the list of coventry user IDs.
*
* @param	string	Type of data to return ('array' returns array of users, otherwise comma-delimited string)
* @param	boolean	True if you want to include the browsing user
*
* @return	string|array	List of coventry users in the specified format
*/
function phpkd_vblvb_fetch_coventry($returntype = 'array', $withself = false)
{
	global $vbulletin;
	static $Coventry;
	static $Coventry_with;

	if (!isset($Coventry))
	{
		if (trim($vbulletin->options['globalignore']) != '')
		{
			$Coventry = preg_split('#\s+#s', $vbulletin->options['globalignore'], -1, PREG_SPLIT_NO_EMPTY);
			$Coventry_with = $Coventry;
			$bbuserkey = array_search($vbulletin->userinfo['userid'], $Coventry);
			if ($bbuserkey !== false AND $bbuserkey !== null)
			{
				unset($Coventry["$bbuserkey"]);
			}
		}
		else
		{
			$Coventry = $Coventry_with = array();
		}
	}

	if ($withself)
	{
		if ($returntype === 'array')
		{
			// return array
			return $Coventry_with;
		}
		else
		{
			// return comma-separated string
			return implode(',', $Coventry_with);
		}
	}
	else
	{
		if ($returntype === 'array')
		{
			// return array
			return $Coventry;
		}
		else
		{
			// return comma-separated string
			return implode(',', $Coventry);
		}
	}
}


// ###################### Start updateforumcount #######################
// updates forum counters and last post info
function phpkd_vblvb_build_forum_counters($forumid, $censor = false)
{
	global $vbulletin;

	$forumid = intval($forumid);
	$foruminfo = fetch_foruminfo($forumid);

	if (!$foruminfo)
	{
		// prevent fatal errors when a forum doesn't exist
		return;
	}

	$coventry = phpkd_vblvb_fetch_coventry('string', true);

	$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "tachyforumcounter WHERE forumid = $forumid");
	$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "tachyforumpost WHERE forumid = $forumid");

	if ($coventry)
	{
		// Thread count
		$tachy_db = $vbulletin->db->query_read("
			SELECT thread.postuserid, COUNT(*) AS threadcount
			FROM " . TABLE_PREFIX . "thread AS thread
			WHERE thread.postuserid IN ($coventry)
				AND thread.visible = 1
				AND thread.open <> 10
				AND thread.forumid = $forumid
			GROUP BY thread.postuserid
		");

		$tachystats = array();

		while ($tachycounter = $vbulletin->db->fetch_array($tachy_db))
		{
			$tachystats["$tachycounter[postuserid]"]['threads'] = $tachycounter['threadcount'];
		}

		$tachy_db = $vbulletin->db->query_read("
			SELECT post.userid, COUNT(*) AS replycount
			FROM " . TABLE_PREFIX . "post AS post
			INNER JOIN " . TABLE_PREFIX . "thread AS thread ON (post.threadid = thread.threadid)
			WHERE post.userid IN ($coventry)
				AND post.visible = 1
				AND thread.forumid = $forumid
			GROUP BY post.userid
		");

		while ($tachycounter = $vbulletin->db->fetch_array($tachy_db))
		{
			if (!isset($tachystats["$tachycounter[userid]"]))
			{
				$tachystats["$tachycounter[userid]"]['threads'] = 0;
			}

			$tachystats["$tachycounter[userid]"]['replies'] = $tachycounter['replycount'];
		}

		foreach ($tachystats AS $user => $stats)
		{
			$vbulletin->db->query_write("
				REPLACE INTO " . TABLE_PREFIX . "tachyforumcounter
					(userid, forumid, threadcount, replycount)
				VALUES
					(" . intval($user) . ",
					" . intval($forumid) . ",
					" . intval($stats['threads']) . ",
					" . intval($stats['replies']) . ")
			");
		}
	}

	$totals = $vbulletin->db->query_first("
		SELECT
			COUNT(*) AS threads,
			SUM(thread.replycount) AS replies
		FROM " . TABLE_PREFIX . "thread AS thread
		WHERE thread.forumid = $forumid
			AND visible = 1
			AND open <> 10
			" . ($coventry ? " AND thread.postuserid NOT IN ($coventry)" : '')
	);

	$totals['replies'] += $totals['threads'];

	$lastthread = $vbulletin->db->query_first("
		SELECT thread.*
		FROM " . TABLE_PREFIX . "thread AS thread
		WHERE forumid = $forumid
			AND visible = 1
			AND open <> 10
			" . ($coventry ? "AND thread.postuserid NOT IN ($coventry)"  : '') ."
		ORDER BY lastpost DESC
		LIMIT 1
	");

	if ($coventry)
	{
		$tachy_posts = array();
		$tachy_db = $vbulletin->db->query_read("
			SELECT thread.*, tachythreadpost.*
			FROM " . TABLE_PREFIX . "tachythreadpost AS tachythreadpost
			INNER JOIN " . TABLE_PREFIX . "thread AS thread ON (tachythreadpost.threadid = thread.threadid)
			WHERE thread.forumid = $forumid
				AND tachythreadpost.lastpost > " . intval($lastthread['lastpost']) . "
				AND thread.visible = 1
				AND thread.open <> 10
			ORDER BY tachythreadpost.lastpost DESC
		");

		while ($tachy = $vbulletin->db->fetch_array($tachy_db))
		{
			if (!isset($tachy_posts["$tachy[userid]"]))
			{
				$tachy_posts["$tachy[userid]"] = $tachy;
			}
		}

		$tachy_replace = array();

		foreach ($tachy_posts AS $tachy)
		{
			if ($censor)
			{
				$tachy['title'] = fetch_censored_text($tachy['title']);
			}


			// Compatibility issue!!
			if (substr(SIMPLE_VERSION, 0, 1) >= 4)
			{
				$tachy_replace[] = "
					($tachy[userid], $forumid, $tachy[lastpost],
					'" . $vbulletin->db->escape_string($tachy['lastposter']) ."',
					$tachy[lastposterid],
					'" . $vbulletin->db->escape_string($tachy['title']) . "',
					$tachy[threadid],
					$tachy[iconid],
					$tachy[lastpostid],
					'" . $vbulletin->db->escape_string($tachy['prefixid']) . "')
				";
			}
			else
			{
				$tachy_replace[] = "
					($tachy[userid], $forumid, $tachy[lastpost],
					'" . $vbulletin->db->escape_string($tachy['lastposter']) ."',
					'" . $vbulletin->db->escape_string($tachy['title']) . "',
					$tachy[threadid],
					$tachy[iconid],
					$tachy[lastpostid],
					'" . $vbulletin->db->escape_string($tachy['prefixid']) . "')
				";
			}
		}

		if ($tachy_replace)
		{
			// Compatibility issue!!
			$vbulletin->db->query_write("
				REPLACE INTO " . TABLE_PREFIX . "tachyforumpost
					(userid, forumid, lastpost, lastposter" . ((substr(SIMPLE_VERSION, 0, 1) >= 4) ? ', lastposterid' : '') . ", lastthread, lastthreadid, lasticonid, lastpostid, lastprefixid)
				VALUES
					" . implode(', ', $tachy_replace)
			);
		}
	}

	//done, update the forum
	$forumdm =& datamanager_init('Forum', $vbulletin, ERRTYPE_SILENT);
	$forumdm->set_existing($foruminfo);
	$forumdm->set_info('rebuild', 1);
	$forumdm->set('threadcount',  $totals['threads'], true, false);
	$forumdm->set('replycount',   $totals['replies'],true, false);
	$forumdm->set('lastpost',     $lastthread['lastpost'], true, false);
	$forumdm->set('lastposter',   $lastthread['lastposter'], true, false);

	// Compatibility issue!!
	if (substr(SIMPLE_VERSION, 0, 1) >= 4)
	{
		$forumdm->set('lastposterid', $lastthread['lastposterid'], true, false);
	}

	$forumdm->set('lastpostid',   $lastthread['lastpostid'], true, false);

	if ($censor)
	{
		$forumdm->set('lastthread', fetch_censored_text($lastthread['title']), true, false);
	}
	else
	{
		$forumdm->set('lastthread', $lastthread['title'], true, false);
	}

	$forumdm->set('lastthreadid', $lastthread['threadid'], true, false);
	$forumdm->set('lasticonid',   ($lastthread['pollid'] ? -1 : $lastthread['iconid']), true, false);
	$forumdm->set('lastprefixid', $lastthread['prefixid'], true, false);
	$forumdm->set_info('disable_cache_rebuild', true);
	$forumdm->save();
	unset($forumdm);
}


/**
* Deletes the post cache for an array of threads
*
* @param	array	Array of thread IDs
*/
function phpkd_vblvb_delete_post_cache_threads($threadarray)
{
	global $vbulletin;

	if (!is_array($threadarray) OR empty($threadarray))
	{
		return;
	}

	$threadarray = array_map('intval', $threadarray);

	$posts = array();
	$post_sql = $vbulletin->db->query_read("
		SELECT post.postid
		FROM " . TABLE_PREFIX . "post AS post
		INNER JOIN " . TABLE_PREFIX . "postparsed AS postparsed ON (post.postid = postparsed.postid)
		WHERE post.threadid IN (" . implode(',', $threadarray) . ")
 	");
	while ($post = $vbulletin->db->fetch_array($post_sql))
	{
		$posts[] = $post['postid'];
	}

	if ($posts)
	{
		$vbulletin->db->query_write("
			DELETE FROM " . TABLE_PREFIX . "postparsed
			WHERE postid IN (" . implode(',', $posts) . ")
		");
	}
}


/**
* Fetches the integer value associated with a moderator log action string
*
* @param	string	The moderator log action
*
* @return	integer
*/
function phpkd_vblvb_fetch_modlogtypes($logtype)
{
	static $modlogtypes = array(
		'closed_thread'                           => 1,
		'opened_thread'                           => 2,
		'thread_moved_to_x'                       => 3,
		'thread_moved_with_redirect_to_a'         => 4,
		'thread_copied_to_x'                      => 5,
		'thread_edited_visible_x_open_y_sticky_z' => 6,
		'thread_merged_with_x'                    => 7,
		'thread_split_to_x'                       => 8,
		'unstuck_thread'                          => 9,
		'stuck_thread'                            => 10,
		'attachment_removed'                      => 11,
		'attachment_uploaded'                     => 12,
		'poll_edited'                             => 13,
		'thread_softdeleted'                      => 14,
		'thread_removed'                          => 15,
		'thread_undeleted'                        => 16,
		'post_x_by_y_softdeleted'                 => 17,
		'post_x_by_y_removed'                     => 18,
		'post_y_by_x_undeleted'                   => 19,
		'post_x_edited'                           => 20,
		'approved_thread'                         => 21,
		'unapproved_thread'                       => 22,
		'thread_merged_from_multiple_threads'     => 23,
		'unapproved_post'                         => 24,
		'approved_post'                           => 25,
		'post_merged_from_multiple_posts'         => 26,
		'approved_attachment'                     => 27,
		'unapproved_attachment'                   => 28,
		'thread_title_x_changed'                  => 29,
		'thread_redirect_removed'                 => 30,
		'posts_copied_to_x'                       => 31,

		'album_x_by_y_edited'                     => 32,
		'album_x_by_y_deleted'                    => 33,
		'picture_x_in_y_by_z_edited'              => 34,
		'picture_x_in_y_by_z_deleted'             => 35,
		// see 46 below as well

		'social_group_x_edited'                   => 36,
		'social_group_x_deleted'                  => 37,
		'social_group_x_members_managed'          => 38,
		'social_group_picture_x_in_y_removed'     => 39,

		'pc_by_x_on_y_edited'                     => 40,
		'pc_by_x_on_y_soft_deleted'               => 41,
		'pc_by_x_on_y_removed'                    => 42,
		'pc_by_x_on_y_undeleted'                  => 43,
		'pc_by_x_on_y_unapproved'                 => 44,
		'pc_by_x_on_y_approved'                   => 45,

		'picture_x_in_y_by_z_approved'            => 46,

		'gm_by_x_in_y_for_z_edited'               => 47,
		'gm_by_x_in_y_for_z_soft_deleted'         => 48,
		'gm_by_x_in_y_for_z_removed'              => 49,
		'gm_by_x_in_y_for_z_undeleted'            => 50,
		'gm_by_x_in_y_for_z_unapproved'           => 51,
		'gm_by_x_in_y_for_z_approved'             => 52,

		'vm_by_x_for_y_edited'                    => 53,
		'vm_by_x_for_y_soft_deleted'              => 54,
		'vm_by_x_for_y_removed'                   => 55,
		'vm_by_x_for_y_undeleted'                 => 56,
		'vm_by_x_for_y_unapproved'                => 57,
		'vm_by_x_for_y_approved'                  => 58,

		'discussion_by_x_for_y_edited'            => 59,
		'discussion_by_x_for_y_soft_deleted'      => 60,
		'discussion_by_x_for_y_removed'           => 61,
		'discussion_by_x_for_y_undeleted'         => 62,
		'discussion_by_x_for_y_unapproved'        => 63,
		'discussion_by_x_for_y_approved'          => 64,
	);

	return !empty($modlogtypes["$logtype"]) ? $modlogtypes["$logtype"] : 0;
}


/**
* Logs the moderation actions that are being performed on the forum
*
* @param	array	Array of information indicating on what data the action was performed
* @param	integer	This value corresponds to the action that was being performed
* @param	string	Other moderator parameters
*/
function phpkd_vblvb_log_moderator_action($loginfo, $logtype, $action = '')
{
	global $vbulletin;

	$modlogsql = array();

	if ($result = phpkd_vblvb_fetch_modlogtypes($logtype))
	{
		$logtype = $result;
	}

	if (is_array($loginfo[0]))
	{
		foreach ($loginfo AS $index => $log)
		{
			if (is_array($action))
			{
				$action = serialize($action);
			}
			$modlogsql[] = "(" . intval($logtype) . ", " . intval($log['userid']) . ", " . TIMENOW . ", " . intval($log['forumid']) . ", " . intval($log['threadid']) . ", " . intval($log['postid']) . ", " . intval($log['pollid']) . ", " . intval($log['attachmentid']) . ", '" . $vbulletin->db->escape_string($action) . "', '" . $vbulletin->db->escape_string(IPADDRESS) . "')";
		}

		$vbulletin->db->query_write("INSERT INTO " . TABLE_PREFIX . "moderatorlog (type, userid, dateline, forumid, threadid, postid, pollid, attachmentid, action, ipaddress) VALUES " . implode(', ', $modlogsql));
	}
	else
	{
		$moderatorlog['userid'] =& $vbulletin->userinfo['userid'];
		$moderatorlog['dateline'] = TIMENOW;

		$moderatorlog['type'] = intval($logtype);

		$moderatorlog['forumid'] = intval($loginfo['forumid']);
		$moderatorlog['threadid'] = intval($loginfo['threadid']);
		$moderatorlog['postid'] = intval($loginfo['postid']);
		$moderatorlog['pollid'] = intval($loginfo['pollid']);
		$moderatorlog['attachmentid'] = intval($loginfo['attachmentid']);

		$moderatorlog['ipaddress'] = IPADDRESS;

		if (is_array($action))
		{
			$action = serialize($action);
		}
		$moderatorlog['action'] = $action;

		$vbulletin->db->query_write(fetch_query_sql($moderatorlog, 'moderatorlog'));
	}
}


// ###################### Start undeletethread #######################
function phpkd_vblvb_undelete_thread($threadid, $countposts = true, $threadinfo = null)
{
	global $vbulletin, $vbphrase;

	// Valid threadinfo array will contain: threadid, forumid, visible
	if (!$threadinfo AND !$threadinfo = fetch_threadinfo($threadid))
	{
		return;
	}

	// thread is not deleted
	if ($threadinfo['visible'] != 2)
	{
		return;
	}

	if ($countposts)
	{
		$posts = $vbulletin->db->query_read("
			SELECT post.userid, postid
			FROM " . TABLE_PREFIX . "post AS post
			WHERE threadid = $threadid
				AND visible = 1
				AND userid > 0
		");
		$userbyuserid = array();
		while ($post = $vbulletin->db->fetch_array($posts))
		{
			if (!isset($userbyuserid["$post[userid]"]))
			{
				$userbyuserid["$post[userid]"] = 1;
			}
			else
			{
				$userbyuserid["$post[userid]"]++;
			}
		}

		if (!empty($userbyuserid))
		{
			$userbypostcount = array();
			foreach ($userbyuserid AS $postuserid => $postcount)
			{
				$alluserids .= ",$postuserid";
				$userbypostcount["$postcount"] .= ",$postuserid";
			}
			foreach($userbypostcount AS $postcount => $userids)
			{
				$casesql .= " WHEN userid IN (0$userids) THEN $postcount\n";
			}

			$vbulletin->db->query_write("
				UPDATE " . TABLE_PREFIX ."user SET
					posts = posts + CASE $casesql ELSE 0 END
				WHERE userid IN (0$alluserids)
			");
		}
	}

	$deletiondata =& datamanager_init('Deletionlog_ThreadPost', $vbulletin, ERRTYPE_SILENT, 'deletionlog');
	$deletioninfo = array('type' => 'thread', 'primaryid' => $threadid);
	$deletiondata->set_existing($deletioninfo);
	$deletiondata->delete();
	unset($deletiondata, $deletioninfo);

	$threadman =& datamanager_init('Thread', $vbulletin, ERRTYPE_SILENT, 'threadpost');
	$threadman->set_existing($threadinfo);
	$threadman->set('visible', 1);
	$threadman->save();

	phpkd_vblvb_fetch_phrase_group('threadmanage');

	phpkd_vblvb_log_moderator_action($threadinfo, 'thread_undeleted');
}


// ###################### Start deletethread #######################
function phpkd_vblvb_delete_thread($threadid, $countposts = true, $physicaldel = true, $delinfo = null, $dolog = true, $threadinfo = null)
{
	global $vbulletin, $vbphrase;

	// valid threadinfo array will contain: threadid, forumid, visible, open, pollid, title
	if (!$threadinfo AND !$threadinfo = fetch_threadinfo($threadid))
	{
		return;
	}

	if (!$physicaldel AND $threadinfo['visible'] == 2)
	{	// thread is already soft deleted
		return;
	}

	if ($dolog AND can_moderate())
	{
		// is a moderator, so log it
		phpkd_vblvb_fetch_phrase_group('threadmanage');

		if ($threadinfo['open'] == 10)
		{
			$type = 'thread_redirect_removed';
		}
		else if (!$physicaldel)
		{
			$type = 'thread_softdeleted';
		}
		else
		{
			$type = 'thread_removed';
		}

		phpkd_vblvb_log_moderator_action($threadinfo, $type);
	}

	if ($physicaldel)
	{
		// Grab the inline moderation cookie (if it exists)
		$vbulletin->input->clean_array_gpc('c', array(
			'vbulletin_inlinethread' => TYPE_STR,
			'vbulletin_inlinepost'   => TYPE_STR,
		));

		if (!empty($vbulletin->GPC['vbulletin_inlinethread']) AND !headers_sent())
		{
			$newcookie = array();
			$found = false;
			$temp = explode('-', $vbulletin->GPC['vbulletin_inlinethread']);
			foreach($temp AS $inlinethreadid)
			{
				if ($inlinethreadid == $threadid)
				{
					$found = true;
				}
				else
				{
					$newcookie[] = intval($inlinethreadid);
				}
			}

			// this thread is in the inline thread cookie so delete it by rewriting cookie without it
			if ($found)
			{
				setcookie('vbulletin_inlinethread', implode('-', $newcookie), TIMENOW + 3600, '/');
			}
		}

		$plist = array();
		if (!empty($vbulletin->GPC['vbulletin_inlinepost']))
		{
			$temp = explode('-', $vbulletin->GPC['vbulletin_inlinepost']);
			foreach($temp AS $inlinepostid)
			{
				$plist["$inlinepostid"] = true;
			}
		}

		if ($threadinfo['open'] == 10)
		{	// this is a redirect, delete it
			$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "thread WHERE threadid = $threadid");
			$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "threadredirect WHERE threadid = $threadid");
			return;
		}
	}


	// Compatibility Issue!!
	$postids = ((substr(SIMPLE_VERSION, 0, 1) >= 4) ? array() : '');
	$posts = $vbulletin->db->query_read("
		SELECT post.userid, post.postid, post.attach, post.visible
		FROM " . TABLE_PREFIX . "post AS post
		WHERE post.threadid = $threadid
	");

	$removepostid = array();
	$userbyuserid = array();

	while ($post = $vbulletin->db->fetch_array($posts))
	{
		if ($countposts AND $post['visible'] == 1 AND $post['userid'])
		{ // deleted posts have already been subtracted, ignore guest posts, hidden posts never had posts added
			if (!isset($userbyuserid["$post[userid]"]))
			{
				$userbyuserid["$post[userid]"] = 1;
			}
			else
			{
				$userbyuserid["$post[userid]"]++;
			}
		}

		// Compatibility Issue!!
		if (substr(SIMPLE_VERSION, 0, 1) >= 4)
		{
			$postids[] = $post['postid'];
		}
		else
		{
			$postids .= $post['postid'] . ',';
		}

		if ($physicaldel)
		{
			delete_post_index($post['postid']); //remove search engine entries

			// mark posts that are in the inline moderation cookie
			if (!empty($plist["$post[postid]"]))
			{
				$removepostid["$post[postid]"] = true;
			}
		}
	}

	if (!empty($userbyuserid) AND $threadinfo['visible'] == 1)
	{ // if the thread is moderated the posts have already been reduced
		$userbypostcount = array();
		foreach ($userbyuserid AS $postuserid => $postcount)
		{
			$alluserids .= ",$postuserid";
			$userbypostcount["$postcount"] .= ",$postuserid";
		}
		foreach($userbypostcount AS $postcount => $userids)
		{
			$casesql .= " WHEN userid IN (0$userids) AND posts > $postcount THEN posts - $postcount\n";
		}

		// postcounts are already negative, so we don't want to do -(-1)
		$vbulletin->db->query_write("
			UPDATE " . TABLE_PREFIX ."user
			SET
				posts =
					CASE $casesql
					ELSE 0
					END
			WHERE
				userid IN (0$alluserids)
		");
	}

	if (!empty($postids))
	{
		if ($physicaldel OR (!$delinfo['keepattachments'] AND can_moderate($threadinfo['forumid'], 'canremoveposts')))
		{
			// Compatibility Issue!!
			if (substr(SIMPLE_VERSION, 0, 1) >= 4)
			{
				require_once(DIR . '/includes/class_bootstrap_framework.php');
				require_once(DIR . '/vb/types.php');
				vB_Bootstrap_Framework::init();
				$types = vB_Types::instance();
				$contenttypeid = intval($types->getContentTypeID('vBForum_Post'));

				$attachdata =& datamanager_init('Attachment', $vbulletin, ERRTYPE_SILENT, 'attachment');
				$attachdata->condition = "a.contentid IN (" . implode(", ", $postids) . ") AND a.contenttypeid = " . intval($contenttypeid);
				$attachdata->delete(true, false);
			}
			else
			{
				$attachdata =& datamanager_init('Attachment', $vbulletin, ERRTYPE_SILENT);
				$attachdata->condition = "attachment.postid IN ($postids" . "0)";
				$attachdata->delete();
			}
		}
	}

	if (!$threadinfo['visible'])
	{ // clear out spamlog if its deleted, it was probably really spam
		$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "moderation WHERE primaryid = $threadid AND type = 'thread'");
		$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "spamlog WHERE postid = " . intval($threadinfo['firstpostid']));
	}

	if (!$physicaldel)
	{
		if (!is_array($delinfo))
		{
			$delinfo = array('userid' => $vbulletin->userinfo['userid'], 'username' => $vbulletin->userinfo['username'], 'reason' => '');
		}

		$deletionman =& datamanager_init('Deletionlog_ThreadPost', $vbulletin, ERRTYPE_SILENT, 'deletionlog');
		$deletionman->set('primaryid', $threadinfo['threadid']);
		$deletionman->set('type', 'thread');
		$deletionman->set('userid', $delinfo['userid']);
		$deletionman->set('username', $delinfo['username']);
		$deletionman->set('reason', $delinfo['reason']);
		$deletionman->save();
		unset($deletionman);

		$threadman =& datamanager_init('Thread', $vbulletin, ERRTYPE_SILENT, 'threadpost');
		$threadman->set_existing($threadinfo);
		$threadman->set('visible', 2);
		if (!$delinfo['keepattachments'])
		{
			$threadman->set('attach', 0);
		}
		$threadman->save();

		// Delete any redirects to this thread
		$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "thread WHERE open = 10 AND pollid = $threadid");

		return;
	}

	if (!empty($postids))
	{
		// Compatibility Issue!!
		if (substr(SIMPLE_VERSION, 0, 1) >= 4)
		{
			$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "post WHERE postid IN (" . implode(", ", $postids) . ")");
			$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "postparsed WHERE postid IN (" . implode(", ", $postids) . ")");
			//$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "reputation WHERE postid IN (" . implode(", ", $postids) . ")");
			$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "moderation WHERE type = 'reply' AND primaryid IN (" . implode(", ", $postids) . ")");
			$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "editlog WHERE postid IN (" . implode(", ", $postids) . ")");
			$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "postedithistory WHERE postid IN (" . implode(", ", $postids) . ")");
			$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "deletionlog WHERE type= 'post' AND primaryid IN (" . implode(", ", $postids) . ")");
			$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "podcastitem WHERE postid = " . intval($threadinfo['firstpostid']));
		}
		else
		{
			$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "post WHERE postid IN ($postids" . "0)");
			$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "postparsed WHERE postid IN ($postids" . "0)");
			//$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "reputation WHERE postid IN ($postids" . "0)");
			$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "moderation WHERE type = 'reply' AND primaryid IN ($postids" . "0)");
			$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "editlog WHERE postid IN ($postids" . "0)");
			$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "postedithistory WHERE postid IN ($postids" . "0)");
			$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "deletionlog WHERE type= 'post' AND primaryid IN ($postids" . "0)");
			$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "podcastitem WHERE postid = " . intval($threadinfo['firstpostid']));
		}

		// remove deleted posts from inline moderation cookie
		if (!empty($removepostid) AND !headers_sent())
		{
			$newcookie = array();
			foreach($plist AS $inlinepostid => $value)
			{
				if (empty($removepostid["$inlinepostid"]))
				{
					$newcookie[] = intval($inlinepostid);
				}
			}

			setcookie('vbulletin_inlinepost', implode('-', $newcookie), TIMENOW + 3600, '/');
		}

	}
	if ($threadinfo['pollid'] != 0 AND $threadinfo['open'] != 10)
	{
		$pollman =& datamanager_init('Poll', $vbulletin, ERRTYPE_SILENT);
		$pollid = array ('pollid' => $threadinfo['pollid']);
		$pollman->set_existing($pollid);
		$pollman->delete();
	}

	$deletiondata =& datamanager_init('Deletionlog_ThreadPost', $vbulletin, ERRTYPE_SILENT, 'deletionlog');
	$deletioninfo = array('type' => 'thread', 'primaryid' => $threadid);
	$deletiondata->set_existing($deletioninfo);
	$deletiondata->delete();
	unset($deletiondata, $deletioninfo);

	$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "thread WHERE threadid = $threadid");
	$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "thread WHERE open=10 AND pollid = $threadid"); // delete redirects
	$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "threadrate WHERE threadid = $threadid");
	$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "threadread WHERE threadid = $threadid");
	$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "subscribethread WHERE threadid = $threadid");
	$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "tachythreadpost WHERE threadid = $threadid");
	$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "tachythreadcounter WHERE threadid = $threadid");

	// Compatibility Issue!!
	if (substr(SIMPLE_VERSION, 0, 1) >= 4)
	{
		require_once(DIR . '/includes/class_taggablecontent.php');
		$content = vB_Taggable_Content_Item::create($vbulletin, "vBForum_Thread", $threadid, $threadinfo);
	}
	else
	{
		$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "tagthread WHERE threadid = $threadid");
	}

	if ($threadinfo['open'] == 10)
	{
		$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "threadredirect WHERE threadid = $threadid");
	}

	$vbulletin->db->query_write("
		UPDATE " . TABLE_PREFIX . "moderatorlog SET
			threadtitle = '". $vbulletin->db->escape_string($threadinfo['title']) ."'
		WHERE threadid = $threadid
	");
}


/**
* Adds an entire phrase group to the $vbphrase array.
* Utility function that is actually only used in this file.
*
* @param	string	Group Name
*/
function phpkd_vblvb_fetch_phrase_group($groupname)
{
	global $vbulletin, $vbphrase, $phrasegroups;

	if (in_array($groupname, $phrasegroups))
	{
		// this group is already in $vbphrase
		return;
	}
	$phrasegroups[] = $groupname;

	$group = $vbulletin->db->query_first_slave("
		SELECT phrasegroup_$groupname AS $groupname
		FROM " . TABLE_PREFIX . "language
		WHERE languageid = " . intval(iif($vbulletin->userinfo['languageid'], $vbulletin->userinfo['languageid'], $vbulletin->options['languageid']))
	);

	$vbphrase = array_merge($vbphrase, unserialize($group["$groupname"]));
}


/**
* Validates the provided value of a setting against its datatype
*
* @param	mixed	(ref) Setting value
* @param	string	Setting datatype ('number', 'boolean' or other)
* @param	boolean	Represent boolean with 1/0 instead of true/false
* @param boolean  Query database for username type
*
* @return	mixed	Setting value
*/
function phpkd_vblvb_validate_setting_value(&$value, $datatype, $bool_as_int = true, $username_query = true)
{
	global $vbulletin;

	switch ($datatype)
	{
		case 'number':
			$value += 0;
			break;

		case 'integer':
			$value = intval($value);
			break;

		case 'arrayinteger':
			$key = array_keys($value);
			$size = sizeOf($key);
			for ($i = 0; $i < $size; $i++)
			{
				$value[$key[$i]] = intval($value[$key[$i]]);
			}
			break;

		case 'arrayfree':
			$key = array_keys($value);
			$size = sizeOf($key);
			for ($i = 0; $i < $size; $i++)
			{
				$value[$key[$i]] = trim($value[$key[$i]]);
			}
			break;

		case 'posint':
			$value = max(1, intval($value));
			break;

		case 'boolean':
			$value = ($bool_as_int ? ($value ? 1 : 0) : ($value ? true : false));
			break;

		case 'bitfield':
			if (is_array($value))
			{
				$bitfield = 0;
				foreach ($value AS $bitval)
				{
					$bitfield += $bitval;
				}
				$value = $bitfield;
			}
			else
			{
				$value += 0;
			}
			break;

		case 'username':
			$value = trim($value);
			if ($username_query)
			{
				if (empty($value))
				{
					$value =  0;
				}
				else if ($userinfo = $vbulletin->db->query_first("SELECT userid FROM " . TABLE_PREFIX . "user WHERE username = '" . $vbulletin->db->escape_string(htmlspecialchars_uni($value)) . "'"))
				{
					$value = $userinfo['userid'];
				}
				else
				{
					$value = false;
				}
			}
			break;

		default:
			$value = trim($value);
	}

	return $value;
}

// ###################### Start approvethread #######################
function phpkd_vblvb_approve_thread($threadid, $countposts = true, $dolog = true, $threadinfo = null)
{
	global $vbulletin, $vbphrase;

	// Valid threadinfo array will contain: threadid, forumid, visible
	if (!$threadinfo AND !$threadinfo = fetch_threadinfo($threadid))
	{
		return;
	}

	if ($threadinfo['visible'])
	{	// thread is already approved or deleted
		return;
	}

	if ($dolog)
	{
		// is a moderator, so log it
		phpkd_vblvb_fetch_phrase_group('threadmanage');
		phpkd_vblvb_log_moderator_action($threadinfo, 'approved_thread');
	}

	// Increment posts if this is a counting forum
	if ($countposts)
	{
		$userbyuserid = array();
		$postids = '';
		$posts = $vbulletin->db->query_read("
			SELECT userid, visible
			FROM " . TABLE_PREFIX . "post AS post
			WHERE threadid = $threadid
				AND visible = 1
				AND userid > 0
		");
		while ($post = $vbulletin->db->fetch_array($posts))
		{
			if (!isset($userbyuserid["$post[userid]"]))
			{
				$userbyuserid["$post[userid]"] = 1;
			}
			else
			{
				$userbyuserid["$post[userid]"]++;
			}
		}

		if (!empty($userbyuserid))
		{
			$userbypostcount = array();
			foreach ($userbyuserid AS $postuserid => $postcount)
			{
				$alluserids .= ",$postuserid";
				$userbypostcount["$postcount"] .= ",$postuserid";
			}
			foreach($userbypostcount AS $postcount => $userids)
			{
				$casesql .= " WHEN userid IN (0$userids) THEN $postcount\n";
			}

			$vbulletin->db->query_write("
				UPDATE " . TABLE_PREFIX . "user
				SET posts = posts +
					CASE
						$casesql
						ELSE 0
					END,
					lastpostid = 0
				WHERE userid IN (0$alluserids)
			");
		}
	}

	// Delete moderation record
	$vbulletin->db->query_write("
		DELETE FROM " . TABLE_PREFIX . "moderation
		WHERE type = 'thread'
			AND primaryid = $threadid
	");
	$vbulletin->db->query_write("
		DELETE FROM " . TABLE_PREFIX . "spamlog
		WHERE postid = $threadinfo[firstpostid]
	");

	// Set thread redirects visible
	$vbulletin->db->query_write("
		UPDATE " . TABLE_PREFIX . "thread
		SET visible = 1
		WHERE open = 10 AND pollid = $threadid
	");

	// Set thread visible
	$threadman =& datamanager_init('Thread', $vbulletin, ERRTYPE_ARRAY, 'threadpost');
	$threadman->set_existing($threadinfo);
	$threadman->set('visible', 1);
	$threadman->save();
	unset($threadman);

	return;
}

// ###################### Start unapprovethread #######################
function phpkd_vblvb_unapprove_thread($threadid, $countposts = true, $dolog = true, $threadinfo = null)
{
	global $vbulletin, $vbphrase;

	// Valid threadinfo array will contain: threadid, forumid, visible, firstpostid
	if (!$threadinfo AND !$threadinfo = fetch_threadinfo($threadid))
	{
		return;
	}

	if (!$threadinfo['visible'])
	{	// thread is already moderated
		return;
	}

	if ($dolog)
	{
		// is a moderator, so log it
		phpkd_vblvb_fetch_phrase_group('threadmanage');
		phpkd_vblvb_log_moderator_action($threadinfo, 'unapproved_thread');
	}

	// Decrement posts if this is a counting forum and the thread is currently visible

	if ($countposts AND $threadinfo['visible'] == 1)
	{
		$userbyuserid = array();
		$postids = '';
		$posts = $vbulletin->db->query_read("
			SELECT userid, visible
			FROM " . TABLE_PREFIX . "post AS post
			WHERE threadid = $threadid
				AND visible = 1
				AND userid > 0
		");
		while ($post = $vbulletin->db->fetch_array($posts))
		{
			if (!isset($userbyuserid["$post[userid]"]))
			{
				$userbyuserid["$post[userid]"] = -1;
			}
			else
			{
				$userbyuserid["$post[userid]"]--;
			}
		}

		if (!empty($userbyuserid))
		{ // if the thread is already deleted or moderated, the posts have already been reduced
			$userbypostcount = array();
			foreach ($userbyuserid AS $postuserid => $postcount)
			{
				$alluserids .= ",$postuserid";
				$userbypostcount["$postcount"] .= ",$postuserid";
			}
			foreach($userbypostcount AS $postcount => $userids)
			{
				$casesql .= " WHEN userid IN (0$userids) THEN $postcount\n";
			}

			$vbulletin->db->query_write("
				UPDATE " . TABLE_PREFIX . "user
				SET posts = CAST(posts AS SIGNED) +
				CASE
					$casesql
					ELSE 0
				END
				WHERE userid IN (0$alluserids)
			");
		}
	}

	// Set thread redirects hidden
	$vbulletin->db->query_write("
		UPDATE " . TABLE_PREFIX . "thread
		SET visible = 0
		WHERE open = 10 AND pollid = $threadid
	");

	if ($threadinfo['visible'] == 2)
	{	// This is a deleted thread - remove deletionlog entry
		$deletiondata =& datamanager_init('Deletionlog_ThreadPost', $vbulletin, ERRTYPE_SILENT, 'deletionlog');
		$deletioninfo = array('type' => 'thread', 'primaryid' => $threadid);
		$deletiondata->set_existing($deletioninfo);
		$deletiondata->delete();
		unset($deletiondata, $deletioninfo);
	}

	// Insert moderation record
	$vbulletin->db->query_write("
		REPLACE INTO " . TABLE_PREFIX . "moderation
		(primaryid, type, dateline)
		VALUES
		($threadid, 'thread', " . TIMENOW . ")
	");

	// Set thread invisible
	$threadman =& datamanager_init('Thread', $vbulletin, ERRTYPE_SILENT, 'threadpost');
	$threadman->set_existing($threadinfo);
	$threadman->set('visible', 0);
	$threadman->save();
	unset($threadman);

	return;
}


/*============================================================================*\
|| ########################################################################### ||
|| # Version: 4.0.200
|| # $Revision$
|| # Released: $Date$
|| ########################################################################### ||
\*============================================================================*/