<?php
/*==================================================================================*\
|| ################################################################################ ||
|| # Product Name: PHPKD - vB Link Verifier Bot                  Version: 3.8.100 # ||
|| # License Type: Commercial License                            $Revision: 124 $ # ||
|| # ---------------------------------------------------------------------------- # ||
|| # 																			  # ||
|| #            Copyright ©2005-2010 PHP KingDom. All Rights Reserved.            # ||
|| #      This product may not be redistributed in whole or significant part.     # ||
|| # 																			  # ||
|| # --------------- 'vB Link Verifier Bot' IS NOT FREE SOFTWARE ---------------- # ||
|| #     http://www.phpkd.net | http://info.phpkd.net/en/license/commercial       # ||
|| ################################################################################ ||
\*==================================================================================*/


if (!defined('VB_AREA'))
{
	exit;
}

$hookobj =& vBulletinHook::init();
require_once(DIR . '/includes/phpkd/vblvb/functions.php');


switch (strval($hookobj->last_called))
{
	case 'admin_options_print':
		{
			// #######################################
			if ($setting['optioncode'] == 'phpkd_vblvb_hosts')
			{
				$handled = TRUE;
				$setting['bitfield'] =& fetch_bitfield_definitions('nocache|phpkd_vblvb_hosts');
				$setting['value'] = intval($setting['value']);
				$setting['html'] = '';

				if ($setting['bitfield'] === NULL)
				{
					print_label_row($description, construct_phrase("<strong>$vbphrase[settings_bitfield_error]</strong>", implode(',', vB_Bitfield_Builder::fetch_errors())), '', 'top', $name, 40);
				}
				else
				{
					#$setting['html'] .= "<fieldset><legend>$vbphrase[yes] / $vbphrase[no]</legend>";
					$setting['html'] .= "<div id=\"ctrl_setting[$setting[varname]]\" class=\"smallfont\">\r\n";
					$setting['html'] .= "<input type=\"hidden\" name=\"setting[$setting[varname]][0]\" value=\"0\" />\r\n";
					foreach ($setting['bitfield'] AS $key => $value)
					{
						$value = intval($value);
						$setting['html'] .= "<table style=\"width:175px; float:$stylevar[left]\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr valign=\"top\">
						<td><input type=\"checkbox\" name=\"setting[$setting[varname]][$value]\" id=\"setting[$setting[varname]]_$key\" value=\"$value\"" . (($setting['value'] & $value) ? ' checked="checked"' : '') . " /></td>
						<td width=\"100%\" style=\"padding-top:4px\"><label for=\"setting[$setting[varname]]_$key\" class=\"smallfont\">" . fetch_phrase_from_key('phpkd_vblvb_hosts_' . $key) . "</label></td>\r\n</tr></table>\r\n";
					}

					$setting['html'] .= "</div>\r\n";
					#$setting['html'] .= "</fieldset>";
					print_label_row($description, $setting['html'], '', 'top', $name, 40);
				}
			}
			else if ($setting['optioncode'] == 'phpkd_vblvb_hosts2')
			{
				$handled = TRUE;
				$setting['bitfield'] =& fetch_bitfield_definitions('nocache|phpkd_vblvb_hosts2');
				$setting['value'] = intval($setting['value']);
				$setting['html'] = '';

				if ($setting['bitfield'] === NULL)
				{
					print_label_row($description, construct_phrase("<strong>$vbphrase[settings_bitfield_error]</strong>", implode(',', vB_Bitfield_Builder::fetch_errors())), '', 'top', $name, 40);
				}
				else
				{
					#$setting['html'] .= "<fieldset><legend>$vbphrase[yes] / $vbphrase[no]</legend>";
					$setting['html'] .= "<div id=\"ctrl_setting[$setting[varname]]\" class=\"smallfont\">\r\n";
					$setting['html'] .= "<input type=\"hidden\" name=\"setting[$setting[varname]][0]\" value=\"0\" />\r\n";
					foreach ($setting['bitfield'] AS $key => $value)
					{
						$value = intval($value);
						$setting['html'] .= "<table style=\"width:175px; float:$stylevar[left]\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr valign=\"top\">
						<td><input type=\"checkbox\" name=\"setting[$setting[varname]][$value]\" id=\"setting[$setting[varname]]_$key\" value=\"$value\"" . (($setting['value'] & $value) ? ' checked="checked"' : '') . " /></td>
						<td width=\"100%\" style=\"padding-top:4px\"><label for=\"setting[$setting[varname]]_$key\" class=\"smallfont\">" . fetch_phrase_from_key('phpkd_vblvb_hosts_' . $key) . "</label></td>\r\n</tr></table>\r\n";
					}

					$setting['html'] .= "</div>\r\n";
					#$setting['html'] .= "</fieldset>";
					print_label_row($description, $setting['html'], '', 'top', $name, 40);
				}
			}
			else if ($setting['optioncode'] == 'phpkd_vblvb_masks')
			{
				$handled = TRUE;
				$setting['bitfield'] =& fetch_bitfield_definitions('nocache|phpkd_vblvb_masks');
				$setting['value'] = intval($setting['value']);
				$setting['html'] = '';

				if ($setting['bitfield'] === NULL)
				{
					print_label_row($description, construct_phrase("<strong>$vbphrase[settings_bitfield_error]</strong>", implode(',', vB_Bitfield_Builder::fetch_errors())), '', 'top', $name, 40);
				}
				else
				{
					#$setting['html'] .= "<fieldset><legend>$vbphrase[yes] / $vbphrase[no]</legend>";
					$setting['html'] .= "<div id=\"ctrl_setting[$setting[varname]]\" class=\"smallfont\">\r\n";
					$setting['html'] .= "<input type=\"hidden\" name=\"setting[$setting[varname]][0]\" value=\"0\" />\r\n";
					foreach ($setting['bitfield'] AS $key => $value)
					{
						$value = intval($value);
						$setting['html'] .= "<table style=\"width:175px; float:$stylevar[left]\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr valign=\"top\">
						<td><input type=\"checkbox\" name=\"setting[$setting[varname]][$value]\" id=\"setting[$setting[varname]]_$key\" value=\"$value\"" . (($setting['value'] & $value) ? ' checked="checked"' : '') . " /></td>
						<td width=\"100%\" style=\"padding-top:4px\"><label for=\"setting[$setting[varname]]_$key\" class=\"smallfont\">" . fetch_phrase_from_key('phpkd_vblvb_masks_' . $key) . "</label></td>\r\n</tr></table>\r\n";
					}

					$setting['html'] .= "</div>\r\n";
					#$setting['html'] .= "</fieldset>";
					print_label_row($description, $setting['html'], '', 'top', $name, 40);
				}
			}
			else if ($setting['optioncode'] == 'phpkd_vblvb_rprts')
			{
				$handled = TRUE;
				$setting['bitfield'] =& fetch_bitfield_definitions('nocache|phpkd_vblvb_rprts');
				$setting['value'] = intval($setting['value']);
				$setting['html'] = '';

				if ($setting['bitfield'] === NULL)
				{
					print_label_row($description, construct_phrase("<strong>$vbphrase[settings_bitfield_error]</strong>", implode(',', vB_Bitfield_Builder::fetch_errors())), '', 'top', $name, 40);
				}
				else
				{
					#$setting['html'] .= "<fieldset><legend>$vbphrase[yes] / $vbphrase[no]</legend>";
					$setting['html'] .= "<div id=\"ctrl_setting[$setting[varname]]\" class=\"smallfont\">\r\n";
					$setting['html'] .= "<input type=\"hidden\" name=\"setting[$setting[varname]][0]\" value=\"0\" />\r\n";
					foreach ($setting['bitfield'] AS $key => $value)
					{
						$value = intval($value);
						$setting['html'] .= "<table style=\"width:175px; float:$stylevar[left]\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr valign=\"top\">
						<td><input type=\"checkbox\" name=\"setting[$setting[varname]][$value]\" id=\"setting[$setting[varname]]_$key\" value=\"$value\"" . (($setting['value'] & $value) ? ' checked="checked"' : '') . " /></td>
						<td width=\"100%\" style=\"padding-top:4px\"><label for=\"setting[$setting[varname]]_$key\" class=\"smallfont\">" . fetch_phrase_from_key('phpkd_vblvb_rprts_' . $key) . "</label></td>\r\n</tr></table>\r\n";
					}

					$setting['html'] .= "</div>\r\n";
					#$setting['html'] .= "</fieldset>";
					print_label_row($description, $setting['html'], '', 'top', $name, 40);
				}
			}
			else if ($setting['optioncode'] == 'phpkd_vblvb_rprtu')
			{
				$handled = TRUE;
				$setting['bitfield'] =& fetch_bitfield_definitions('nocache|phpkd_vblvb_rprtu');
				$setting['value'] = intval($setting['value']);
				$setting['html'] = '';

				if ($setting['bitfield'] === NULL)
				{
					print_label_row($description, construct_phrase("<strong>$vbphrase[settings_bitfield_error]</strong>", implode(',', vB_Bitfield_Builder::fetch_errors())), '', 'top', $name, 40);
				}
				else
				{
					#$setting['html'] .= "<fieldset><legend>$vbphrase[yes] / $vbphrase[no]</legend>";
					$setting['html'] .= "<div id=\"ctrl_setting[$setting[varname]]\" class=\"smallfont\">\r\n";
					$setting['html'] .= "<input type=\"hidden\" name=\"setting[$setting[varname]][0]\" value=\"0\" />\r\n";
					foreach ($setting['bitfield'] AS $key => $value)
					{
						$value = intval($value);
						$setting['html'] .= "<table style=\"width:175px; float:$stylevar[left]\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr valign=\"top\">
						<td><input type=\"checkbox\" name=\"setting[$setting[varname]][$value]\" id=\"setting[$setting[varname]]_$key\" value=\"$value\"" . (($setting['value'] & $value) ? ' checked="checked"' : '') . " /></td>
						<td width=\"100%\" style=\"padding-top:4px\"><label for=\"setting[$setting[varname]]_$key\" class=\"smallfont\">" . fetch_phrase_from_key('phpkd_vblvb_rprtu_' . $key) . "</label></td>\r\n</tr></table>\r\n";
					}

					$setting['html'] .= "</div>\r\n";
					#$setting['html'] .= "</fieldset>";
					print_label_row($description, $setting['html'], '', 'top', $name, 40);
				}
			}
			else if ($setting['optioncode'] == 'phpkd_vblvb_punish')
			{
				$handled = TRUE;
				$setting['bitfield'] =& fetch_bitfield_definitions('nocache|phpkd_vblvb_punish');
				$setting['value'] = intval($setting['value']);
				$setting['html'] = '';

				if ($setting['bitfield'] === NULL)
				{
					print_label_row($description, construct_phrase("<strong>$vbphrase[settings_bitfield_error]</strong>", implode(',', vB_Bitfield_Builder::fetch_errors())), '', 'top', $name, 40);
				}
				else
				{
					#$setting['html'] .= "<fieldset><legend>$vbphrase[yes] / $vbphrase[no]</legend>";
					$setting['html'] .= "<div id=\"ctrl_setting[$setting[varname]]\" class=\"smallfont\">\r\n";
					$setting['html'] .= "<input type=\"hidden\" name=\"setting[$setting[varname]][0]\" value=\"0\" />\r\n";
					foreach ($setting['bitfield'] AS $key => $value)
					{
						$value = intval($value);
						$setting['html'] .= "<table style=\"width:175px; float:$stylevar[left]\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr valign=\"top\">
						<td><input type=\"checkbox\" name=\"setting[$setting[varname]][$value]\" id=\"setting[$setting[varname]]_$key\" value=\"$value\"" . (($setting['value'] & $value) ? ' checked="checked"' : '') . " /></td>
						<td width=\"100%\" style=\"padding-top:4px\"><label for=\"setting[$setting[varname]]_$key\" class=\"smallfont\">" . fetch_phrase_from_key('phpkd_vblvb_punish_' . $key) . "</label></td>\r\n</tr></table>\r\n";
					}

					$setting['html'] .= "</div>\r\n";
					#$setting['html'] .= "</fieldset>";
					print_label_row($description, $setting['html'], '', 'top', $name, 40);
				}
			}
			else if ($setting['optioncode'] == 'phpkd_vblvb_forums')
			{
				$handled = TRUE;
				$vbphrase['select_forum'] = $vbphrase['phpkd_vblvb_select_forums'];
				$options = construct_forum_chooser_options(1);

				// eval($setting['optiondata']);

				if (is_array($options) AND !empty($options))
				{
					print_select_row($description, $name . '[]', $options, unserialize($setting['value']), FALSE, 8, TRUE);
				}
				else
				{
					print_input_row($description, $name, $setting['value']);
				}
			}
			else if ($setting['optioncode'] == 'phpkd_vblvb_usergroups')
			{
				$handled = TRUE;
				$usergrouplist = array();
				foreach ($vbulletin->usergroupcache AS $usergroup)
				{
					$usergrouplist["$usergroup[usergroupid]"] = $usergroup['title'];
				}

				if (is_array($usergrouplist) AND !empty($usergrouplist))
				{
					print_select_row($description, $name . '[]', array(0 => $vbphrase['phpkd_vblvb_select_usergroups']) + $usergrouplist, unserialize($setting['value']), FALSE, 8, TRUE);
				}
				else
				{
					print_input_row($description, $name, $setting['value']);
				}
			}
		}
		break;
	case 'admin_options_processing':
		{
			// ######################################
			// phpkd_vblvb_forums
			if ($oldsetting['optioncode'] == 'phpkd_vblvb_forums')
			{
				if (count($settings["$oldsetting[varname]"]) > 1 AND $settings["$oldsetting[varname]"][0] == 0)
				{
					unset($settings["$oldsetting[varname]"][0]);
				}

				$settings["$oldsetting[varname]"] = serialize($settings["$oldsetting[varname]"]);
			}
			else if ($oldsetting['optioncode'] == 'phpkd_vblvb_usergroups')
			{
				if (count($settings["$oldsetting[varname]"]) > 1 AND $settings["$oldsetting[varname]"][0] == 0)
				{
					unset($settings["$oldsetting[varname]"][0]);
				}

				// serialize the array of usergroup inputs
				if (!is_array($settings["$oldsetting[varname]"]))
				{
					 $settings["$oldsetting[varname]"] = array();
				}

				$settings["$oldsetting[varname]"] = array_map('intval', $settings["$oldsetting[varname]"]);
				$settings["$oldsetting[varname]"] = serialize($settings["$oldsetting[varname]"]);
			}
		}
		break;
	case 'editpost_update_process':
		{
			// ####################################
			if ($vbulletin->options['phpkd_vblvb_active'])
			{
				if ($vbulletin->options['phpkd_vblvb_checked_edited'] == 1 OR ($threadinfo['firstpostid'] == $postinfo['postid'] AND $vbulletin->options['phpkd_vblvb_checked_edited'] == 2))
				{
					$phpkd_vblvb_pass = FALSE;

					// ... Licensing ... //
					$phpkd_vblvb_license_proceed = FALSE;
					$phpkd_commercial38_license = @unserialize($vbulletin->options['phpkd_commercial38_license']);
					if ($phpkd_commercial38_license['vblvb']['lc'] > (TIMENOW - 86400))
					{
						$phpkd_vblvb_license_proceed = TRUE;
					}
					else if (phpkd_vblvb_license())
					{
						$phpkd_vblvb_license_proceed = TRUE;

						$phpkd_commercial38_license['vblvb']['lc'] = TIMENOW;
						$db->query_write("UPDATE " . TABLE_PREFIX . "setting SET value = '" . @serialize($phpkd_commercial38_license) . "' WHERE varname = 'phpkd_commercial38_license'");

						require_once(DIR . '/includes/adminfunctions.php');
						build_options();
					}
					// ... Licensing ... //


					if ($phpkd_vblvb_license_proceed AND !in_array($vbulletin->userinfo['usergroupid'], explode(' ', $vbulletin->options['phpkd_vblvb_powerful_ugids'])))
					{
						$phpkd_vblvb_fetch_urls = phpkd_vblvb_fetch_urls($edit['message']);

						// Critical Limit/Red Line
						if ($phpkd_vblvb_fetch_urls['dead'] > 0 AND $phpkd_vblvb_fetch_urls['checked'] > 0)
						{
							$phpkd_vblvb_critical = ($phpkd_vblvb_fetch_urls['dead'] / $phpkd_vblvb_fetch_urls['checked']) * 100;
							if ($phpkd_vblvb_critical > $vbulletin->options['phpkd_vblvb_critical'])
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


					if ($phpkd_vblvb_pass AND $vbulletin->options['phpkd_vblvb_revert'] AND $phpkd_vblvb_logged = $db->query_first_slave("
						SELECT phpkd_vblvb FROM " . TABLE_PREFIX . "post
						WHERE postid = $postinfo[postid]
					"))
					{
						require_once(DIR . '/includes/functions_databuild.php');

						if ($phpkd_vblvb_logged['phpkd_vblvb'])
						{
							$phpkd_vblvb_punish_move = FALSE;
							$phpkd_vblvb_arr = unserialize($phpkd_vblvb_logged['phpkd_vblvb']);
							if ($phpkd_vblvb_arr['move'][0])
							{
								$destforumid = verify_id('forum', $phpkd_vblvb_arr['move']['orifid']);
								$destforuminfo = fetch_foruminfo($destforumid);
								if ($destforuminfo['cancontainthreads'] AND !$destforuminfo['link'])
								{
									$phpkd_vblvb_punish_move = TRUE;
								}
							}


							if ($phpkd_vblvb_arr['moderate'] AND $threadinfo['visible'] == 0)
							{
								approve_thread($threadinfo['threadid'], $foruminfo['countposts'], FALSE, $threadinfo);
							}


							if ($phpkd_vblvb_arr['close'] AND $threadinfo['open'] == 0)
							{
								$threadman =& datamanager_init('Thread', $vbulletin, ERRTYPE_STANDARD, 'threadpost');
								$threadman->set_info('skip_moderator_log', true);
								$threadman->set_existing($threadinfo);
								$threadman->set('open', 1);
								$threadman->save();
								unset($threadman);
							}


							if ($phpkd_vblvb_arr['delete'] AND $threadinfo['visible'] == 2)
							{
								undelete_thread($puthreadinfo['threadid'], $puforuminfo['countposts'], $puthreadinfo);
							}


							if ($phpkd_vblvb_punish_move AND $threadinfo['forumid'] == $phpkd_vblvb_arr['move']['destfid'])
							{
								// check to see if this thread is being returned to a forum it's already been in
								// if a redirect exists already in the destination forum, remove it
								if ($checkprevious = $db->query_first_slave("SELECT threadid FROM " . TABLE_PREFIX . "thread WHERE forumid = $destforuminfo[forumid] AND open = 10"))
								{
									$old_redirect =& datamanager_init('Thread', $vbulletin, ERRTYPE_ARRAY, 'threadpost');
									$old_redirect->set_existing($checkprevious);
									$old_redirect->delete(false, true, NULL, false);
									unset($old_redirect);
								}

								// check to see if this thread is being moved to the same forum it's already in but allow copying to the same forum
								if ($destforuminfo['forumid'] != $threadinfo['forumid'])
								{
									// update forumid/notes and unstick to prevent abuse
									$threadman =& datamanager_init('Thread', $vbulletin, ERRTYPE_STANDARD, 'threadpost');
									$threadman->set_info('skip_moderator_log', true);
									$threadman->set_existing($threadinfo);
									$threadman->set('title', $threadinfo['title'], true, false);
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
										$phpkd_vblvb_posts = $db->query_read_slave("
											SELECT userid
											FROM " . TABLE_PREFIX . "post
											WHERE threadid = $threadinfo[threadid]
												AND	userid > 0
												AND visible = 1
										");

										$userbyuserid = array();
										while ($post = $db->fetch_array($phpkd_vblvb_posts))
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

											$db->query_write("
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


							$db->query_write("
								UPDATE " . TABLE_PREFIX . "post SET
									phpkd_vblvb = ''
								WHERE postid = $postinfo[postid]
							");
						}
					}
				}
			}
		}
		break;
	case 'newpost_process':
		{
			// ####################################
			if ($vbulletin->options['phpkd_vblvb_active'])
			{
				if (($type == 'thread' AND ($vbulletin->options['phpkd_vblvb_checked_new'] == 1 OR $vbulletin->options['phpkd_vblvb_checked_new'] == 2)) OR $type == 'reply' AND $vbulletin->options['phpkd_vblvb_checked_new'] == 1)
				{
					// ... Licensing ... //
					$phpkd_vblvb_license_proceed = FALSE;
					$phpkd_commercial38_license = @unserialize($vbulletin->options['phpkd_commercial38_license']);
					if ($phpkd_commercial38_license['vblvb']['lc'] > (TIMENOW - 86400))
					{
						$phpkd_vblvb_license_proceed = TRUE;
					}
					else if (phpkd_vblvb_license())
					{
						$phpkd_vblvb_license_proceed = TRUE;

						$phpkd_commercial38_license['vblvb']['lc'] = TIMENOW;
						$db->query_write("UPDATE " . TABLE_PREFIX . "setting SET value = '" . @serialize($phpkd_commercial38_license) . "' WHERE varname = 'phpkd_commercial38_license'");

						require_once(DIR . '/includes/adminfunctions.php');
						build_options();
					}
					// ... Licensing ... //


					if ($phpkd_vblvb_license_proceed AND !in_array($vbulletin->userinfo['usergroupid'], explode(' ', $vbulletin->options['phpkd_vblvb_powerful_ugids'])))
					{
						$phpkd_vblvb_fetch_urls = phpkd_vblvb_fetch_urls($post['message']);

						// Critical Limit/Red Line
						if ($phpkd_vblvb_fetch_urls['dead'] > 0 AND $phpkd_vblvb_fetch_urls['checked'] > 0)
						{
							$phpkd_vblvb_critical = ($phpkd_vblvb_fetch_urls['dead'] / $phpkd_vblvb_fetch_urls['checked']) * 100;
							if ($phpkd_vblvb_critical > $vbulletin->options['phpkd_vblvb_critical'])
							{
								$dataman->error('phpkd_vblvb_newpost');
							}
						}
					}
				}
			}
		}
		break;
	default:
		{
			$hookobj = new vBulletinHook_PHPKD_VBLVB($hookobj->pluginlist, $hookobj->hookusage);
		}
		break;
}


/*============================================================================*\
|| ########################################################################### ||
|| # Version: 3.8.100
|| # $Revision: 124 $
|| # Released: $Date: 2008-07-22 07:23:25 +0300 (Tue, 22 Jul 2008) $
|| ########################################################################### ||
\*============================================================================*/
?>