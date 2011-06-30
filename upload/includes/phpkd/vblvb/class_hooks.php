<?php
/*==================================================================================*\
|| ################################################################################ ||
|| # Product Name: vB Link Verifier Bot 'Ultimate'               Version: 4.1.210 # ||
|| # License Type: Commercial License                            $Revision$ # ||
|| # ---------------------------------------------------------------------------- # ||
|| # 																			  # ||
|| #            Copyright ©2005-2011 PHP KingDom. All Rights Reserved.            # ||
|| #      This product may not be redistributed in whole or significant part.     # ||
|| # 																			  # ||
|| # ---------- "vB Link Verifier Bot 'Ultimate'" IS NOT FREE SOFTWARE ---------- # ||
|| #     http://www.phpkd.net | http://info.phpkd.net/en/license/commercial       # ||
|| ################################################################################ ||
\*==================================================================================*/


// No direct access! Should be accessed throuth the core class only!!
if (!defined('VB_AREA') OR !defined('PHPKD_VBLVB') OR @get_class($this) != 'PHPKD_VBLVB')
{
	echo 'Prohibited Access!';
	exit;
}


/**
 * Hooks class
 *
 * @category	vB Link Verifier Bot 'Ultimate'
 * @package		PHPKD_VBLVB
 * @subpackage	PHPKD_VBLVB_Hooks
 * @copyright	Copyright ©2005-2011 PHP KingDom. All Rights Reserved. (http://www.phpkd.net)
 * @license		http://info.phpkd.net/en/license/commercial
 */
class PHPKD_VBLVB_Hooks
{
	/**
	 * The PHPKD_VBLVB registry object
	 *
	 * @var	PHPKD_VBLVB
	 */
	private $_registry = null;

	/**
	 * Constructor - checks that PHPKD_VBLVB registry object including vBulletin registry oject has been passed correctly.
	 *
	 * @param	PHPKD_VBLVB	Instance of the main product's data registry object - expected to have both vBulletin data registry & database object as two of its members.
	 * @return	void
	 */
	public function __construct(&$registry)
	{
		if (is_object($registry))
		{
			$this->_registry =& $registry;

			if (is_object($registry->_vbulletin))
			{
				if (!is_object($registry->_vbulletin->db))
				{
					trigger_error('vBulletin Database object is not an object!', E_USER_ERROR);
				}
			}
			else
			{
				trigger_error('vBulletin Registry object is not an object!', E_USER_ERROR);
			}
		}
		else
		{
			trigger_error('PHPKD_VBLVB Registry object is not an object!', E_USER_ERROR);
		}
	}

	/*
	 * Required Initializations
	 * ~~~~~~~~~~~~~~~~~~~~~~~~~~
	 * NULL
	 *
	 * Input Parameters:
	 * ~~~~~~~~~~~~~~~~~~
	 * $do, $admin
	 *
	 * Output Parameters:
	 * ~~~~~~~~~~~~~~~~~~~
	 * $return_value
	 *
	 */
	public function can_administer($params)
	{
		// Parameters required!
		if ($this->_registry->verify_hook_params($params))
		{
			@extract($params);
			$bits = array(
				'phpkd_vblvb' => 1
			);

			foreach($do as $field)
			{
				if (isset($bits["$field"]) AND ($admin['phpkd_vblvb'] & $bits["$field"]))
				{
					$return_value = true;
					return array('return_value' => $return_value);
				}
			}
		}
	}

	/*
	 * Required Initializations
	 * ~~~~~~~~~~~~~~~~~~~~~~~~~~
	 * NULL
	 *
	 * Input Parameters:
	 * ~~~~~~~~~~~~~~~~~~
	 * $admindm
	 *
	 * Output Parameters:
	 * ~~~~~~~~~~~~~~~~~~~
	 * NULL
	 *
	 */
	public function admin_permissions_process($params)
	{
		// Parameters required!
		if ($this->_registry->verify_hook_params($params))
		{
			@extract($params);
			$this->_registry->_vbulletin->input->clean_array_gpc('p', array(
				'phpkd_vblvb' => TYPE_ARRAY_INT
			));

			$perms = 0;

			foreach ($this->_registry->_vbulletin->GPC['phpkd_vblvb'] as $bit => $value)
			{
				if ($value)
				{
					$perms += $bit;
				}
			}

			$admindm->set('phpkd_vblvb', $perms);

			return true;
		}
	}

