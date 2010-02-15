<?php
/*==================================================================================*\
|| ################################################################################ ||
|| # Product Name: PHPKD - vB Link Verifier Bot                  Version: 4.0.130 # ||
|| # License Type: Commercial License                            $Revision$ # ||
|| # ---------------------------------------------------------------------------- # ||
|| # 																			  # ||
|| #            Copyright ©2005-2010 PHP KingDom. All Rights Reserved.            # ||
|| #      This product may not be redistributed in whole or significant part.     # ||
|| # 																			  # ||
|| # --------------- 'vB Link Verifier Bot' IS NOT FREE SOFTWARE ---------------- # ||
|| #     http://www.phpkd.net | http://info.phpkd.net/en/license/commercial       # ||
|| ################################################################################ ||
\*==================================================================================*/


// No direct access! Should be accessed throuth the core class only!!
if (!defined('VB_AREA') OR !defined('PHPKD_VBLVB') OR @get_class($this) != 'PHPKD_VBLVB')
{
	echo 'Prohibited Access!';
	exit;
}

// require_once(DIR . '/includes/phpkd/vblvb/class_core.php');


/**
 * Data Manager class
 *
 * @package vB Link Verifier Bot 'Pro' Edition
 * @author PHP KingDom Development Team
 * @version $Revision$
 * @since $Date$
 * @copyright PHP KingDom (PHPKD)
 */
class PHPKD_VBLVB_DM extends PHPKD_VBLVB
{
	/**
	* Constructor - Registers passed (pre-prepared!) values to the current object handler
	*
	* @param	vB_Registry	Instance of the vBulletin data registry object - expected to have the database object as one of its members ($this->db).
	* @param	array		Initialize required data (Hosts/Masks/Punishments/Reports)
	* @param	integer		One of the ERRTYPE_x constants
	*/
	function PHPKD_VBLVB_DM(&$registry, &$parent)
	{
		$this->registry =& $registry;

		$this->vbphrase      = $parent->vbphrase;
		$this->error_handler = $parent->error_handler;
		$this->hosts         = $parent->hosts;
		$this->masks         = $parent->masks;
		$this->protocols     = $parent->protocols;
		$this->bbcodes       = $parent->bbcodes;
		$this->punishments   = $parent->punishments;
		$this->staff_reports = $parent->staff_reports;
		$this->user_reports  = $parent->user_reports;
		$this->threadmodes   = $parent->threadmodes;
		$this->postmodes     = $parent->postmodes;
		//parent::PHPKD_VBLVB($registry, $initparams);
		// Do nothing!!
	}


