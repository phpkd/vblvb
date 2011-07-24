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
 * Data Manager class
 *
 * @category	vB Link Verifier Bot 'Ultimate'
 * @package		PHPKD_VBLVB
 * @subpackage	PHPKD_VBLVB_DM
 * @copyright	Copyright ©2005-2011 PHP KingDom. All Rights Reserved. (http://www.phpkd.net)
 * @license		http://info.phpkd.net/en/license/commercial
 */
class PHPKD_VBLVB_DM
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
	 * @return	PHPKD_VBLVB
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

		return $this;
	}

	/**
	 * Extract links from text, pass it to be verified and return report
	 *
	 * @param	string	Message text
	 * @param	int		postid
	 * @return	array	Returns array of how many (all/checked/alive/dead/down) links
	 */
	public function fetch_urls($messagetext, $postid = 0)
	{
		$opentags = array();
		$closetags = array();
		$this->_registry->initialize(array('hosts', 'protocols', 'bbcodes'));

		foreach ($this->_registry->bbcodes as $bbkey => $bbvalue)
		{
			if ('LINK' != $bbkey)
			{
				foreach ($bbvalue as $key => $value)
				{
					switch ($key)
					{
						case 'open':
							$opentags[] = $value;
							break;
						case 'close':
							if ('LIST' != $bbkey)
							{
								$closetags[] = $value;
							}
							break;
					}
				}
			}
		}

		if (!empty($opentags) AND !empty($closetags))
		{
			$taglist = implode('|', $opentags) . implode('|', $closetags);
			$regex1 = '#(^|(?<=[^_a-z0-9-=\]"\'/@]|(?<=' . $taglist . ')\]))((' . implode('|', $this->_registry->protocols) . ')://|www\.)((\[(?!/)|[^\s[^$`"{}<>])+)(?!\[/url|\[/img)(?=[,.!\')]*(\)\s|\)$|[\s[]|$))#siU';
			preg_match_all($regex1, $messagetext, $matches1);

			if (is_array($matches1) AND !empty($matches1))
			{
				unset($matches1[1], $matches1[2], $matches1[3], $matches1[4], $matches1[5], $matches1[6]);
			}
		}

		if (isset($this->_registry->bbcodes['link']))
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
			$this->_registry->seterror('phpkd_vblvb_invalid_criteria', ERRTYPE_ECHO, $postid);
			return array('all' => 0, 'checked' => 0, 'alive' => 0, 'dead' => 0, 'down' => 0);
		}


		if (is_array($matches) AND !empty($matches))
		{
			$actualurls = array();

			foreach ($matches as $matchvalue)
			{
				foreach ($matchvalue as $singleurl)
				{
					if (!empty($singleurl))
					{
						$actualurls[] = trim($singleurl);
					}
				}
			}
		}
		else
		{
			$this->_registry->seterror('phpkd_vblvb_invalid_criteria', ERRTYPE_ECHO, $postid);
			return array('all' => 0, 'checked' => 0, 'alive' => 0, 'dead' => 0, 'down' => 0);
		}


		if (is_array($actualurls) AND !empty($actualurls))
		{
			$checked = 0;
			$counter = 0;
			$urlsreturn = array();

			foreach(array_unique($actualurls) as $url)
			{
				if ($this->_registry->_vbulletin->phpkd_vblvb['general_maxlinks'] > 0 AND $checked >= $this->_registry->_vbulletin->phpkd_vblvb['general_maxlinks'])
				{
					break;
				}

				// Match URLs with active hosts
				foreach($this->_registry->hosts as $host)
				{
					if (!empty($host['urlmatch']) AND preg_match("#$host[urlmatch]#i", $url, $hostmatch))
					{
						if (0 == $checked)
						{
							$this->_registry->logstring('<ol>', true, $postid);
						}

						if (!empty($host['apiurl']) AND count($hostmatch) > 1)
						{
							unset($hostmatch[0]);
							$urlsreturn[] = ($this->_registry->_vbulletin->phpkd_vblvb['linkdir_recording_active'] ? array('host' => $host['domain'], 'url' => $url, 'lastcheck' => TIMENOW, 'hash' => md5($url), 'status' => $this->check($url, str_replace(array('{1}', '{2}'), array($hostmatch[1], $hostmatch[2]), $host['apiurl']), $host['status'], $host['contentmatch'], $host['downmatch'], $host['urlsearch'], $host['urlreplace'], $userid, $postid)) : array('status' => $this->check($url, str_replace(array('{1}', '{2}'), array($hostmatch[1], $hostmatch[2]), $host['apiurl']), $host['status'], $host['contentmatch'], $host['downmatch'], $host['urlsearch'], $host['urlreplace'], $userid, $postid)));
						}
						else
						{
							$urlsreturn[] = ($this->_registry->_vbulletin->phpkd_vblvb['linkdir_recording_active'] ? array('host' => $host['domain'], 'url' => $url, 'lastcheck' => TIMENOW, 'hash' => md5($url), 'status' => $this->check($url, '', $host['status'], $host['contentmatch'], $host['downmatch'], $host['urlsearch'], $host['urlreplace'], $userid, $postid)) : array('status' => $this->check($url, '', $host['status'], $host['contentmatch'], $host['downmatch'], $host['urlsearch'], $host['urlreplace'], $userid, $postid)));
						}

						$checked++;
					}
				}

				$counter++;
			}

			if (0 < $checked)
			{
				$this->_registry->logstring('</ol>', true, $postid);
			}

			$alive = 0;
			$dead = 0;
			$down = 0;

			foreach ($urlsreturn as $urlreturn)
			{
				switch ($urlreturn['status'])
				{
					case 'alive':
						$alive++;
						break;

					case 'dead':
						$dead++;
						break;

					case 'down':
					default:
						$down++;
						break;
				}
			}

			if (0 == $checked)
			{
				$this->_registry->seterror('phpkd_vblvb_invalid_criteria', ERRTYPE_ECHO, $postid);
			}

			return array('all' => $counter, 'checked' => $checked, 'alive' => $alive, 'dead' => $dead, 'down' => $down, 'urlrecords' => ($this->_registry->_vbulletin->phpkd_vblvb['linkdir_recording_active'] ? $urlsreturn : false));
		}
		else
		{
			$this->_registry->seterror('phpkd_vblvb_invalid_criteria', ERRTYPE_ECHO, $postid);
			return array('all' => 0, 'checked' => 0, 'alive' => 0, 'dead' => 0, 'down' => 0);
		}
	}

	/**
	 * Verify if the supplied link is (alive/dead/down) & return it's status
	 *
	 * @param	string	Link to be checked
	 * @param	string	API URL to be checked (in case of API call)
	 * @param	string	Per Link Regex formula to be evaluated
	 * @param	string	Regex search patern to be applied on the supplied link -if required-
	 * @param	string	Regex replace patern to be applied on the supplied link -if required-
	 * @param	int		userid
	 * @param	int		postid
	 * @return	array	Checked link status & report
	 */
	public function check($url, $apiurl = '', $hoststatus, $contentmatch, $downmatch, $urlsearch, $urlreplace, $userid = 0, $postid = 0)
	{
		// Just keep the original URL as it is for the logging purposes. See ( http://forum.phpkd.net/project.php?issueid=65 )
		$oriurl = $url;
		$colors = unserialize($this->_registry->_vbulletin->phpkd_vblvb['lookfeel_linkstatus_colors']);

		if ('alive' == $hoststatus)
		{
			if (!empty($apiurl))
			{
				$page = $this->vurl($apiurl);
				$excontentmatch = explode('|', $contentmatch);
				$ex2contentmatch = explode(',', $excontentmatch[1]);
				$exdownmatch = explode('|', $downmatch);
				$expage = explode(',', $page);

				// if (count($excontentmatch) > 1 AND count($expage) > 1 AND $excontentmatch[1] == $expage[$excontentmatch[0] - 1])
				if (count($excontentmatch) > 1 AND count($expage) > 1 AND ($ex2contentmatch[0] == $expage[$excontentmatch[0] - 1] OR $ex2contentmatch[1] == $expage[$excontentmatch[0] - 1]))
				{
					$status = 'alive';
					$log = construct_phrase($this->_registry->_vbphrase['phpkd_vblvb_log_link_alive'], $colors[0], $oriurl);
				}
				else if (count($exdownmatch) > 1 AND count($expage) > 1 AND $exdownmatch[1] == $expage[$exdownmatch[0] - 1])
				{
					$status = 'down';
					$log = construct_phrase($this->_registry->_vbphrase['phpkd_vblvb_log_link_down'], $colors[2], $oriurl);
				}
				else
				{
					$status = 'dead';
					$log = construct_phrase($this->_registry->_vbphrase['phpkd_vblvb_log_link_dead'], $colors[1], $oriurl);
				}
			}
			else
			{
				if (!empty($urlsearch) AND preg_match("#$urlsearch#i", $url))
				{
					$url = preg_replace("#$urlsearch#i", $urlreplace, $url);
				}

				$page = $this->vurl($url);

				if (!empty($contentmatch) AND preg_match("#$contentmatch#i", $page))
				{
					$status = 'alive';
					$log = construct_phrase($this->_registry->_vbphrase['phpkd_vblvb_log_link_alive'], $colors[0], $oriurl);
				}
				else if (!empty($downmatch) AND preg_match("#$downmatch#i", $page))
				{
					$status = 'down';
					$log = construct_phrase($this->_registry->_vbphrase['phpkd_vblvb_log_link_down'], $colors[2], $oriurl);
				}
				else
				{
					$status = 'dead';
					$log = construct_phrase($this->_registry->_vbphrase['phpkd_vblvb_log_link_dead'], $colors[1], $oriurl);
				}
			}
		}
		else if ('dead' == $hoststatus)
		{
			$status = 'dead';
			$log = construct_phrase($this->_registry->_vbphrase['phpkd_vblvb_log_link_dead'], $colors[1], $oriurl);
		}
		else
		{
			$status = 'down';
			$log = construct_phrase($this->_registry->_vbphrase['phpkd_vblvb_log_link_down'], $colors[2], $oriurl);
		}


		$this->_registry->logstring($log, (($this->_registry->_vbulletin->phpkd_vblvb['reporting_included_posts'] <= 1) AND ($this->_registry->_vbulletin->phpkd_vblvb['reporting_included_links'] == 0 OR ($this->_registry->_vbulletin->phpkd_vblvb['reporting_included_links'] == 1 AND $status == 'alive') OR ($this->_registry->_vbulletin->phpkd_vblvb['reporting_included_links'] == 2 AND $status == 'dead') OR ($this->_registry->_vbulletin->phpkd_vblvb['reporting_included_links'] == 3 AND $status == 'down'))), $postid);

		return $status;
	}

	/**
	 * Returns content of the remotely fetched page
	 *
	 * @param	string	URL to be remotely fetched
	 * @param	string	Posted fields (string as query string, or as array)
	 * @return	string	Page Content
	 */
	public function vurl($url, $post = null)
	{
		require_once(DIR . '/includes/class_vurl.php');

		$vurl = new vB_vURL($this->_registry->_vbulletin);
		$vurl->set_option(VURL_URL, $url);
		$vurl->set_option(VURL_USERAGENT, 'vBulletin/' . FILE_VERSION);
		$vurl->set_option(VURL_FOLLOWLOCATION, 1);
		$vurl->set_option(VURL_MAXREDIRS, 3);

		if (null !== $post)
		{
			$vurl->set_option(VURL_POST, 1);
			$vurl->set_option(VURL_POSTFIELDS, $post);
		}

		$vurl->set_option(VURL_RETURNTRANSFER, 1);
		$vurl->set_option(VURL_CLOSECONNECTION, 1);

		return $vurl->exec();
	}

	/**
	 * Staff Reports
	 *
	 * @param	string	Concatenated string of all punished posts
	 * @param	array	Array of checked/dead/punished post counts
	 * @return	boolean	True on success
	 */
	public function staff_reports($punished_links, $records)
	{
		$this->_registry->initialize(array('staff_reports'));

		if (!empty($this->_registry->staff_reports) AND $this->_registry->_vbulletin->phpkd_vblvb['reporting_reporter'] AND $reporter = fetch_userinfo($this->_registry->_vbulletin->phpkd_vblvb['reporting_reporter']) AND $mods = $this->fetch_staff() AND $postlogs = $this->_registry->getPostlog())
		{
			require_once(DIR . '/includes/functions_wysiwyg.php');

			$logstring = $this->_registry->_vbphrase['phpkd_vblvb_log_checked_posts'] . '<ol class="smallfont">';

			if ($this->_registry->_vbulletin->phpkd_vblvb['reporting_included_posts'] <= 1)
			{
				$posts = array();
				$colors = unserialize($this->_registry->_vbulletin->phpkd_vblvb['lookfeel_linkstatus_colors']);

				foreach ($postlogs as $postitemid => $postitem)
				{
					$posts[$postitem['forumid']]['forumtitle'] = $postitem['forumtitle'];
					$posts[$postitem['forumid']][$postitem['threadid']]['threadtitle'] = $postitem['threadtitle'];
					$posts[$postitem['forumid']][$postitem['threadid']][$postitem['postid']] = $postitem;
				}

				foreach ($posts AS $forumid => $forumposts)
				{
					$logstring .= '<li>' . construct_phrase($this->_registry->_vbphrase['phpkd_vblvb_log_forum'], $this->_registry->_vbulletin->options['bburl'] . '/forumdisplay.php?f=' . $forumid, $forumposts['forumtitle']) . '<ol class="smallfont">';
					unset($forumposts['forumtitle']);

					foreach ($forumposts AS $threadid => $threadposts)
					{
						$logstring .= '<li>' . construct_phrase($this->_registry->_vbphrase['phpkd_vblvb_log_thread'], $this->_registry->_vbulletin->options['bburl'] . '/showthread.php?t=' . $threadid, $threadposts['threadtitle']) . '<ol class="smallfont">';
						unset($threadposts['threadtitle']);

						foreach ($threadposts AS $postid => $post)
						{
							$logstring .= $post['logrecord'];
						}

						$logstring .= '</ol></li><br />';
					}

					$logstring .= '</ol></li>';
				}

				$logstring .= construct_phrase($this->_registry->_vbphrase['phpkd_vblvb_log_summery_all'], $colors[0], $colors[1], $colors[2], $records['checked'], ($records['checked'] - $records['dead']), $records['dead'], $records['punished']) . '</ol><br />';
			}

			if (!empty($punished_links) AND $this->_registry->_vbulletin->phpkd_vblvb['reporting_included_posts'] == 0 OR $this->_registry->_vbulletin->phpkd_vblvb['reporting_included_posts'] == 2)
			{
				$logstring .= $this->_registry->_vbphrase['phpkd_vblvb_log_punished_posts'] . '<ol class="smallfont">' . $punished_links . '</ol><br />';
			}


			cache_permissions($reporter, false);
			$formatedlog = convert_wysiwyg_html_to_bbcode($logstring, false, true);
			$datenow = vbdate($this->_registry->_vbulletin->options['dateformat'], TIMENOW);
			$timenow = vbdate($this->_registry->_vbulletin->options['timeformat'], TIMENOW);


			foreach ($this->_registry->staff_reports as $staff_report)
			{
				switch ($staff_report)
				{
					// Staff Reports: Private Messages
					case 'pm':
						if (is_array($mods) AND !empty($mods))
						{
							foreach ($mods as $mod)
							{
								if (!empty($mod['username']))
								{
									$email_langid = ($mod['languageid'] > 0 ? $mod['languageid'] : $this->_registry->_vbulletin->options['languageid']);
									eval(fetch_email_phrases('phpkd_vblvb_staff_reports', $email_langid));

									// create the DM to do error checking and insert the new PM
									$pmdm =& datamanager_init('PM', $this->_registry->_vbulletin, ERRTYPE_SILENT);
									$pmdm->set_info('is_automated', true);
									$pmdm->set('fromuserid', $reporter['userid']);
									$pmdm->set('fromusername', $reporter['username']);
									$pmdm->set_info('receipt', false);
									$pmdm->set_info('savecopy', false);
									$pmdm->set('title', $subject);
									$pmdm->set('message', $message);
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

					// Staff Reports: E-Mails
					case 'email':
						if ($this->_registry->_vbulletin->options['enableemail'])
						{
							if (is_array($mods) AND count($mods) > 0)
							{
								require_once(DIR . '/includes/class_bbcode_alt.php');
								$plaintext_parser = new vB_BbCodeParser_PlainText($this->_registry->_vbulletin, fetch_tag_list());

								foreach ($mods as $mod)
								{
									if (!empty($mod['email']))
									{
										$email_langid = ($mod['languageid'] > 0 ? $mod['languageid'] : $this->_registry->_vbulletin->options['languageid']);
										$plaintext_parser->set_parsing_language($email_langid);
										eval(fetch_email_phrases('phpkd_vblvb_staff_reports', $email_langid));
										vbmail($mod['email'], $subject, $plaintext_parser->parse($message), true);
									}
								}

								unset($plaintext_parser);
							}
						}
						break;

					// Staff Reports: New Reply
					case 'reply':
						if ($this->_registry->_vbulletin->phpkd_vblvb['reporting_threadid'] > 0 AND $reportthread = fetch_threadinfo($this->_registry->_vbulletin->phpkd_vblvb['reporting_threadid']) AND !$reportthread['isdeleted'] AND $reportthread['visible'] == 1  AND $reportforum = fetch_foruminfo($reportthread['forumid']))
						{
							eval(fetch_email_phrases('phpkd_vblvb_staff_reports', $this->_registry->_vbulletin->options['languageid']));

							$postman =& datamanager_init('Post', $this->_registry->_vbulletin, ERRTYPE_SILENT, 'threadpost');
							$postman->set_info('thread', $reportthread);
							$postman->set_info('forum', $reportforum);
							$postman->set_info('is_automated', true);
							$postman->set_info('parseurl', true);
							$postman->set('threadid', $reportthread['threadid']);
							$postman->set('userid', $reporter['userid']);
							$postman->set('allowsmilie', true);
							$postman->set('visible', true);
							$postman->set('title', $subject);
							$postman->set('pagetext', $message);

							// not posting as the current user, IP won't make sense
							$postman->set('ipaddress', '');

							$postman->save();
							unset($postman);
						}
						break;

					// Staff Reports: New Thread
					case 'thread':
						if ($this->_registry->_vbulletin->phpkd_vblvb['reporting_forumid'] > 0 AND $reportforum = fetch_foruminfo($this->_registry->_vbulletin->phpkd_vblvb['reporting_forumid']))
						{
							// Start: Required for 'mark_thread_read', fix the following bug: http://forum.phpkd.net/project.php?issueid=76
							if (!$db)
							{
								global $db;
								$db = $this->_registry->_vbulletin->db;
							}
							// End: Required for 'mark_thread_read', fix the following bug: http://forum.phpkd.net/project.php?issueid=76

							eval(fetch_email_phrases('phpkd_vblvb_staff_reports', $this->_registry->_vbulletin->options['languageid']));

							$threadman =& datamanager_init('Thread_FirstPost', $this->_registry->_vbulletin, ERRTYPE_SILENT, 'threadpost');
							$threadman->set_info('forum', $reportforum);
							$threadman->set_info('is_automated', true);
							$threadman->set_info('skip_moderator_email', true);
							$threadman->set_info('mark_thread_read', true);
							$threadman->set_info('parseurl', true);
							$threadman->set('allowsmilie', true);
							$threadman->set('userid', $reporter['userid']);
							$threadman->setr_info('user', $reporter);
							$threadman->set('title', $subject);
							$threadman->set('pagetext', $message);
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

								if (($userperms & $this->_registry->_vbulletin->bf_ugp_forumpermissions['canview']) AND ($userperms & $this->_registry->_vbulletin->bf_ugp_forumpermissions['canviewthreads']) AND $reporter['autosubscribe'] != -1)
								{
									$this->_registry->_vbulletin->db->query_write("
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

			// It's OK! Return true for success
			return true;
		}
	}

	/**
	 * User Reports
	 *
	 * @param	array	Array of posts to send user reports for their authors!
	 * @return	boolean	True on success
	 */
	public function user_reports($postids)
	{
		$this->_registry->initialize(array('user_reports'));

		if (!empty($this->_registry->user_reports) AND $this->_registry->_vbulletin->phpkd_vblvb['reporting_reporter'] AND $reporter = fetch_userinfo($this->_registry->_vbulletin->phpkd_vblvb['reporting_reporter']))
		{
			require_once(DIR . '/includes/functions_wysiwyg.php');
			require_once(DIR . '/includes/class_bbcode_alt.php');

			cache_permissions($reporter, false);
			$datenow = vbdate($this->_registry->_vbulletin->options['dateformat'], TIMENOW);
			$timenow = vbdate($this->_registry->_vbulletin->options['timeformat'], TIMENOW);
			$plaintext_parser = new vB_BbCodeParser_PlainText($this->_registry->_vbulletin, fetch_tag_list());
			$contactuslink = $this->_registry->_vbulletin->options['bburl'] . '/' . $this->_registry->_vbulletin->options['contactuslink'];
			$bbtitle = $this->_registry->_vbulletin->options['bbtitle'];


			foreach ($postids as $postid)
			{
				$postlog = $this->_registry->getPostlog($postid);
				$formatedlog = convert_wysiwyg_html_to_bbcode($postlog['logrecord'], false, true);

				foreach ($this->_registry->user_reports as $user_report)
				{
					switch ($user_report)
					{
						// User Reports: Private Messages
						case 'pm':
							$email_langid = ($postlog['languageid'] > 0 ? $postlog['languageid'] : $this->_registry->_vbulletin->options['languageid']);
							eval(fetch_email_phrases('phpkd_vblvb_user_reports', $email_langid));

							// create the DM to do error checking and insert the new PM
							$pmdm =& datamanager_init('PM', $this->_registry->_vbulletin, ERRTYPE_SILENT);
							$pmdm->set_info('is_automated', true);
							$pmdm->set('fromuserid', $reporter['userid']);
							$pmdm->set('fromusername', $reporter['username']);
							$pmdm->set_info('receipt', false);
							$pmdm->set_info('savecopy', false);
							$pmdm->set('title', $subject);
							$pmdm->set('message', $message);
							$pmdm->set_recipients(unhtmlspecialchars($postlog['username']), $reporter['permissions']);
							$pmdm->set('dateline', TIMENOW);
							$pmdm->set('allowsmilie', true);

							$pmdm->pre_save();
							if (empty($pmdm->errors))
							{
								$pmdm->save();
							}
							unset($pmdm);
							break;

						// User Reports: E-Mails
						case 'email':
							if ($this->_registry->_vbulletin->options['enableemail'])
							{
								if (!empty($postlog['email']))
								{
									$email_langid = ($postlog['languageid'] > 0 ? $postlog['languageid'] : $this->_registry->_vbulletin->options['languageid']);
									$plaintext_parser->set_parsing_language($email_langid);
									eval(fetch_email_phrases('phpkd_vblvb_user_reports', $email_langid));
									vbmail($postlog['email'], $subject, $plaintext_parser->parse($message), true);
								}
							}
							break;
					}
				}
			}

			unset($plaintext_parser);

			// It's OK! Return true for success
			return true;
		}
	}

	/**
	 * Punish dead posts/threads
	 *
	 * @param	array	Posts/Threads to be punished
	 * @return	void
	 */
	public function punish($punished_content)
	{
		$logpunish = array();
		$this->_registry->initialize(array('thread_punishs', 'post_punishs'));

		require_once(DIR . '/includes/functions_log_error.php');
		require_once(DIR . '/includes/functions_databuild.php');


		// Punish whole threads
		if (!empty($this->_registry->thread_punishs) AND !empty($punished_content['threads']))
		{
			$countingthreads = array();
			$modrecords = array();
			$reporter = fetch_userinfo($this->_registry->_vbulletin->phpkd_vblvb['reporting_reporter']);

			foreach ($punished_content['threads'] AS $threadid => $thread)
			{
				foreach ($this->_registry->thread_punishs as $punishment)
				{
					switch ($punishment)
					{
						case 'close':
							if ($thread['open'] == 1)
							{
								$logpunish['threads'][$threadid]['close'] = TIMENOW;
							}
							break;

						case 'unstick':
							if ($thread['sticky'] == 1)
							{
								$logpunish['threads'][$threadid]['unstick'] = TIMENOW;
							}
							break;

						case 'moderate':
							if ($thread['visible'] == 1)
							{
								$logpunish['threads'][$threadid]['moderate'] = TIMENOW;
							}
							break;

						case 'delete':
							if ($thread['visible'])
							{
								$logpunish['threads'][$threadid]['delete'] = TIMENOW;
							}
							break;
					}
				}

				if ($reporter['userid'])
				{
					$modlog[] = array(
						'userid'   => $reporter['userid'],
						'forumid'  => $punished_content['threads']["$threadid"]['forumid'],
						'threadid' => $threadid,
					);
				}
			}

			if ($reporter['userid'])
			{
				$delinfo = array(
					'userid'          => $reporter['userid'],
					'username'        => $reporter['username'],
					'reason'          => $this->_registry->_vbphrase['phpkd_vblvb_punish_reason'],
					'keepattachments' => 1
				);
			}

			foreach ($this->_registry->thread_punishs as $punishment)
			{
				switch ($punishment)
				{
					case 'close':
						$this->_registry->_vbulletin->db->query_write("
							UPDATE " . TABLE_PREFIX . "thread
							SET open = 0
							WHERE threadid IN(" . implode(',', array_keys($punished_content['threads'])) . ")
						");

						if (!empty($modlog))
						{
							log_moderator_action($modlog, 'closed_thread');
						}
						break;

					case 'unstick':
						$this->_registry->_vbulletin->db->query_write("
							UPDATE " . TABLE_PREFIX . "thread
							SET sticky = 0
							WHERE threadid IN(" . implode(',', array_keys($punished_content['threads'])) . ")
						");

						if (!empty($modlog))
						{
							log_moderator_action($modlog, 'unstuck_thread');
						}
						break;

					case 'moderate':
						// Set threads hidden
						$this->_registry->_vbulletin->db->query_write("
							UPDATE " . TABLE_PREFIX . "thread
							SET visible = 0
							WHERE threadid IN(" . implode(',', array_keys($punished_content['threads'])) . ")
						");

						// Set thread redirects hidden
						$this->_registry->_vbulletin->db->query_write("
							UPDATE " . TABLE_PREFIX . "thread
							SET visible = 0
							WHERE open = 10 AND pollid IN(" . implode(',', array_keys($punished_content['threads'])) . ")
						");

						foreach ($punished_content['threads'] as $threadid => $thread)
						{
							// this thread is visible AND in a counting forum
							if ($thread['visible'] AND $thread['replycount'])
							{
								$countingthreads[] = $threadid;
							}

							$modrecords[] = "($threadid, 'thread', " . TIMENOW . ")";
						}

						if (!empty($countingthreads))
						{
							// Update post count for visible posts
							$userbyuserid = array();
							$posts = $this->_registry->_vbulletin->db->query_read("
								SELECT userid
								FROM " . TABLE_PREFIX . "post
								WHERE threadid IN(" . implode(',', $countingthreads) . ")
									AND visible = 1
									AND userid > 0
							");
							while ($post = $this->_registry->_vbulletin->db->fetch_array($posts))
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
							{
								$userbypostcount = array();
								$alluserids = '';

								foreach ($userbyuserid AS $postuserid => $postcount)
								{
									$alluserids .= ",$postuserid";
									$userbypostcount["$postcount"] .= ",$postuserid";
								}

								foreach($userbypostcount AS $postcount => $userids)
								{
									$casesql .= " WHEN userid IN (0$userids) THEN $postcount\n";
								}

								$this->_registry->_vbulletin->db->query_write("
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

						if (!empty($modrecords))
						{
							// Insert Moderation Records
							$this->_registry->_vbulletin->db->query_write("
								REPLACE INTO " . TABLE_PREFIX . "moderation
								(primaryid, type, dateline)
								VALUES
								" . implode(',', $modrecords) . "
							");
						}

						// Clean out deletionlog
						$this->_registry->_vbulletin->db->query_write("
							DELETE FROM " . TABLE_PREFIX . "deletionlog
							WHERE primaryid IN(" . implode(',', array_keys($punished_content['threads'])) . ")
								AND type = 'thread'
						");

						foreach ($punished_content['forums'] as $forumid)
						{
							build_forum_counters($forumid);
						}

						if (!empty($modlog))
						{
							log_moderator_action($modlog, 'unapproved_thread');
						}
						break;

					case 'delete':
						foreach ($punished_content['threads'] as $threadid => $thread)
						{
							$replycount = $this->_registry->_vbulletin->forumcache["$thread[forumid]"]['options'] & $this->_registry->_vbulletin->bf_misc_forumoptions['countposts'];

							if ($thread['visible'] == 2)
							{
								# Thread is already soft deleted
								continue;
							}

							$threadman =& datamanager_init('Thread', $this->_registry->_vbulletin, ERRTYPE_SILENT, 'threadpost');
							$threadman->set_existing($thread);

							// Redirect
							if ($thread['open'] == 10)
							{
								$threadman->delete(false, true, ($delinfo ? $delinfo : null), ($delinfo ? true : false));
							}
							else
							{
								$threadman->delete($replycount, false, ($delinfo ? $delinfo : null), ($delinfo ? true : false));
							}

							unset($threadman);
						}

						foreach ($punished_content['forums'] as $forumid)
						{
							build_forum_counters($forumid);
						}
						break;

					case 'move':
						// check whether destination forum can contain posts
						if ($destforuminfo = verify_id('forum', $this->_registry->_vbulletin->phpkd_vblvb['punishment_forumid'], false, true) AND $destforuminfo['cancontainthreads'] AND !$destforuminfo['link'])
						{
							$threadarray = array();
							$countingthreads = array();

							foreach ($punished_content['threads'] as $threadid => $thread)
							{
								$logpunish['threads'][$threadid][$punishment] = array('orifid' => $thread['forumid'], 'destfid' => $destforuminfo['forumid']);

								// Ignore all threads that are already in the destination forum
								if ($thread['forumid'] != $destforuminfo['forumid'])
								{
									$threadarray["$thread[threadid]"] = $thread;

									if ($thread['visible'])
									{
										$countingthreads[] = $threadid;
									}
								}
							}

							if (!empty($threadarray))
							{
								// check to see if this thread is being returned to a forum it's already been in
								// if a redirect exists already in the destination forum, remove it
								$checkprevious = $this->_registry->_vbulletin->db->query_read("SELECT threadid FROM " . TABLE_PREFIX . "thread WHERE forumid = $destforuminfo[forumid] AND open = 10 AND pollid IN(" . implode(',', array_keys($threadarray)) . ")");

								while ($check = $this->_registry->_vbulletin->db->fetch_array($checkprevious))
								{
									$old_redirect =& datamanager_init('Thread', $this->_registry->_vbulletin, ERRTYPE_SILENT, 'threadpost');
									$old_redirect->set_existing($check);
									$old_redirect->delete(false, true, NULL, false);
									unset($old_redirect);
								}

								// Move threads
								$this->_registry->_vbulletin->db->query_write("
									UPDATE " . TABLE_PREFIX . "thread
									SET forumid = $destforuminfo[forumid]
									WHERE threadid IN(" . implode(',', array_keys($threadarray)) . ")
								");

								require_once(DIR . '/includes/functions_prefix.php');
								remove_invalid_prefixes(array_keys($threadarray), $destforuminfo['forumid']);

								// Update canview status of thread subscriptions
								update_subscriptions(array('threadids' => array_keys($threadarray)));

								// Kill the post cache for these threads
								delete_post_cache_threads(array_keys($threadarray));


								if (!empty($countingthreads))
								{
									$userbyuserid = array();

									$posts = $this->_registry->_vbulletin->db->query_read("
										SELECT post.userid, post.threadid, forum.replycount
										FROM " . TABLE_PREFIX . "post AS post
										LEFT JOIN " . TABLE_PREFIX . "thread AS thread ON (post.threadid = thread.threadid)
										LEFT JOIN " . TABLE_PREFIX . "forum AS forum ON (thread.forumid = forum.forumid)
										WHERE post.threadid IN(" . implode(',', $countingthreads) . ")
											AND post.visible = 1
											AND	post.userid > 0
									");

									while ($post = $this->_registry->_vbulletin->db->fetch_array($posts))
									{
										if ($post['replycount'] AND !$destforuminfo['replycount'])
										{
											// Take away a post
											if (!isset($userbyuserid["$post[userid]"]))
											{
												$userbyuserid["$post[userid]"] = -1;
											}
											else
											{
												$userbyuserid["$post[userid]"]--;
											}
										}
										else if (!$post['replycount'] AND $destforuminfo['replycount'])
										{
											// Add a post
											if (!isset($userbyuserid["$post[userid]"]))
											{
												$userbyuserid["$post[userid]"] = 1;
											}
											else
											{
												$userbyuserid["$post[userid]"]++;
											}
										}
									}

									if (!empty($userbyuserid))
									{
										$alluserids = '';
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

										$this->_registry->_vbulletin->db->query_write("
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

								// Search index maintenance
								require_once(DIR . '/vb/search/indexcontroller/queue.php');
								foreach($threadarray AS $threadid => $thread)
								{
									vb_Search_Indexcontroller_Queue::indexQueue('vBForum', 'Post', 'thread_data_change', $threadid);
								}

								foreach($punished_content['forums'] as $forumid)
								{
									build_forum_counters($forumid);
								}

								build_forum_counters($destforuminfo['forumid']);


								foreach ($threadarray as $threadid => $thread)
								{
									$modlog[] = array(
										'userid'   => $reporter['userid'],
										'forumid'  => $thread['forumid'],
										'threadid' => $threadid,
									);
								}

								log_moderator_action($modlog, 'thread_moved_to_x', $destforuminfo['title']);
							}
						}
						break;
				}
			}
		}


		// Punish individual posts
		if (!empty($this->_registry->post_punishs) AND !empty($punished_content['posts']))
		{
			$firstpost = array();
			$reporter = fetch_userinfo($this->_registry->_vbulletin->phpkd_vblvb['reporting_reporter']);

			foreach ($punished_content['posts'] AS $postid => $post)
			{
				foreach ($this->_registry->post_punishs as $punishment)
				{
					switch ($punishment)
					{
						case 'moderate':
							if ($post['pvisible'])
							{
								$logpunish['posts'][$postid]['moderate'] = TIMENOW;
							}
							break;

						case 'delete':
							if ($post['pvisible'] == 1)
							{
								$logpunish['posts'][$postid]['delete'] = TIMENOW;
							}
							break;
					}
				}
			}

			if ($reporter['userid'])
			{
				$delinfo = array(
					'userid'          => $reporter['userid'],
					'username'        => $reporter['username'],
					'reason'          => $this->_registry->_vbphrase['phpkd_vblvb_punish_reason'],
					'keepattachments' => 1
				);
			}

			foreach ($punished_content['posts'] as $postid => $post)
			{
				if ($post['firstpostid'] == $postid AND $post['visible'] == 1)
				{
					// Case 'moderate': unapproving a thread so do not decremement the counters of any other posts in this thread
					// Case 'delete': deleting a thread so do not decremement the counters of any other posts in this thread
					$firstpost["$post[threadid]"] = true;
				}
				else if (!empty($firstpost["$post[threadid]"]))
				{
					$punished_content['posts']["$postid"]['skippostcount'] = true;
				}
			}

			foreach ($this->_registry->post_punishs as $punishment)
			{
				switch ($punishment)
				{
					case 'moderate':
						foreach ($punished_content['posts'] as $postid => $post)
						{
							$threadinfo = array(
								'threadid'    => $post['threadid'],
								'forumid'     => $post['forumid'],
								'visible'     => $post['visible'],
								'firstpostid' => $post['firstpostid']
							);

							// Can't send $thread without considering that thread_visible may change if we approve the first post of a thread
							unapprove_post($postid, ($post['replycount'] AND !$post['skippostcount']), true, $post, $threadinfo, false);
						}
						break;

					case 'delete':
						foreach ($punished_content['posts'] AS $postid => $post)
						{
							$postman =& datamanager_init('Post', $this->_registry->_vbulletin, ERRTYPE_SILENT, 'threadpost');
							$postman->set_existing($post);
							$postman->delete(($post['replycount'] AND !$post['skippostcount']), $post['threadid'], false, $delinfo);
							unset($postman);
						}
						break;
				}
			}

			foreach (array_keys($punished_content['threads']) AS $threadid)
			{
				build_thread_counters($threadid);
			}

			foreach (array_keys($punished_content['forums']) AS $forumid)
			{
				build_forum_counters($forumid);
			}
		}


		if (!empty($logpunish))
		{
			$update_lastpunish_thread = '';
			$update_lastpunish_post = '';

			foreach ($logpunish as $typeid => $type)
			{
				switch ($typeid)
				{
					case 'threads':
						foreach ($type as $threadid => $thread)
						{
							$update_lastpunish_thread .= ' WHEN ' . $threadid . ' THEN \'' . serialize($thread) . '\'';
						}
						break;

					case 'posts':
						foreach ($type as $postid => $post)
						{
							$update_lastpunish_post .= ' WHEN ' . $postid . ' THEN \'' . serialize($post) . '\'';
						}
						break;
				}
			}
		}


		// Record punishment actions in details (for future use when editing)
		if (!empty($logpunish['threads']))
		{
			$this->_registry->_vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "thread SET
				phpkd_vblvb_lastpunish = CASE threadid
				$update_lastpunish_thread ELSE phpkd_vblvb_lastpunish END
				WHERE threadid IN(" . implode(',', array_keys($punished_content['threads'])) . ")
			");
		}

		// Record punishment actions in details (for future use when editing)
		if (!empty($logpunish['posts']))
		{
			$this->_registry->_vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "post SET
				phpkd_vblvb_lastpunish = CASE postid
				$update_lastpunish_post ELSE phpkd_vblvb_lastpunish END
				WHERE postid IN(" . implode(',', array_keys($punished_content['posts'])) . ")
			");
		}
	}

	/**
	 * Get Staff Members
	 *
	 * @return	array	Staff members to be notified
	 */
	public function fetch_staff()
	{
		$mods = array();

		if ($moderators = $this->_registry->_vbulletin->db->query_read("
			SELECT DISTINCT user.email, user.languageid, user.userid, user.username
			FROM " . TABLE_PREFIX . "moderator AS moderator
			INNER JOIN " . TABLE_PREFIX . "user AS user ON (user.userid = moderator.userid)
			WHERE moderator.permissions & " . ($this->_registry->_vbulletin->bf_misc_moderatorpermissions['canbanusers']) . "
				AND moderator.forumid <> -1
		"))
		{
			while ($moderator = $this->_registry->_vbulletin->db->fetch_array($moderators))
			{
				$mods["$moderator[userid]"] = $moderator;
			}
		}

		if (empty($mods) OR $this->_registry->_vbulletin->phpkd_vblvb['reporting_staff_reports_messaging'] == 1)
		{
			$moderators = $this->_registry->_vbulletin->db->query_read("
				SELECT DISTINCT user.email, user.languageid, user.username, user.userid
				FROM " . TABLE_PREFIX . "usergroup AS usergroup
				INNER JOIN " . TABLE_PREFIX . "user AS user ON
					(user.usergroupid = usergroup.usergroupid OR FIND_IN_SET(usergroup.usergroupid, user.membergroupids))
				WHERE usergroup.adminpermissions > 0
					AND (usergroup.adminpermissions & " . $this->_registry->_vbulletin->bf_ugp_adminpermissions['ismoderator'] . ")
					" . (!empty($mods) ? "AND userid NOT IN (" . implode(',', array_keys($mods)) . ")" : "") . "
			");

			if ($moderators)
			{
				while ($moderator = $this->_registry->_vbulletin->db->fetch_array($moderators))
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
|| # Version: 4.1.210
|| # $Revision$
|| # Released: $Date$
|| ########################################################################### ||
\*============================================================================*/