	/*
	 * Required Initializations
	 * ~~~~~~~~~~~~~~~~~~~~~~~~~~
	 * NULL
	 *
	 * Input Parameters:
	 * ~~~~~~~~~~~~~~~~~~
	 * $user
	 *
	 * Output Parameters:
	 * ~~~~~~~~~~~~~~~~~~~
	 * NULL
	 *
	 */
	public function admin_permissions_form($params)
	{
		// Parameters required!
		if ($this->_registry->verify_hook_params($params))
		{
			@extract($params);
			print_yes_no_row($this->_registry->_vbphrase['can_administer_phpkd_vblvb'], "phpkd_vblvb[1]", ($user['phpkd_vblvb'] & 1 ? 1 : 0));
			return true;
		}
	}

	/*
	 * Required Initializations
	 * ~~~~~~~~~~~~~~~~~~~~~~~~~~
	 * NULL
	 *
	 * Input Parameters:
	 * ~~~~~~~~~~~~~~~~~~
	 * (vB_DataManager_Admin) $there
	 *
	 * Output Parameters:
	 * ~~~~~~~~~~~~~~~~~~~
	 * NULL
	 *
	 */
	public function admindata_start($params)
	{
		// Parameters required!
		if ($this->_registry->verify_hook_params($params))
		{
			@extract($params);
			$there->validfields['phpkd_vblvb'] = array(TYPE_UINT, REQ_NO);
			return true;
		}
	}

	/*
	 * Required Initializations
	 * ~~~~~~~~~~~~~~~~~~~~~~~~~~
	 * NULL
	 *
	 * Input Parameters:
	 * ~~~~~~~~~~~~~~~~~~
	 * $thread
	 *
	 * Output Parameters:
	 * ~~~~~~~~~~~~~~~~~~~
	 * $phpkd_vblvb_postbit
	 *
	 */
	public function showthread_postbit_create($params)
	{
		// Parameters required!
		if ($this->_registry->verify_hook_params($params))
		{
			@extract($params);

			$phpkd_vblvb_postbit = false;
			$lookfeel_postbit = unserialize($this->_registry->_vbulletin->phpkd_vblvb['lookfeel_postbit_note']);

			if (!empty($lookfeel_postbit) AND in_array($thread['forumid'], $lookfeel_postbit))
			{
				$phpkd_vblvb_postbit = true;
			}

			return array('phpkd_vblvb_postbit' => $phpkd_vblvb_postbit);
		}
	}

	/*
	 * Required Initializations
	 * ~~~~~~~~~~~~~~~~~~~~~~~~~~
	 * NULL
	 *
	 * Input Parameters:
	 * ~~~~~~~~~~~~~~~~~~
	 * $post, $forumid, $template_prefix, $templatename
	 *
	 * Output Parameters:
	 * ~~~~~~~~~~~~~~~~~~~
	 * $template_hook
	 *
	 */
	public function postbit_display_complete($params)
	{
		// Parameters required!
		if ($this->_registry->verify_hook_params($params))
		{
			@extract($params);
			global $phpkd_vblvb_postbit;

			if (!empty($phpkd_vblvb_postbit))
			{
				if (!empty($post['phpkd_vblvb_lastcheck']))
				{
					// format date/time
					$postdate = vbdate($this->_registry->_vbulletin->options['dateformat'], $post['phpkd_vblvb_lastcheck'], true);
					$posttime = vbdate($this->_registry->_vbulletin->options['timeformat'], $post['phpkd_vblvb_lastcheck']);

					$template_hook['postbit_phpkd_vblvb'] .= construct_phrase($this->_registry->_vbphrase['phpkd_vblvb_lastcheck'], $postdate, $posttime);
				}
				else
				{
					$template_hook['postbit_phpkd_vblvb'] .= $this->_registry->_vbphrase['phpkd_vblvb_lastcheck_never'];
				}
			}
			else
			{
				$template_hook['postbit_phpkd_vblvb'] .= $this->_registry->_vbphrase['phpkd_vblvb_lastcheck_disabled'];
			}

			return array('template_hook' => $template_hook);
		}
	}