	/**
	* Extract links from text, pass it to be verified & return log records
	*
	* @param	string	Message text
	*
	* @return	array	Returns array of numbers determines how many links (all/checked/alive/dead/down) in addition to log records
	*/
	function fetch_urls($messagetext)
	{
		$tmpbbcodes1 = $tmpbbcodes2 = array();
		foreach ($this->bbcodes AS $key => $value)
		{
			if ($key != 'LINK')
			{
				foreach ($value AS $ky => $val)
				{
					switch ($ky)
					{
						case 'open':
							$tmpbbcodes1[] = $value['open'];
							break;
						case 'close':
							if ($key != 'LIST')
							{
								$tmpbbcodes2[] = $value['close'];
							}
							break;
					}
				}
			}
		}


		if (!empty($tmpbbcodes1) AND !empty($tmpbbcodes2))
		{
			$taglist = implode('|', $tmpbbcodes1) . implode('|', $tmpbbcodes2);
			$regex1 = '#(^|(?<=[^_a-z0-9-=\]"\'/@]|(?<=' . $taglist . ')\]))((' . implode('|', $this->protocols) . ')://|www\.)((\[(?!/)|[^\s[^$`"{}<>])+)(?!\[/url|\[/img)(?=[,.!\')]*(\)\s|\)$|[\s[]|$))#siU';
			preg_match_all($regex1, $messagetext, $matches1);

			if (is_array($matches1) AND !empty($matches1))
			{
				unset($matches1[1], $matches1[2], $matches1[3], $matches1[4], $matches1[5], $matches1[6]);
			}
		}

		if (isset($this->bbcodes['LINK']))
		{
			$regex2 = '#\[url=("|\'|)?(.*)\\1\](?:.*)\[/url\]|\[url\](.*)\[/url\]#siU';
			preg_match_all($regex2, $messagetext, $matches2);

			if (is_array($matches2) AND !empty($matches2))
			{
				unset($matches2[0], $matches2[1]);
			}
		}


		if ((is_array($matches1) AND !empty($matches1)) AND (is_array($matches2) AND !empty($matches2)))
		{
			$matches = array_merge($matches1, $matches2);
		}
		else if ((is_array($matches1) AND !empty($matches1)) AND (!is_array($matches2) OR empty($matches2)))
		{
			$matches = $matches1;
		}
		else if ((!is_array($matches1) OR empty($matches1)) AND (is_array($matches2) AND !empty($matches2)))
		{
			$matches = $matches2;
		}
		else
		{
			$this->error('phpkd_vblvb_invalid_criteria');
			return array('all' => 0, 'checked' => 0, 'alive' => 0, 'dead' => 0, 'down' => 0, 'log' => '<br />' . $this->vbphrase['phpkd_vblvb_invalid_criteria'] . '<br />');
		}


		if (is_array($matches) AND !empty($matches))
		{
			$actualurls = array();
			foreach ($matches AS $key => $value)
			{
				foreach ($value AS $singleurl)
				{
					if ($singleurl != '')
					{
						$actualurls[] = $singleurl;
					}
				}
			}
		}
		else
		{
			$this->error('phpkd_vblvb_invalid_criteria');
			return array('all' => 0, 'checked' => 0, 'alive' => 0, 'dead' => 0, 'down' => 0, 'log' => '<br />' . $this->vbphrase['phpkd_vblvb_invalid_criteria'] . '<br />');
		}


		if (is_array($actualurls) AND !empty($actualurls))
		{
			$log = '';
			$counter = 0;
			$return = array();


			if (defined('IN_CONTROL_PANEL'))
			{
				echo '<ol>';
				vbflush();
			}

			foreach(array_unique($actualurls) AS $url)
			{
				if ($this->registry->options['phpkd_vblvb_maxlinks'] > 0 AND $counter >= $this->registry->options['phpkd_vblvb_maxlinks'])
				{
					continue;
				}

				if (!empty($url))
				{
					// Detect masked links& unmask it!
					$url = $this->unmask($url);

					// Process Available Hosts
					foreach($this->hosts AS $host)
					{
						if(preg_match("#$host[urlmatch]#i", $url))
						{
							$return[] = $this->check(trim($url), $host['status'], $host['contentmatch'], $host['urlsearch'], $host['urlreplace'], $host['downmatch']);
						}
					}

					$counter++;
				}
			}

			if (defined('IN_CONTROL_PANEL'))
			{
				echo '</ol>';
				vbflush();
			}


			$log .= '<ol>';
			$alive = $dead = $down = 0;
			foreach ($return AS $rtrn)
			{
				switch ($rtrn['status'])
				{
					case 'alive':
						$alive++;
						break;
					case 'dead':
						$dead++;
						break;
					case 'down':
						$down++;
						break;
				}

				$log .= $rtrn['log'];
			}
			$log .= '</ol>';

			return array('all' => intval($counter), 'checked' => intval($alive + $dead + $down), 'alive' => intval($alive), 'dead' => intval($dead), 'down' => intval($down), 'log' => $log);
		}
		else
		{
			$this->error('phpkd_vblvb_invalid_criteria');
			return array('all' => 0, 'checked' => 0, 'alive' => 0, 'dead' => 0, 'down' => 0, 'log' => '<br />' . $this->vbphrase['phpkd_vblvb_invalid_criteria'] . '<br />');
		}
	}


