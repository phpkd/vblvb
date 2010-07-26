<?php
/*==================================================================================*\
|| ################################################################################ ||
|| # Product Name: vB Link Verifier Bot 'Ultimate'               Version: 4.0.136 # ||
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


if (@get_class($this) != 'PHPKD_VBLVB' OR !defined('PHPKD_VBLVB'))
{
	echo 'Prohibited Access!';
	exit;
}


/*
 * Required Initializations
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~
 * NULL
 * 
 * Input Parameters:
 * ~~~~~~~~~~~~~~~~~~
 * $foruminfo, $threadinfo, $postinfo, $edit, $dataman
 * 
 * Output Parameters:
 * ~~~~~~~~~~~~~~~~~~~
 * NULL
 * 
 */


// Parameters required!
if (is_array($params) AND !empty($params) AND $this->verify_hook_params($params))
{
	$internalhandle = FALSE;
	@extract($params);
}
else
{
	trigger_error('Invalid parameters!', E_USER_ERROR);
}


if ($this->registry->options['phpkd_vblvb_checked_editedposts'] == 1 OR ($threadinfo['firstpostid'] == $postinfo['postid'] AND $this->registry->options['phpkd_vblvb_checked_editedposts'] == 2))
{
	$phpkd_vblvb_pass = FALSE;

	// ... Licensing ... //
	$phpkd_vblvb_license_proceed = FALSE;
	$phpkd_commercial40_license = @unserialize($this->registry->options['phpkd_commercial40_license']);
	if ($phpkd_commercial40_license['vblvb']['lc'] > (TIMENOW - 86400))
	{
		$phpkd_vblvb_license_proceed = TRUE;
	}
	else if ($this->verify_license() == 'valid')
	{
		$phpkd_vblvb_license_proceed = TRUE;

		$phpkd_commercial40_license['vblvb']['lc'] = TIMENOW;
		$this->registry->db->query_write("UPDATE " . TABLE_PREFIX . "setting SET value = '" . @serialize($phpkd_commercial40_license) . "' WHERE varname = 'phpkd_commercial40_license'");

		require_once(DIR . '/includes/phpkd/vblvb/functions_databuild.php');
		phpkd_vblvb_build_options();
	}
	// ... Licensing ... //


	if ($phpkd_vblvb_license_proceed AND !in_array($this->registry->userinfo['usergroupid'], explode(' ', $this->registry->options['phpkd_vblvb_powerful_ugids'])))
	{
		$internalhandle = TRUE;

		$phpkd_vblvb_fetch_urls = $this->dm(array('hosts' => TRUE, 'masks' => TRUE, 'protocols' => TRUE, 'bbcodes' => TRUE, 'punishments' => TRUE, 'staff_reports' => TRUE, 'user_reports' => TRUE))->fetch_urls($edit['message']);

		// Critical Limit/Red Line
		if ($phpkd_vblvb_fetch_urls['dead'] > 0 AND $phpkd_vblvb_fetch_urls['checked'] > 0)
		{
			$phpkd_vblvb_critical = ($phpkd_vblvb_fetch_urls['dead'] / $phpkd_vblvb_fetch_urls['checked']) * 100;
			if ($phpkd_vblvb_critical > $this->registry->options['phpkd_vblvb_critical'])
			{
				$dataman->error('phpkd_vblvb_editpost');
			}
			else
			{
				$phpkd_vblvb_pass = TRUE;
			}
		}
		else
		{
			$phpkd_vblvb_pass = TRUE;
		}
	}
	else
	{
		$phpkd_vblvb_pass = TRUE;
	}


	if ($phpkd_vblvb_pass AND $this->registry->options['phpkd_vblvb_revert'] AND $phpkd_vblvb_logged = $this->registry->db->query_first("
		SELECT phpkd_vblvb FROM " . TABLE_PREFIX . "post
		WHERE postid = $postinfo[postid]
	"))
	{
		if ($phpkd_vblvb_logged['phpkd_vblvb'])
		{
			require_once(DIR . '/includes/phpkd/vblvb/functions_databuild.php');


			if ($phpkd_vblvb_arr['moderate'] AND $threadinfo['visible'] == 0)
			{
				phpkd_vblvb_approve_thread($threadinfo['threadid'], $foruminfo['countposts'], FALSE, $threadinfo);
			}


			if ($phpkd_vblvb_arr['close'] AND $threadinfo['open'] == 0)
			{
				$threadman =& datamanager_init('Thread', $this->registry, ERRTYPE_STANDARD, 'threadpost');
				$threadman->set_info('skip_moderator_log', true);
				$threadman->set_existing($threadinfo);
				$threadman->set('open', 1);
				$threadman->save();
				unset($threadman);
			}


			// TODO: Temporary disabled!
			if ($phpkd_vblvb_arr['delete'] AND $threadinfo['visible'] == 2)
			{
				phpkd_vblvb_undelete_thread($threadinfo['threadid'], $foruminfo['countposts'], $threadinfo);
			}


			$phpkd_vblvb_arr = @unserialize($phpkd_vblvb_logged['phpkd_vblvb']);
			if ($phpkd_vblvb_arr['move']['orifid'] AND $phpkd_vblvb_arr['move']['destfid'])
			{
				$destforum = verify_id('forum', $phpkd_vblvb_arr['move']['orifid'], FALSE, TRUE);
				if ($destforum['cancontainthreads'] AND !$destforum['link'])
				{
					if ($threadinfo['forumid'] == $phpkd_vblvb_arr['move']['destfid'])
					{
						// check to see if this thread is being returned to a forum it's already been in
						// if a redirect exists already in the destination forum, remove it
						if ($checkprevious = $this->registry->db->query_first("SELECT threadid FROM " . TABLE_PREFIX . "thread WHERE forumid = $destforum[forumid] AND open = 10"))
						{
							$old_redirect =& datamanager_init('Thread', $this->registry, ERRTYPE_ARRAY, 'threadpost');
							$old_redirect->set_existing($checkprevious);
							$old_redirect->delete(false, true, NULL, false);
							unset($old_redirect);
						}

						// check to see if this thread is being moved to the same forum it's already in but allow copying to the same forum
						if ($destforum['forumid'] != $threadinfo['forumid'])
						{
							// update forumid/notes and unstick to prevent abuse
							$threadman =& datamanager_init('Thread', $this->registry, ERRTYPE_STANDARD, 'threadpost');
							$threadman->set_info('skip_moderator_log', true);
							$threadman->set_existing($threadinfo);
							$threadman->set('title', $threadinfo['title'], true, false);
							$threadman->set('forumid', $destforum['forumid']);
							$threadman->save();
							unset($threadman);

							// kill the cache for the old thread
							phpkd_vblvb_delete_post_cache_threads(array($threadinfo['threadid']));

							// Update Post Count if we move from a counting forum to a non counting or vice-versa..
							// Source Dest  Visible Thread    Hidden Thread
							// Yes    Yes   ~           	  ~
							// Yes    No    -visible          ~
							// No     Yes   +visible          ~
							// No     No    ~                 ~
							if ($threadinfo['visible'] AND (($foruminfo['countposts'] AND !$destforum['countposts']) OR (!$foruminfo['countposts'] AND $destforum['countposts'])))
							{
								$uposts = $this->registry->db->query_read("
									SELECT userid
									FROM " . TABLE_PREFIX . "post
									WHERE threadid = $threadinfo[threadid]
										AND	userid > 0
										AND visible = 1
								");

								$userbyuserid = array();
								while ($upost = $this->registry->db->fetch_array($uposts))
								{
									if (!isset($userbyuserid["$upost[userid]"]))
									{
										$userbyuserid["$upost[userid]"] = 1;
									}
									else
									{
										$userbyuserid["$upost[userid]"]++;
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

									foreach ($userbypostcount AS $postcount => $userids)
									{
										$casesql .= " WHEN userid IN (0$userids) THEN $postcount";
									}

									$operator = ($destforum['countposts'] ? '+' : '-');

									$this->registry->db->query_write("
										UPDATE " . TABLE_PREFIX . "user
										SET posts = CAST(posts AS SIGNED) $operator
											CASE
												$casesql
												ELSE 0
											END
										WHERE userid IN (0$alluserids)
									");
								}

								unset($userbyuserid, $userbypostcount, $operator);
							}

							phpkd_vblvb_build_forum_counters($threadinfo['forumid']);
							if ($threadinfo['forumid'] != $destforum['forumid'])
							{
								phpkd_vblvb_build_forum_counters($destforum['forumid']);
							}

							// Update canview status of thread subscriptions
							phpkd_vblvb_update_subscriptions(array('threadids' => array($threadinfo['threadid'])));
						}
					}
				}
			}


			$this->registry->db->query_write("
				UPDATE " . TABLE_PREFIX . "post SET
					phpkd_vblvb = ''
				WHERE postid = $postinfo[postid]
			");
		}
	}
}


if ($internalhandle)
{
	return TRUE;
}
else
{
	return FALSE;
}


/*============================================================================*\
|| ########################################################################### ||
|| # Version: 4.0.136
|| # $Revision$
|| # Released: $Date$
|| ########################################################################### ||
\*============================================================================*/