	/*
	 * Required Initializations
	 * ~~~~~~~~~~~~~~~~~~~~~~~~~~
	 * NULL
	 *
	 * Input Parameters:
	 * ~~~~~~~~~~~~~~~~~~
	 * $type, $post, $dataman
	 *
	 * Output Parameters:
	 * ~~~~~~~~~~~~~~~~~~~
	 * NULL
	 *
	 */
	public function newpost_process($params)
	{
		// Parameters required!
		if ($this->_registry->verify_hook_params($params))
		{
			@extract($params);

			if (($type == 'thread' AND ($this->_registry->_vbulletin->phpkd_vblvb['general_checked_newposts'] == 1 OR $this->_registry->_vbulletin->phpkd_vblvb['general_checked_newposts'] == 2)) OR $type == 'reply' AND $this->_registry->_vbulletin->phpkd_vblvb['general_checked_newposts'] == 1)
			{
				if ($this->_registry->verify_license(true) AND !in_array($this->_registry->_vbulletin->userinfo['usergroupid'], explode(' ', $this->_registry->_vbulletin->phpkd_vblvb['punishment_powerful_ugids'])))
				{
					$links = $this->_registry->getDmhandle()->fetch_urls($post['message'], $post['postid']);

					// Critical Limit/Red Line
					if ($links['checked'] > 0 AND $links['dead'] > 0)
					{
						$critical = ($links['dead'] / $links['checked']) * 100;

						if ($critical >= $this->_registry->_vbulletin->phpkd_vblvb['general_critical_limit'])
						{
							$dataman->error('phpkd_vblvb_invalid_checkpost');
						}
					}
				}
			}

			return true;
		}
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
	public function editpost_update_process($params)
	{
		// Parameters required!
		if ($this->_registry->verify_hook_params($params))
		{
			@extract($params);

			if ($this->_registry->_vbulletin->phpkd_vblvb['general_checked_editedposts'] == 1 OR ($threadinfo['firstpostid'] == $postinfo['postid'] AND $this->_registry->_vbulletin->phpkd_vblvb['general_checked_editedposts'] == 2))
			{
				if ($this->_registry->verify_license(true))
				{
					if (!in_array($this->_registry->_vbulletin->userinfo['usergroupid'], explode(' ', $this->_registry->_vbulletin->phpkd_vblvb['punishment_powerful_ugids'])))
					{
						$proceed = false;
						$links = $this->_registry->getDmhandle()->fetch_urls($edit['message'], $edit['postid']);

						// Critical Limit/Red Line
						if ($links['checked'] > 0 AND $links['dead'] > 0)
						{
							$critical = ($links['dead'] / $links['checked']) * 100;

							if ($critical >= $this->_registry->_vbulletin->phpkd_vblvb['general_critical_limit'])
							{
								$dataman->error('phpkd_vblvb_invalid_checkpost');
							}
							else
							{
								$proceed = true;
							}
						}
						else
						{
							$proceed = true;
						}
					}
					else
					{
						$proceed = true;
					}

					if ($proceed AND !$edit['preview'] AND !$this->_registry->_vbulletin->GPC['advanced'] AND $this->_registry->_vbulletin->phpkd_vblvb['punishment_onedit_revert'])
					{
						require_once(DIR . '/includes/functions_databuild.php');

						if ($postlog = $this->_registry->_vbulletin->db->query_first("
							SELECT phpkd_vblvb_lastpunish FROM " . TABLE_PREFIX . "post
							WHERE postid = $postinfo[postid]
						") AND !empty($postlog['phpkd_vblvb_lastpunish']))
						{
							$postlog_arr = @unserialize($postlog['phpkd_vblvb_lastpunish']);

							if ($postlog_arr['moderate'] AND $postinfo['visible'] == 0)
							{
								approve_post($postinfo['postid'], $foruminfo['countposts'], false, $postinfo, $threadinfo);
							}

							if ($postlog_arr['delete'] AND $postinfo['visible'] == 2)
							{
								undelete_post($postinfo['postid'], $foruminfo['countposts'], $postinfo, $threadinfo);
							}

							$this->_registry->_vbulletin->db->query_write("
								UPDATE " . TABLE_PREFIX . "post SET
									phpkd_vblvb_lastpunish = ''
								WHERE postid = $postinfo[postid]
							");
						}

						if ($threadlog = $this->_registry->_vbulletin->db->query_first("
							SELECT phpkd_vblvb_lastpunish FROM " . TABLE_PREFIX . "thread
							WHERE threadid = $threadinfo[threadid]
						") AND !empty($threadlog['phpkd_vblvb_lastpunish']))
						{
							$threadlog_arr = @unserialize($threadlog['phpkd_vblvb_lastpunish']);

							if ($threadlog_arr['close'] AND $threadinfo['open'] == 0)
							{
								$threadman =& datamanager_init('Thread', $this->_registry->_vbulletin, ERRTYPE_SILENT, 'threadpost');
								$threadman->set_info('skip_moderator_log', true);
								$threadman->set_existing($threadinfo);
								$threadman->set('open', 1);
								$threadman->save();
								unset($threadman);
							}

							if ($threadlog_arr['unstick'] AND $threadinfo['sticky'] == 0)
							{
								$threadman =& datamanager_init('Thread', $this->_registry->_vbulletin, ERRTYPE_SILENT, 'threadpost');
								$threadman->set_info('skip_moderator_log', true);
								$threadman->set_existing($threadinfo);
								$threadman->set('sticky', 1);
								$threadman->save();
								unset($threadman);
							}

							if ($threadlog_arr['moderate'] AND $threadinfo['visible'] == 0)
							{
								approve_thread($threadinfo['threadid'], $foruminfo['countposts'], false, $threadinfo);
								build_forum_counters($threadinfo['forumid']);
							}

							if ($threadlog_arr['delete'] AND $threadinfo['visible'] == 2)
							{
								undelete_thread($threadinfo['threadid'], $foruminfo['countposts'], $threadinfo);
								build_forum_counters($threadinfo['forumid']);
							}

							if ($threadlog_arr['move']['orifid'] AND $threadlog_arr['move']['destfid'])
							{
								// check whether destination forum can contain posts
								if ($destforuminfo = verify_id('forum', $threadlog_arr['move']['orifid'], false, true) AND $destforuminfo['cancontainthreads'] AND !$destforuminfo['link'])
								{
									// Ignore all threads that are already in the destination forum
									if ($threadinfo['forumid'] == $threadlog_arr['move']['destfid'])
									{
										// check to see if this thread is being returned to a forum it's already been in
										// if a redirect exists already in the destination forum, remove it
										if ($checkprevious = $this->_registry->_vbulletin->db->query_first("SELECT threadid FROM " . TABLE_PREFIX . "thread WHERE forumid = $destforuminfo[forumid] AND open = 10 AND pollid = $threadinfo[threadid]"))
										{
											$old_redirect =& datamanager_init('Thread', $this->_registry->_vbulletin, ERRTYPE_ARRAY, 'threadpost');
											$old_redirect->set_existing($checkprevious);
											$old_redirect->delete(false, true, NULL, false, false);
											unset($old_redirect);
										}

										// check to see if this thread is being moved to the same forum it's already in but allow copying to the same forum
										if ($destforuminfo['forumid'] != $threadinfo['forumid'])
										{
											// update forumid
											$threadman =& datamanager_init('Thread', $this->_registry->_vbulletin, ERRTYPE_STANDARD, 'threadpost');
											$threadman->set_info('skip_moderator_log', true);
											$threadman->set_existing($threadinfo);
											$threadman->set('forumid', $destforuminfo['forumid']);
											$threadman->save();
											unset($threadman);

											// kill the cache for the old thread
											delete_post_cache_threads(array($threadinfo['threadid']));

											// Update Post Count if we move from a counting forum to a non counting or vice-versa..
											// Source Dest  Visible Thread    Hidden Thread
											// Yes    Yes   ~           	  ~
											// Yes    No    -visible          ~
											// No     Yes   +visible          ~
											// No     No    ~                 ~
											if ($threadinfo['visible'] AND (($foruminfo['countposts'] AND !$destforuminfo['countposts']) OR (!$foruminfo['countposts'] AND $destforuminfo['countposts'])))
											{
												$posts = $this->_registry->_vbulletin->db->query_read("
													SELECT userid
													FROM " . TABLE_PREFIX . "post
													WHERE threadid = $threadinfo[threadid]
														AND	userid > 0
														AND visible = 1
												");

												$userbyuserid = array();
												while ($post = $this->_registry->_vbulletin->db->fetch_array($posts))
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

													foreach ($userbypostcount AS $postcount => $userids)
													{
														$casesql .= " WHEN userid IN (0$userids) THEN $postcount";
													}

													$operator = ($destforuminfo['countposts'] ? '+' : '-');

													$this->_registry->_vbulletin->db->query_write("
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

											build_forum_counters($threadinfo['forumid']);
											if ($threadinfo['forumid'] != $destforuminfo['forumid'])
											{
												build_forum_counters($destforuminfo['forumid']);
											}

											// Update canview status of thread subscriptions
											update_subscriptions(array('threadids' => array($threadinfo['threadid'])));
										}
									}
								}
							}

							$this->_registry->_vbulletin->db->query_write("
								UPDATE " . TABLE_PREFIX . "thread SET
									phpkd_vblvb_lastpunish = ''
								WHERE threadid = $threadinfo[threadid]
							");
						}
					}
				}
			}

			return true;
		}
	}
}


/*============================================================================*\
|| ########################################################################### ||
|| # Version: 4.1.210
|| # $Revision$
|| # Released: $Date$
|| ########################################################################### ||
\*============================================================================*/