	/**
	* Returns unmasked URL
	*
	* @param	string	URL to be umasked
	*
	* @return	string	unmasked URL
	*/
	function unmask($url)
	{
		static $recursive;
		if (!$recursive)
		{
			$recursive = 0;
		}

		if (is_array($this->masks) AND !empty($this->masks))
		{
			foreach ($this->masks AS $maskid => $maskregex)
			{
				if (preg_match($maskregex, $url))
				{
					switch ($maskid)
					{
						case 'anonym.to':
							$url = explode('?', $url);
							unset($url[0]);
							$url = implode($url, '?');
							break;
						case 'lix.in':
							$curlpost = 'tiny=' . trim(substr(strstr($url, 'n/'), 2)) . '&submit=continue';
							preg_match('@name="ifram" src="(.+?)"@i', $this->vurl($url, $curlpost), $match);
							$url = $match[1];
							break;
						case 'linkbucks.com':
							$page = $this->vurl($url);
							preg_match("/<a href=\"(.+)\" id=\"aSkipLink\">/", $page, $match);
							$url = $match[1];
							break;
					}
				}
			}

			if ($this->registry->options['phpkd_vblvb_masks_recursion'])
			{
				$recursive++;

				if ($this->registry->options['phpkd_vblvb_masks_recursion_level'] == 0 OR ($recursive <= $this->registry->options['phpkd_vblvb_masks_recursion_level']))
				{
					if (preg_match($maskregex, $url))
					{
						return $this->unmask($url);
					}
					else
					{
						return $url;
					}
				}
				else
				{
					return $url;
				}
			}
			else
			{
				return $url;
			}
		}
		else
		{
			return $url;
		}
	}


	/**
	* Returns content of the remotely fetched page
	*
	* @param	string	URL to be remotely fetched
	* @param	string	Posted fields (string as query string, or as array)
	*
	* @return	string	Page Content
	*/
	function vurl($url, $post = '0')
	{
		require_once(DIR . '/includes/class_vurl.php');

		$vurl = new vB_vURL($this->registry);
		$vurl->set_option(VURL_URL, $url);
		$vurl->set_option(VURL_USERAGENT, 'vBulletin/' . FILE_VERSION);
		$vurl->set_option(VURL_FOLLOWLOCATION, 1);
		$vurl->set_option(VURL_MAXREDIRS, 1);

		if($post != '0') 
		{
			$vurl->set_option(VURL_POST, 1);
			$vurl->set_option(VURL_POSTFIELDS, $post);
		}

		$vurl->set_option(VURL_RETURNTRANSFER, 1);
		$vurl->set_option(VURL_CLOSECONNECTION, 1);
		return $vurl->exec();
	}


	/**
	* Verify if the supplied link is (alive/dead/down) & return a report about it.
	*
	* @param	string	Link to be checked
	* @param	string	Per Link Regex formula to be evaluated
	* @param	string	Regex search patern to be applied on the supplied link -if required-
	* @param	string	Regex replace patern to be applied on the supplied link -if required-
	*
	* @return	array	Checked link status & report
	*/
	function check($url, $status, $contentmatch, $urlsearch, $urlreplace, $downmatch)
	{
		$colors = unserialize($this->registry->options['phpkd_vblvb_linkstatus_colors']);

		if ($status == 'alive')
		{
			if(!empty($urlsearch)) 
			{
				$url = preg_replace($urlsearch, $urlreplace, $url);
			}
	
			$page = $this->vurl($url);
			$url = htmlentities($url, ENT_QUOTES);
	
	
			if($contentmatch != '' AND preg_match("#$contentmatch#i", $page)) 
			{
				$status = 'alive';
				$log = construct_phrase($this->vbphrase['phpkd_vblvb_log_link_alive'], $colors[0], $url);
			}
			else if($downmatch != '' AND preg_match("#$downmatch#i", $page)) 
			{
				$status = 'down';
				$log = construct_phrase($this->vbphrase['phpkd_vblvb_log_link_down'], $colors[2], $url);
			}
			else 
			{
				$status = 'dead';
				$log = construct_phrase($this->vbphrase['phpkd_vblvb_log_link_dead'], $colors[1], $url);
			}
		}
		else
		{
			$status = 'dead';
			$log = construct_phrase($this->vbphrase['phpkd_vblvb_log_link_dead'], $colors[1], $url);
		}


		if (defined('IN_CONTROL_PANEL'))
		{
			echo $log;
			vbflush();
		}


		return array('status' => $status, 'log' => $log);
	}


	/**
	* Staff Reports
	*
	* @param	string	Report content to be sent to staff members
	*
	* @return	boolean	True on success
	*/
	function staff_reports($log)
	{
		if ($this->registry->options['phpkd_vblvb_reporter'] AND $reporter = fetch_userinfo($this->registry->options['phpkd_vblvb_reporter']) AND $mods = $this->fetch_staff())
		{
			require_once(DIR . '/includes/functions_wysiwyg.php');
			$formatedlog = convert_wysiwyg_html_to_bbcode($log);

			$datenow = vbdate($this->registry->options['dateformat'], TIMENOW);
			$timenow = vbdate($this->registry->options['timeformat'], TIMENOW);


			foreach ($this->staff_reports AS $rprtsid => $rprts)
			{
				if ($this->registry->options['phpkd_vblvb_rprts'] & $rprts)
				{
					switch ($rprtsid)
					{
						// Staff Reports: Send Private Messages
						case 'RPRTS_PM':
							if (is_array($mods) AND !empty($mods))
							{
								foreach ($mods AS $mod)
								{
									if (!empty($mod['username']))
									{
										cache_permissions($reporter, false);

										// create the DM to do error checking and insert the new PM
										$pmdm =& datamanager_init('PM', $this->registry, ERRTYPE_SILENT);
										$pmdm->set_info('is_automated', true);
										$pmdm->set('fromuserid', $reporter['userid']);
										$pmdm->set('fromusername', $reporter['username']);
										$pmdm->set_info('receipt', false);
										$pmdm->set_info('savecopy', false);
										$pmdm->set('title', construct_phrase($this->vbphrase['phpkd_vblvb_rprts_title'], $datenow, $timenow));
										$pmdm->set('message', construct_phrase($this->vbphrase['phpkd_vblvb_rprts_message'], $formatedlog));
										$pmdm->set_recipients(unhtmlspecialchars($mod['username']), $reporter['permissions']);
										$pmdm->set('dateline', TIMENOW);
										$pmdm->set('allowsmilie', true);

										$pmdm->pre_save();
										if (empty($pmdm->errors))
										{
											$pmdm->save();
										}
										unset($pmdm);
									}
								}
							}
							break;

						// Staff Reports: Send E-Mails
						case 'RPRTS_EMAIL':
							if ($this->registry->options['enableemail'])
							{
								if (is_array($mods) AND count($mods) > 0)
								{
									require_once(DIR . '/includes/class_bbcode_alt.php');
									$plaintext_parser = new vB_BbCodeParser_PlainText($this->registry, fetch_tag_list());
									$plaintext_parser->set_parsing_language('1');
									$plaintextlog = $plaintext_parser->parse($formatedlog);

									foreach ($mods AS $mod)
									{
										if (!empty($mod['email']))
										{
											$email_langid = ($mod['languageid'] > 0 ? $mod['languageid'] : $this->registry->options['languageid']);
											eval(fetch_email_phrases('phpkd_vblvb_rprts_email', $email_langid));
											vbmail($mod['email'], $subject, $message, true);
										}
									}

									unset($plaintext_parser);
								}
							}
							break;

						// Staff Reports: Post New Reply
						case 'RPRTS_REPLY':
							if ($this->registry->options['phpkd_vblvb_report_tid'] > 0 AND $reportthread = fetch_threadinfo($this->registry->options['phpkd_vblvb_report_tid']) AND !$reportthread['isdeleted'] AND $reportthread['visible'] == 1  AND $reportforum = fetch_foruminfo($reportthread['forumid']))
							{
								$postman =& datamanager_init('Post', $this->registry, ERRTYPE_STANDARD, 'threadpost');
								$postman->set_info('thread', $reportthread);
								$postman->set_info('forum', $reportforum);
								$postman->set_info('is_automated', true);
								$postman->set_info('parseurl', true);
								$postman->set('threadid', $reportthread['threadid']);
								$postman->set('userid', $reporter['userid']);
								$postman->set('allowsmilie', true);
								$postman->set('visible', true);
								$postman->set('title', construct_phrase($this->vbphrase['phpkd_vblvb_rprts_title'], $datenow, $timenow));
								$postman->set('pagetext', construct_phrase($this->vbphrase['phpkd_vblvb_rprts_message'], $formatedlog));

								// not posting as the current user, IP won't make sense
								$postman->set('ipaddress', '');

								$postman->save();
								unset($postman);
							}
							break;

						// Staff Reports: Post New Thread
						case 'RPRTS_THREAD':
							if ($this->registry->options['phpkd_vblvb_report_fid'] > 0 AND $reportforum = fetch_foruminfo($this->registry->options['phpkd_vblvb_report_fid']))
							{
								$threadman =& datamanager_init('Thread_FirstPost', $this->registry, ERRTYPE_SILENT, 'threadpost');
								$threadman->set_info('forum', $reportforum);
								$threadman->set_info('is_automated', true);
								$threadman->set_info('skip_moderator_email', true);
								$threadman->set_info('mark_thread_read', true);
								$threadman->set_info('parseurl', true);
								$threadman->set('allowsmilie', true);
								$threadman->set('userid', $reporter['userid']);
								$threadman->setr_info('user', $reporter);
								$threadman->set('title', construct_phrase($this->vbphrase['phpkd_vblvb_rprts_title'], $datenow, $timenow));
								$threadman->set('pagetext', construct_phrase($this->vbphrase['phpkd_vblvb_rprts_message'], $formatedlog));
								$threadman->set('forumid', $reportforum['forumid']);
								$threadman->set('visible', 1);

								// not posting as the current user, IP won't make sense
								$threadman->set('ipaddress', '');

								if ($rpthreadid = $threadman->save())
								{
									$threadman->set_info('skip_moderator_email', false);
									$threadman->email_moderators(array('newthreademail', 'newpostemail'));

									// check the permission of the posting user
									$userperms = fetch_permissions($reportforum['forumid'], $reporter['userid'], $reporter);
									if (($userperms & $this->registry->bf_ugp_forumpermissions['canview']) AND ($userperms & $this->registry->bf_ugp_forumpermissions['canviewthreads']) AND $reporter['autosubscribe'] != -1)
									{
										$this->registry->db->query_write("
											INSERT IGNORE INTO " . TABLE_PREFIX . "subscribethread
												(userid, threadid, emailupdate, folderid, canview)
											VALUES
												(" . $reporter['userid'] . ", $rpthreadid, $reporter[autosubscribe], 0, 1)
										");
									}
								}

								unset($threadman);
							}
							break;
					}
				}
			}

			// It's OK! Return true for success
			return TRUE;
		}
	}


	/**
	* User Reports
	*
	* @param	string	Report content to be sent to post author
	*
	* @return	boolean	True on success
	*/
	function user_reports($punished)
	{
		if ($this->registry->options['phpkd_vblvb_reporter'] AND $reporter = fetch_userinfo($this->registry->options['phpkd_vblvb_reporter']))
		{
			$datenow = vbdate($this->registry->options['dateformat'], TIMENOW);
			$timenow = vbdate($this->registry->options['timeformat'], TIMENOW);

			foreach ($this->user_reports AS $rprtuid => $rprtu)
			{
				if ($this->registry->options['phpkd_vblvb_rprtu'] & $rprtu)
				{
					switch ($rprtuid)
					{
						// User Reports: Send Private Messages
						case 'RPRTU_PM':
							foreach ($punished AS $userid => $user)
							{
								$formatedlog = '[LIST=1]';
								foreach ($user AS $postid => $post)
								{
									if (!$user['username'])
									{
										$user['username'] = $post['username'];
									}

									$formatedlog .= '[*][url=' . $this->registry->options['bburl'] . '/showpost.php?p=' . $post['postid'] . ']' . ($post['title'] ? $post['title'] : $post['threadtitle']) . '[/url]';
								}
								$formatedlog .= '[/LIST]';

								if (!empty($user['username']))
								{
									cache_permissions($reporter, false);

									// create the DM to do error checking and insert the new PM
									$pmdm =& datamanager_init('PM', $this->registry, ERRTYPE_SILENT);
									$pmdm->set_info('is_automated', true);
									$pmdm->set('fromuserid', $reporter['userid']);
									$pmdm->set('fromusername', $reporter['username']);
									$pmdm->set_info('receipt', false);
									$pmdm->set_info('savecopy', false);
									$pmdm->set('title', construct_phrase($this->vbphrase['phpkd_vblvb_rprtu_title'], $datenow, $timenow));
									$pmdm->set('message', construct_phrase($this->vbphrase['phpkd_vblvb_rprtu_message'], $user['username'], $formatedlog, $this->registry->options['bburl'] . '/' . $this->registry->options['contactuslink'], $this->registry->options['bbtitle']));
									$pmdm->set_recipients(unhtmlspecialchars($user['username']), $reporter['permissions']);
									$pmdm->set('dateline', TIMENOW);
									$pmdm->set('allowsmilie', true);

									$pmdm->pre_save();
									if (empty($pmdm->errors))
									{
										$pmdm->save();
									}
									unset($pmdm);
								}
							}
							break;

						// User Reports: Send E-Mails
						case 'RPRTU_EMAIL':
							if ($this->registry->options['enableemail'])
							{
								foreach ($punished AS $userid => $user)
								{
									$plaintextlog = '';
									foreach ($user AS $postid => $post)
									{
										$email = ($user['email'] ? $user['email'] : $post['email']);
										$username = ($user['username'] ? $user['username'] : $post['username']);
										$languageid = ($user['languageid'] ? $user['languageid'] : $post['languageid']);
					
										$plaintextlog .= '* ' . ($post['title'] ? $post['title'] : $post['threadtitle']) . ': ' . $this->registry->options['bburl'] . '/showpost.php?p=' . $post['postid'] . "\n";
									}
									$plaintextlog .= '';

									$contactuslink = $this->registry->options['bburl'] . '/' . $this->registry->options['contactuslink'];
									$bbtitle = $this->registry->options['bbtitle'];

									if (!empty($email))
									{
										$email_langid = ($languageid > 0 ? $languageid : $this->registry->options['languageid']);
										eval(fetch_email_phrases('phpkd_vblvb_rprtu_email', $email_langid));
										vbmail($email, $subject, $message, true);
									}
								}
							}
							break;
					}
				}
			}

			// It's OK! Return true for success
			return TRUE;
		}
	}


	/**
	* Punish bad posts
	*
	* @param	array	Posts to be punished
	*
	* @return	boolean	True on success
	*/
	function punish($punished)
	{
		if (is_array($this->punishments) AND !empty($this->punishments))
		{
			require_once(DIR . '/includes/phpkd/vblvb/functions_databuild.php');


			foreach ($punished AS $userid => $user)
			{
				foreach ($user AS $postid => $post)
				{
					$logpunish = array();
					$punishedpost   = fetch_postinfo($post['postid']);
					$punishedthread = fetch_threadinfo($post['threadid']);
					$punishedforum  = fetch_foruminfo($post['forumid']);


					foreach ($this->punishments AS $punishment)
					{
						switch ($punishment)
						{
							case 'PUNISH_MODERATE':
								$logpunish['moderate'] = TRUE;
								phpkd_vblvb_unapprove_thread($punishedthread['threadid'], $punishedforum['countposts'], FALSE, $punishedthread);
								break;


							case 'PUNISH_CLOSE':
								$logpunish['close'] = TRUE;
								$threadman =& datamanager_init('Thread', $this->registry, ERRTYPE_STANDARD, 'threadpost');
								$threadman->set_info('skip_moderator_log', true);
								$threadman->set_existing($punishedthread);
								$threadman->set('open', 0);
								$threadman->save();
								unset($threadman);
								break;


							case 'PUNISH_MOVE':
								if ($this->registry->options['phpkd_vblvb_punish_fid'] > 0)
								{
									if ($destforum = verify_id('forum', $this->registry->options['phpkd_vblvb_punish_fid'], FALSE, TRUE))
									{
										if ($destforum['cancontainthreads'] AND !$destforum['link'])
										{
											$logpunish['move'] = array('orifid' => $punishedforum['forumid'], 'destfid' => $destforum['forumid']);

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
											if ($destforum['forumid'] == $punishedforum['forumid'])
											{
												continue;
											}

											// update forumid/notes and unstick to prevent abuse
											$threadman =& datamanager_init('Thread', $this->registry, ERRTYPE_STANDARD, 'threadpost');
											$threadman->set_info('skip_moderator_log', true);
											$threadman->set_existing($punishedthread);
											$threadman->set('title', $punishedthread['title'], true, false);
											$threadman->set('forumid', $destforum['forumid']);
											$threadman->save();
											unset($threadman);

											// kill the cache for the old thread
											phpkd_vblvb_delete_post_cache_threads(array($punishedthread['threadid']));

											// Update Post Count if we move from a counting forum to a non counting or vice-versa..
											// Source Dest  Visible Thread    Hidden Thread
											// Yes    Yes   ~           	  ~
											// Yes    No    -visible          ~
											// No     Yes   +visible          ~
											// No     No    ~                 ~
											if ($punishedthread['visible'] AND (($punishedforum['countposts'] AND !$destforum['countposts']) OR (!$punishedforum['countposts'] AND $destforum['countposts'])))
											{
												$uposts = $this->registry->db->query_read("
													SELECT userid
													FROM " . TABLE_PREFIX . "post
													WHERE threadid = $punishedthread[threadid]
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

											phpkd_vblvb_build_forum_counters($punishedforum['forumid']);
											if ($punishedforum['forumid'] != $destforum['forumid'])
											{
												phpkd_vblvb_build_forum_counters($destforum['forumid']);
											}

											// Update canview status of thread subscriptions
											phpkd_vblvb_update_subscriptions(array('threadids' => array($punishedthread['threadid'])));
										}
									}
								}
								break;


							// TODO: Temporary disabled!
							case 'PUNISH_DELETE':
								$logpunish['delete'] = TRUE;
								$reporter = fetch_userinfo($this->registry->options['phpkd_vblvb_reporter']);
								$delinfo = array('userid' => $reporter['userid'], 'username' => $reporter['username'], 'reason' => $this->vbphrase['phpkd_vblvb_punish_reason'], 'keepattachments' => 1);
								phpkd_vblvb_delete_thread($punishedthread['threadid'], $punishedforum['countposts'], FALSE, $delinfo, FALSE, $punishedthread);
								break;
						}
					}


					// Record Punishment Actions In Details (For Future use when editing)
					$logpunish['dateline'] = TIMENOW;
					$this->registry->db->query_write("
						UPDATE " . TABLE_PREFIX . "post SET
							phpkd_vblvb = '" . serialize($logpunish) . "'
						WHERE postid = $postid
							AND userid = $userid
					");


					unset($punishedpost, $punishedthread, $punishedforum);
				}
			}
		}
	}


	/**
	* Get Staff Members
	*
	* @return	array	Staff members to be notified
	*/
	function fetch_staff()
	{
		$mods = array();
		if ($moderators = $this->registry->db->query_read("
			SELECT DISTINCT user.email, user.languageid, user.userid, user.username
			FROM " . TABLE_PREFIX . "moderator AS moderator
			INNER JOIN " . TABLE_PREFIX . "user AS user ON (user.userid = moderator.userid)
			WHERE moderator.permissions & " . ($this->registry->bf_misc_moderatorpermissions['canbanusers']) . "
				AND moderator.forumid <> -1
		"))
		{
			while ($moderator = $this->registry->db->fetch_array($moderators))
			{
				$mods["$moderator[userid]"] = $moderator;
			}
		}

		if (empty($mods) OR $this->registry->options['phpkd_vblvb_rprts_messaging'] == 1)
		{
			$moderators = $this->registry->db->query_read("
				SELECT DISTINCT user.email, user.languageid, user.username, user.userid
				FROM " . TABLE_PREFIX . "usergroup AS usergroup
				INNER JOIN " . TABLE_PREFIX . "user AS user ON
					(user.usergroupid = usergroup.usergroupid OR FIND_IN_SET(usergroup.usergroupid, user.membergroupids))
				WHERE usergroup.adminpermissions > 0
					AND (usergroup.adminpermissions & " . $this->registry->bf_ugp_adminpermissions['ismoderator'] . ")
					" . (!empty($mods) ? "AND userid NOT IN (" . implode(',', array_keys($mods)) . ")" : "") . "
			");

			if ($moderators)
			{
				while ($moderator = $this->registry->db->fetch_array($moderators))
				{
					$mods["$moderator[userid]"] = $moderator;
				}
			}
		}

		return $mods;
	}
}


/*============================================================================*\
|| ########################################################################### ||
|| # Version: 4.0.130
|| # $Revision$
|| # Released: $Date$
|| ########################################################################### ||
\*============================================================================*/