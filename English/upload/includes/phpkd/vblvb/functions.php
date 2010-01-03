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


class vBulletinHook_PHPKD_VBLVB extends vBulletinHook
{
	var $last_called = '';

	function vBulletinHook_PHPKD_VBLVB(&$pluginlist, &$hookusage)
	{
		$this->pluginlist =& $pluginlist;
		$this->hookusage =& $hookusage;
	}

	function &fetch_hook_object($hookname)
	{
		$this->last_called = $hookname;
		return parent::fetch_hook_object($hookname);
	}
}


/*
 * This long tail list of definitions is better than calling 'fetch_bitfield_definitions'
 * in 'adminfunctions_options.php' from a memory usage wise!!
 * This method used to decrease memory usage as possible.
 */

// Hosts #1
define('HOSTS1_ADRIVE_COM',       1);
define('HOSTS1_AXIFILE_COM',      2);
define('HOSTS1_BADONGO_COM',      4);
define('HOSTS1_BITROAD_NET',      8);
define('HOSTS1_COCOSHARE_CC',     16);
define('HOSTS1_DEPOSITFILES_COM', 32);
define('HOSTS1_EASY_SHARE_COM',   64);
define('HOSTS1_EGOSHARE_COM',     128);
define('HOSTS1_FILEFACTORY_COM',  256);
define('HOSTS1_FILEFRONT_COM',    512);
define('HOSTS1_FILES_TO',         1024);
define('HOSTS1_FLYUPLOAD_COM',    2048);
define('HOSTS1_GIGASIZE_COM',     4096);
define('HOSTS1_IFILE_IT',         8192);
define('HOSTS1_IFOLDER_RU',       16384);
define('HOSTS1_MEDIAFIRE_COM',    32768);
define('HOSTS1_MEGASHARE_COM',    65536);
define('HOSTS1_MEGAUPLOAD_COM',   131072);
define('HOSTS1_MOMUPLOAD_COM',    262144);
define('HOSTS1_NETLOAD_IN',       524288);
define('HOSTS1_RAPIDSHARE_COM',   1048576);
define('HOSTS1_RAPIDSHARE_DE',    2097152);
define('HOSTS1_RNBLOAD_COM',      4194304);
define('HOSTS1_SAVEFILE_COM',     8388608);
define('HOSTS1_SENDSPACE_COM',    16777216);
define('HOSTS1_SPEEDYSHARE_COM',  33554432);
define('HOSTS1_TURBOUPLOAD_COM',  67108864);
define('HOSTS1_UPLOADED_TO',      134217728);
define('HOSTS1_UPLOADING_COM',    268435456);
define('HOSTS1_UPLOADPALACE_COM', 536870912);

// HOSTS #2
define('HOSTS2_USAUPLOAD_NET',    1);
define('HOSTS2_ZIDDU_COM',        2);
define('HOSTS2_ZSHARE_NET',       4);

// Masks
define('MASKS_ANONYM_TO',              1);
define('MASKS_LINKBUCKS_COM',          2);
define('MASKS_LIX_IN',                 4);
define('MASKS_RAPIDSHARE_COM_FOLDERS', 8);

// Staff Reports
define('RPRTS_PM',     1);
define('RPRTS_EMAIL',  2);
define('RPRTS_REPLY',  4);
define('RPRTS_THREAD', 8);

// User Reports
define('RPRTU_PM',     1);
define('RPRTU_EMAIL',  2);

// Punishment Method for Dead posts
define('PUNISH_MODERATE', 1);
define('PUNISH_CLOSE',    2);
define('PUNISH_MOVE',     4);
define('PUNISH_DELETE',   8);



/**
* Returns list of URLs from text
*
* @param	string	Message text
*
* @return	array
*/
function phpkd_vblvb_fetch_urls($messagetext)
{
	global $vbulletin;


	preg_match_all('#\[url=("|\'|)?(.*)\\1\](?:.*)\[/url\]|\[url\](.*)\[/url\]#siU', $messagetext, $matches);

	if (!empty($matches))
	{
		$matches = @array_merge($matches[2], $matches[3]);
	}


	$hosts = $masks = array();

	// #################### Hosts #1 ####################
	if ($vbulletin->options['phpkd_vblvb_hosts'] & HOSTS1_ADRIVE_COM)
	{
		$hosts[] = array("adrive\.com\/public\/", "view");
	}

	if ($vbulletin->options['phpkd_vblvb_hosts'] & HOSTS1_AXIFILE_COM)
	{
		$hosts[] = array("axifile\.com\/?", "You have request", "@com\?@i", "com/?");
	}

	if ($vbulletin->options['phpkd_vblvb_hosts'] & HOSTS1_BADONGO_COM)
	{
		$hosts[] = array("badongo\.com\/([a-z]{2}\/)?(file)|(vid)\/", "fileBoxMenu");
	}

	if ($vbulletin->options['phpkd_vblvb_hosts'] & HOSTS1_BITROAD_NET)
	{
		$hosts[] = array("bitroad\.net\/download\/[0-9a-z]+\/", "File:");
	}

	if ($vbulletin->options['phpkd_vblvb_hosts'] & HOSTS1_COCOSHARE_CC)
	{
		$hosts[] = array("cocoshare\.cc\/[0-9]+\/", "Filesize:");
	}

	if ($vbulletin->options['phpkd_vblvb_hosts'] & HOSTS1_DEPOSITFILES_COM)
	{
		$hosts[] = array("depositfiles\.com\/([a-z]{2}\/)?files\/", "File Name", "@(com\/files\/)|(com\/[a-z]{2}\/files\/)@i", "com/en/files/");
	}

	if ($vbulletin->options['phpkd_vblvb_hosts'] & HOSTS1_EASY_SHARE_COM)
	{
		$hosts[] = array("easy-share\.com" , "file url:");
	}

	if ($vbulletin->options['phpkd_vblvb_hosts'] & HOSTS1_EGOSHARE_COM)
	{
		$hosts[] = array("egoshare\.com" , "You have requested");
	}

	if ($vbulletin->options['phpkd_vblvb_hosts'] & HOSTS1_FILEFACTORY_COM)
	{
		$hosts[] = array("filefactory\.com\/file\/", "(download link)|(Please try again later)");
	}

	if ($vbulletin->options['phpkd_vblvb_hosts'] & HOSTS1_FILEFRONT_COM)
	{
		$hosts[] = array("filefront\.com\/", "http://static4.filefront.com/ffv6/graphics/b_download_still.gif");
	}

	if ($vbulletin->options['phpkd_vblvb_hosts'] & HOSTS1_FILES_TO)
	{
		$hosts[] = array("files\.to\/get\/", "You requested the following file");
	}

	if ($vbulletin->options['phpkd_vblvb_hosts'] & HOSTS1_FLYUPLOAD_COM)
	{
		$hosts[] = array("flyupload\.flyupload.com\/get\?fid" , "Download Now");
	}

	if ($vbulletin->options['phpkd_vblvb_hosts'] & HOSTS1_GIGASIZE_COM)
	{
		$hosts[] = array("gigasize\.com\/get\.php\?d=", "Downloaded");
	}

	if ($vbulletin->options['phpkd_vblvb_hosts'] & HOSTS1_IFILE_IT)
	{
		$hosts[] = array("ifile\.it\/", "Request Ticket");
	}

	if ($vbulletin->options['phpkd_vblvb_hosts'] & HOSTS1_IFOLDER_RU)
	{
		$hosts[] = array("ifolder\.ru\/[0-9]+", "ints_code");
	}

	if ($vbulletin->options['phpkd_vblvb_hosts'] & HOSTS1_MEDIAFIRE_COM)
	{
		$hosts[] = array("mediafire\.com\/(download\.php)?\?", "You requested");
	}

	if ($vbulletin->options['phpkd_vblvb_hosts'] & HOSTS1_MEGASHARE_COM)
	{
		$hosts[] = array("megashare\.com\/[0-9]+", "Free");
	}

	if ($vbulletin->options['phpkd_vblvb_hosts'] & HOSTS1_MEGAUPLOAD_COM)
	{
		$hosts[] = array("megaupload\.com/([a-z]{2}\/)?\?d=", "(Filename:)|(All download slots assigned to your country)");
	}

	if ($vbulletin->options['phpkd_vblvb_hosts'] & HOSTS1_MOMUPLOAD_COM)
	{
		$hosts[] = array("momupload\.com\/files\/", "You want to download the file");
	}

	if ($vbulletin->options['phpkd_vblvb_hosts'] & HOSTS1_NETLOAD_IN)
	{
		$hosts[] = array("netload\.in\/datei[0-9a-z]{32}\/", "download_load");
	}

	if ($vbulletin->options['phpkd_vblvb_hosts'] & HOSTS1_RAPIDSHARE_COM)
	{
		$hosts[] = array("rapidshare\.com\/files\/", "(FILE DOWNLOAD|This file is larger than 200 Megabyte)");
	}

	if ($vbulletin->options['phpkd_vblvb_hosts'] & HOSTS1_RAPIDSHARE_DE)
	{
		$hosts[] = array("rapidshare\.de\/files\/", "You want to download");
	}

	if ($vbulletin->options['phpkd_vblvb_hosts'] & HOSTS1_RNBLOAD_COM)
	{
		$hosts[] = array("rnbload\.com\/file/" , "Filename:");
	}

	if ($vbulletin->options['phpkd_vblvb_hosts'] & HOSTS1_SAVEFILE_COM)
	{
		$hosts[] = array("savefile\.com\/files\/", "link to this file");
	}

	if ($vbulletin->options['phpkd_vblvb_hosts'] & HOSTS1_SENDSPACE_COM)
	{
		$hosts[] = array("sendspace\.com\/file\/", "The download link is located below.");
	}

	if ($vbulletin->options['phpkd_vblvb_hosts'] & HOSTS1_SPEEDYSHARE_COM)
	{
		$hosts[] = array("speedyshare\.com\/[0-9]+\.html", "\/data\/");
	}

	if ($vbulletin->options['phpkd_vblvb_hosts'] & HOSTS1_TURBOUPLOAD_COM)
	{
		$hosts[] = array("(d\.turboupload\.com\/)|(turboupload.com\/download\/)", "(Please wait while we prepare your file.)|(You have requested the file)");
	}

	if ($vbulletin->options['phpkd_vblvb_hosts'] & HOSTS1_UPLOADED_TO)
	{
		$hosts[] = array("uploaded\.to\/(\?id=|file\/)", "Filename:");
	}

	if ($vbulletin->options['phpkd_vblvb_hosts'] & HOSTS1_UPLOADING_COM)
	{
		$hosts[] = array("uploading\.com\/files\/", "Download file");
	}

	if ($vbulletin->options['phpkd_vblvb_hosts'] & HOSTS1_UPLOADPALACE_COM)
	{
		$hosts[] = array("uploadpalace\.com\/[a-zA-Z]{2}\/file\/[0-9]+\/", "Filename:");
	}


	// #################### Hosts #2 ####################
	if ($vbulletin->options['phpkd_vblvb_hosts2'] & HOSTS2_USAUPLOAD_NET)
	{
		$hosts[] = array("usaupload\.net\/d\/", "This is the download page for file");
	}

	if ($vbulletin->options['phpkd_vblvb_hosts2'] & HOSTS2_ZIDDU_COM)
	{
		$hosts[] = array("ziddu\.com\/", "Download Link");
	}

	if ($vbulletin->options['phpkd_vblvb_hosts2'] & HOSTS2_ZSHARE_NET)
	{
		$hosts[] = array("zshare\.net\/(download|audio|video)\/", "Last Download");
	}


	// #################### Masks ####################
	if ($vbulletin->options['phpkd_vblvb_masks'] & MASKS_ANONYM_TO)
	{
		$masks['anonym_to'] = 'anonym.to';
	}

	if ($vbulletin->options['phpkd_vblvb_masks'] & MASKS_LINKBUCKS_COM)
	{
		$masks['linkbucks_com'] = 'linkbucks.com';
	}

	if ($vbulletin->options['phpkd_vblvb_masks'] & MASKS_LIX_IN)
	{
		$masks['lix_in'] = 'lix.in';
	}

	if ($vbulletin->options['phpkd_vblvb_masks'] & MASKS_RAPIDSHARE_COM_FOLDERS)
	{
		$masks['rapidshare_com_folders'] = 'rapidshare.com';
	}


	$counter = 0;
	$return = array();
	foreach($matches AS $url)
	{
		if ($vbulletin->options['phpkd_vblvb_maxlinks'] > 0 AND $counter >= $vbulletin->options['phpkd_vblvb_maxlinks'])
		{
			continue;
		}

		if (!empty($url))
		{
			// Un-Mask Masked Links ...
			if(in_array('anonym.to', $masks) AND preg_match("#^(http)\:\/\/(www\.)?anonym\.to\/\?#i", $url))
			{
				$url = explode('?', $url);
				unset($url[0]);
				$url = implode($url, '?');
			}

			if(in_array('lix.in', $masks) AND preg_match("#^(http)\:\/\/(www\.)?lix\.in\/#i", $url))
			{
				$curlpost = 'tiny=' . trim(substr(strstr($url, 'n/'), 2)) . '&submit=continue';
				preg_match('@name="ifram" src="(.+?)"@i', phpkd_vblvb_curl($url, $curlpost), $match);
				$url = $match[1];
			}

			if(in_array('linkbucks.com', $masks) AND preg_match("#^(http)\:\/\/(www\.)?linkbucks\.com\/link\/#i", $url))
			{
				$page = phpkd_vblvb_curl($url);
				preg_match("/<a href=\"(.+)\" id=\"aSkipLink\">/", $page, $match);
				$url = $match[1];
			}

			if(in_array('rapidshare.com', $masks) AND preg_match("#rapidshare\.com\/users\/#i" , $url))
			{
				$page = phpkd_vblvb_curl($url);
				preg_match_all("/<a href=\"(.+)\" target=\"_blank\">/", $page, $match);
				unset($match[1][0]);

				foreach($match[1] AS $url)
				{
					$return[] = phpkd_vblvb_check(trim($url), "You would like to download the following file::");
				}
			}


			// Process Available Hosts
			foreach($hosts AS $host)
			{
				if(preg_match("#$host[0]#i", $url))
				{
					$return[] = phpkd_vblvb_check(trim($url), $host[1], $host[2], $host[3]);
				}
			}

			$counter++;
		}
	}


	$log = '';
	$alive = $down = $dead = 0;
	foreach ($return AS $rtrn)
	{
		switch ($rtrn['status'])
		{
			case 'alive':
				$alive++;
				break;
			case 'down':
				$down++;
				break;
			case 'dead':
				$dead++;
				break;
		}

		$log .= $rtrn['log'];
	}

	return array('all' => $counter, 'checked' => $alive + $down + $dead, 'alive' => $alive, 'down' => $down, 'dead' => $dead, 'log' => $log);
}


/**
* Returns content of the remotely fetched page
*
* @param	string	URL to be remotely fetched
* @param	string	Posted fields (string as query string, or as array)
*
* @return	string	Page Content
*/
function phpkd_vblvb_curl($url, $post = '0')
{
	global $vbulletin;
	require_once(DIR . '/includes/class_vurl.php');

	$vurl = new vB_vURL($vbulletin);
	$vurl->set_option(VURL_URL, $url);
	$vurl->set_option(VURL_USERAGENT, 'vBulletin/' . FILE_VERSION);

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
* Returns list of URLs from text
*
* @param	string	Link to be checked
* @param	string	Regex formula to be evaluated
*
* @return	array	Checked link status & report
*/
function phpkd_vblvb_check($link, $regex, $pattern = '', $replace = '')
{
	global $vbphrase;

	if(!empty($pattern)) 
	{
		$link = preg_replace($pattern, $replace, $link);
	}

	$page = phpkd_vblvb_curl($link);
	$link = htmlentities($link, ENT_QUOTES);


	if(preg_match("#$regex#i", $page)) 
	{
		$status = 'alive';
		$log = '<li>' . $vbphrase['phpkd_vblvb_log_link_active'] . "<a href=\"$link\" target=\"_blank\">$link</a></li>";
	}
	else if(preg_match("#The file you are trying to access is temporarily unavailable.#i", $page)) 
	{
		$status = 'down';
		$log = '<li>' . $vbphrase['phpkd_vblvb_log_link_down'] . "<a href=\"$link\" target=\"_blank\">$link</a></li>";
	}
	else 
	{
		$status = 'dead';
		$log = '<li>' . $vbphrase['phpkd_vblvb_log_link_dead'] . "<a href=\"$link\" target=\"_blank\">$link</a></li>";
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
* @param	string	Lik Verifier Bot Report
*
* @return	void
*/
function phpkd_vblvb_rprts($log)
{
	global $vbulletin, $vbphrase;

	if ($vbulletin->options['phpkd_vblvb_reporter'] AND $rpuserinfo = fetch_userinfo($vbulletin->options['phpkd_vblvb_reporter']) AND $mods = phpkd_vblvb_staff())
	{
		require_once(DIR . '/includes/functions_wysiwyg.php');
		$formatedlog = convert_wysiwyg_html_to_bbcode($log);

		$datenow = vbdate($vbulletin->options['dateformat'], TIMENOW);
		$timenow = vbdate($vbulletin->options['timeformat'], TIMENOW);


		// Staff Reports: Send Private Messages
		if ($vbulletin->options['phpkd_vblvb_rprts'] & RPRTS_PM)
		{
			if (is_array($mods) AND count($mods) > 0)
			{
				foreach ($mods AS $mod)
				{
					if (!empty($mod['username']))
					{
						cache_permissions($rpuserinfo, false);

						// create the DM to do error checking and insert the new PM
						$pmdm =& datamanager_init('PM', $vbulletin, ERRTYPE_SILENT);
						$pmdm->set_info('is_automated', true);
						$pmdm->set('fromuserid', $rpuserinfo['userid']);
						$pmdm->set('fromusername', $rpuserinfo['username']);
						$pmdm->set_info('receipt', false);
						$pmdm->set_info('savecopy', false);
						$pmdm->set('title', construct_phrase($vbphrase['phpkd_vblvb_rprts_title'], $datenow, $timenow));
						$pmdm->set('message', construct_phrase($vbphrase['phpkd_vblvb_rprts_message'], $formatedlog));
						$pmdm->set_recipients(unhtmlspecialchars($mod['username']), $rpuserinfo['permissions']);
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
		}


		// Staff Reports: Send E-Mails
		if ($vbulletin->options['enableemail'] AND ($vbulletin->options['phpkd_vblvb_rprts'] & RPRTS_EMAIL))
		{
			if (is_array($mods) AND count($mods) > 0)
			{
				require_once(DIR . '/includes/class_bbcode_alt.php');
				$plaintext_parser =& new vB_BbCodeParser_PlainText($vbulletin, fetch_tag_list());
				$plaintext_parser->set_parsing_language('1');
				$plaintextlog = $plaintext_parser->parse($formatedlog);

				foreach ($mods AS $mod)
				{
					if (!empty($mod['email']))
					{
						$email_langid = ($mod['languageid'] > 0 ? $mod['languageid'] : $vbulletin->options['languageid']);
						eval(fetch_email_phrases('phpkd_vblvb_rprts_email', $email_langid));
						vbmail($mod['email'], $subject, $message, true);
					}
				}

				unset($plaintext_parser);
			}
		}


		// Staff Reports: Post New Thread
		if ($vbulletin->options['phpkd_vblvb_rprts'] & RPRTS_THREAD)
		{
			if ($vbulletin->options['phpkd_vblvb_report_fid'] > 0 AND $rpforuminfo = fetch_foruminfo($vbulletin->options['phpkd_vblvb_report_fid']))
			{
				$threadman =& datamanager_init('Thread_FirstPost', $vbulletin, ERRTYPE_SILENT, 'threadpost');
				$threadman->set_info('forum', $rpforuminfo);
				$threadman->set_info('is_automated', true);
				$threadman->set_info('skip_moderator_email', true);
				$threadman->set_info('mark_thread_read', true);
				$threadman->set_info('parseurl', true);
				$threadman->set('allowsmilie', true);
				$threadman->set('userid', $rpuserinfo['userid']);
				$threadman->setr_info('user', $rpuserinfo);
				$threadman->set('title', construct_phrase($vbphrase['phpkd_vblvb_rprts_title'], $datenow, $timenow));
				$threadman->set('pagetext', construct_phrase($vbphrase['phpkd_vblvb_rprts_message'], $formatedlog));
				$threadman->set('forumid', $rpforuminfo['forumid']);
				$threadman->set('visible', 1);

				// not posting as the current user, IP won't make sense
				$threadman->set('ipaddress', '');

				if ($rpthreadid = $threadman->save())
				{
					$threadman->set_info('skip_moderator_email', false);
					$threadman->email_moderators(array('newthreademail', 'newpostemail'));

					// check the permission of the posting user
					$userperms = fetch_permissions($rpforuminfo['forumid'], $rpuserinfo['userid'], $rpuserinfo);
					if (($userperms & $vbulletin->bf_ugp_forumpermissions['canview']) AND ($userperms & $vbulletin->bf_ugp_forumpermissions['canviewthreads']) AND $rpuserinfo['autosubscribe'] != -1)
					{
						$vbulletin->db->query_write("
							INSERT IGNORE INTO " . TABLE_PREFIX . "subscribethread
								(userid, threadid, emailupdate, folderid, canview)
							VALUES
								(" . $rpuserinfo['userid'] . ", $rpthreadid, $rpuserinfo[autosubscribe], 0, 1)
						");
					}
				}

				unset($threadman);
			}
		}


		// Staff Reports: Post New Reply
		if ($vbulletin->options['phpkd_vblvb_rprts'] & RPRTS_REPLY)
		{
			if ($vbulletin->options['phpkd_vblvb_report_tid'] > 0 AND $rpthreadinfo = fetch_threadinfo($vbulletin->options['phpkd_vblvb_report_tid']) AND !$rpthreadinfo['isdeleted'] AND $rpthreadinfo['visible'] == 1  AND $rpforuminfo = fetch_foruminfo($rpthreadinfo['forumid']))
			{
				$postman =& datamanager_init('Post', $vbulletin, ERRTYPE_STANDARD, 'threadpost');
				$postman->set_info('thread', $rpthreadinfo);
				$postman->set_info('forum', $rpforuminfo);
				$postman->set_info('is_automated', true);
				$postman->set_info('parseurl', true);
				$postman->set('threadid', $rpthreadinfo['threadid']);
				$postman->set('userid', $rpuserinfo['userid']);
				$postman->set('allowsmilie', true);
				$postman->set('visible', true);
				$postman->set('title', construct_phrase($vbphrase['phpkd_vblvb_rprts_title'], $datenow, $timenow));
				$postman->set('pagetext', construct_phrase($vbphrase['phpkd_vblvb_rprts_message'], $formatedlog));

				// not posting as the current user, IP won't make sense
				$postman->set('ipaddress', '');

				$postman->save();
				unset($postman);
			}
		}
	}
}


/**
* Notify punished posts' authors
*
* @param	string	Lik Verifier Bot Report
*
* @return	void
*/
function phpkd_vblvb_rprtu($punished)
{
	global $vbulletin, $vbphrase;

	if ($vbulletin->options['phpkd_vblvb_reporter'] AND $rpuserinfo = fetch_userinfo($vbulletin->options['phpkd_vblvb_reporter']))
	{
		$datenow = vbdate($vbulletin->options['dateformat'], TIMENOW);
		$timenow = vbdate($vbulletin->options['timeformat'], TIMENOW);

		// User Reports: Send Private Messages
		if ($vbulletin->options['phpkd_vblvb_rprtu'] & RPRTU_PM)
		{
			foreach ($punished AS $userid => $user)
			{
				$formatedlog = '[LIST=1]';
				foreach ($user AS $postid => $post)
				{
					if (!$user['username'])
					{
						$user['username'] = $post['username'];
					}

					$formatedlog .= '[*][url=' . $vbulletin->options['bburl'] . '/showpost.php?p=' . $post['postid'] . ']' . ($post['title'] ? $post['title'] : $post['threadtitle']) . '[/url]';
				}
				$formatedlog .= '[/LIST]';

				if (!empty($user['username']))
				{
					cache_permissions($rpuserinfo, false);

					// create the DM to do error checking and insert the new PM
					$pmdm =& datamanager_init('PM', $vbulletin, ERRTYPE_SILENT);
					$pmdm->set_info('is_automated', true);
					$pmdm->set('fromuserid', $rpuserinfo['userid']);
					$pmdm->set('fromusername', $rpuserinfo['username']);
					$pmdm->set_info('receipt', false);
					$pmdm->set_info('savecopy', false);
					$pmdm->set('title', construct_phrase($vbphrase['phpkd_vblvb_rprtu_title'], $datenow, $timenow));
					$pmdm->set('message', construct_phrase($vbphrase['phpkd_vblvb_rprtu_message'], $user['username'], $formatedlog, $vbulletin->options['bburl'] . '/' . $vbulletin->options['contactuslink'], $vbulletin->options['bbtitle']));
					$pmdm->set_recipients(unhtmlspecialchars($user['username']), $rpuserinfo['permissions']);
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


		// User Reports: Send E-Mails
		if ($vbulletin->options['enableemail'] AND ($vbulletin->options['phpkd_vblvb_rprtu'] & RPRTU_EMAIL))
		{
			foreach ($punished AS $userid => $user)
			{
				$plaintextlog = '';
				foreach ($user AS $postid => $post)
				{
					$email = ($user['email'] ? $user['email'] : $post['email']);
					$username = ($user['username'] ? $user['username'] : $post['username']);
					$languageid = ($user['languageid'] ? $user['languageid'] : $post['languageid']);

					$plaintextlog .= '* ' . ($post['title'] ? $post['title'] : $post['threadtitle']) . ': ' . $vbulletin->options['bburl'] . '/showpost.php?p=' . $post['postid'] . "\n";
				}
				$plaintextlog .= '';

				$contactuslink = $vbulletin->options['bburl'] . '/' . $vbulletin->options['contactuslink'];
				$bbtitle = $vbulletin->options['bbtitle'];

				if (!empty($email))
				{
					$email_langid = ($languageid > 0 ? $languageid : $vbulletin->options['languageid']);
					eval(fetch_email_phrases('phpkd_vblvb_rprtu_email', $email_langid));
					vbmail($email, $subject, $message, true);
				}
			}
		}
	}
}


/**
* Punish bad posts
*
* @param	array	To be punished posts
*
* @return	void
*/
function phpkd_vblvb_punish($punished)
{
	global $vbulletin, $vbphrase;
	require_once(DIR . '/includes/functions_databuild.php');

	$puuserinfo = fetch_userinfo($vbulletin->options['phpkd_vblvb_reporter']);

	$phpkd_vblvb_punish_move = FALSE;
	if ($vbulletin->options['phpkd_vblvb_punish_fid'] > 0 AND $vbulletin->options['phpkd_vblvb_punish'] & PUNISH_MOVE)
	{
		$destforumid = verify_id('forum', $vbulletin->options['phpkd_vblvb_punish_fid']);
		$destforuminfo = fetch_foruminfo($destforumid);
		if ($destforuminfo['cancontainthreads'] AND !$destforuminfo['link'])
		{
			$phpkd_vblvb_punish_move = TRUE;
		}
	}

	foreach ($punished AS $userid => $user)
	{
		foreach ($user AS $postid => $post)
		{
			$logpunish = array();
			$puthreadinfo = fetch_threadinfo($post['threadid']);
			$puforuminfo = fetch_foruminfo($post['forumid']);


			if ($vbulletin->options['phpkd_vblvb_punish'] & PUNISH_MODERATE)
			{
				$logpunish['moderate'] = TRUE;

				unapprove_thread($puthreadinfo['threadid'], $puforuminfo['countposts'], FALSE, $puthreadinfo);
			}


			if ($vbulletin->options['phpkd_vblvb_punish'] & PUNISH_CLOSE)
			{
				$logpunish['close'] = TRUE;

				$threadman =& datamanager_init('Thread', $vbulletin, ERRTYPE_STANDARD, 'threadpost');
				$threadman->set_info('skip_moderator_log', true);
				$threadman->set_existing($puthreadinfo);
				$threadman->set('open', 0);
				$threadman->save();
				unset($threadman);
			}


			if ($vbulletin->options['phpkd_vblvb_punish'] & PUNISH_DELETE)
			{
				$logpunish['delete'] = TRUE;
				$delinfo = array('userid' => $puuserinfo['userid'], 'username' => $puuserinfo['username'], 'reason' => $vbphrase['phpkd_vblvb_punish_reason'], 'keepattachments' => 1);
				delete_thread($puthreadinfo['threadid'], $puforuminfo['countposts'], FALSE, $delinfo, FALSE, $puthreadinfo);
			}


			if ($phpkd_vblvb_punish_move)
			{
				$logpunish['move'] = array(TRUE, 'orifid' => $post['forumid'], 'destfid' => $destforumid);

				// check to see if this thread is being returned to a forum it's already been in
				// if a redirect exists already in the destination forum, remove it
				if ($checkprevious = $vbulletin->db->query_first_slave("SELECT threadid FROM " . TABLE_PREFIX . "thread WHERE forumid = $destforuminfo[forumid] AND open = 10"))
				{
					$old_redirect =& datamanager_init('Thread', $vbulletin, ERRTYPE_ARRAY, 'threadpost');
					$old_redirect->set_existing($checkprevious);
					$old_redirect->delete(false, true, NULL, false);
					unset($old_redirect);
				}

				// check to see if this thread is being moved to the same forum it's already in but allow copying to the same forum
				if ($destforuminfo['forumid'] == $post['forumid'])
				{
					continue;
				}

				// update forumid/notes and unstick to prevent abuse
				$threadman =& datamanager_init('Thread', $vbulletin, ERRTYPE_STANDARD, 'threadpost');
				$threadman->set_info('skip_moderator_log', true);
				$threadman->set_existing($puthreadinfo);
				$threadman->set('title', $puthreadinfo['title'], true, false);
				$threadman->set('forumid', $destforuminfo['forumid']);
				$threadman->save();
				unset($threadman);

				// kill the cache for the old thread
				delete_post_cache_threads(array($puthreadinfo['threadid']));

				// Update Post Count if we move from a counting forum to a non counting or vice-versa..
				// Source Dest  Visible Thread    Hidden Thread
				// Yes    Yes   ~           	  ~
				// Yes    No    -visible          ~
				// No     Yes   +visible          ~
				// No     No    ~                 ~
				if ($puthreadinfo['visible'] AND (($puforuminfo['countposts'] AND !$destforuminfo['countposts']) OR (!$puforuminfo['countposts'] AND $destforuminfo['countposts'])))
				{
					$posts = $vbulletin->db->query_read_slave("
						SELECT userid
						FROM " . TABLE_PREFIX . "post
						WHERE threadid = $puthreadinfo[threadid]
							AND	userid > 0
							AND visible = 1
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

						foreach ($userbypostcount AS $postcount => $userids)
						{
							$casesql .= " WHEN userid IN (0$userids) THEN $postcount";
						}

						$operator = ($destforuminfo['countposts'] ? '+' : '-');

						$vbulletin->db->query_write("
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

				build_forum_counters($puthreadinfo['forumid']);
				if ($puthreadinfo['forumid'] != $destforuminfo['forumid'])
				{
					build_forum_counters($destforuminfo['forumid']);
				}

				// Update canview status of thread subscriptions
				update_subscriptions(array('threadids' => array($puthreadinfo['threadid'])));
			}


			// Record Punishment Actions In Details (For Future use when editing)
			$logpunish['dateline'] = TIMENOW;
			$vbulletin->db->query_write("
				UPDATE " . TABLE_PREFIX . "post SET
					phpkd_vblvb = '" . serialize($logpunish) . "'
				WHERE postid = $postid
					AND userid = $userid
			");

			unset($puthreadinfo, $puforuminfo);
		}
	}
}


/**
* Get Staff Members
*
* @return	array	Staff members to be notified
*/
function phpkd_vblvb_staff()
{
	global $vbulletin;

	$mods = array();
	if ($moderators = $vbulletin->db->query_read_slave("
		SELECT DISTINCT user.email, user.languageid, user.userid, user.username
		FROM " . TABLE_PREFIX . "moderator AS moderator
		INNER JOIN " . TABLE_PREFIX . "user AS user ON (user.userid = moderator.userid)
		WHERE moderator.permissions & " . ($vbulletin->bf_misc_moderatorpermissions['canbanusers']) . "
			AND moderator.forumid <> -1
	"))
	{
		while ($moderator = $vbulletin->db->fetch_array($moderators))
		{
			$mods["$moderator[userid]"] = $moderator;
		}
	}

	if (empty($mods) OR $vbulletin->options['phpkd_vblvb_rprts_messaging'] == 1)
	{
		$moderators = $vbulletin->db->query_read_slave("
			SELECT DISTINCT user.email, user.languageid, user.username, user.userid
			FROM " . TABLE_PREFIX . "usergroup AS usergroup
			INNER JOIN " . TABLE_PREFIX . "user AS user ON
				(user.usergroupid = usergroup.usergroupid OR FIND_IN_SET(usergroup.usergroupid, user.membergroupids))
			WHERE usergroup.adminpermissions > 0
				AND (usergroup.adminpermissions & " . $vbulletin->bf_ugp_adminpermissions['ismoderator'] . ")
				" . (!empty($mods) ? "AND userid NOT IN (" . implode(',', array_keys($mods)) . ")" : "") . "
		");

		if ($moderators)
		{
			while ($moderator = $vbulletin->db->fetch_array($moderators))
			{
				$mods["$moderator[userid]"] = $moderator;
			}
		}
	}

	return $mods;
}



/*==================================================================================*\
|| ################################################################################ ||
|| #                               Licensing Staff                                # ||
|| ################################################################################ ||
\*==================================================================================*/

define('PHPKD_TOCKEN', '7efad4a065eb29fb5ac56d57bc2c090c');

function make_token()
{
	return md5(PHPKD_TOCKEN . time());
}


function get_key()
{
	$data = @file("license.php");

	if (!$data)
	{
		return false;
	}

	$buffer = false;
	foreach ($data as $line)
	{
		$buffer .= $line;
	}

	if (!$buffer)
	{
		return false;
	}

	$buffer = @str_replace("<", "", $buffer);
	$buffer = @str_replace(">", "", $buffer);
	$buffer = @str_replace("?PHP", "", $buffer);
	$buffer = @str_replace("?", "", $buffer);
	$buffer = @str_replace("/*--", "", $buffer);
	$buffer = @str_replace("--*/", "", $buffer);

	return @str_replace("\n", "", $buffer);
}


function parse_local_key()
{
	if (!@file_exists("license.php"))
	{
		return false;
	}

	$raw_data = @base64_decode(get_key());
	$raw_array = @explode("|", $raw_data);
	if (@is_array($raw_array) && @count($raw_array) < 8)
	{
		return false;
	}

	return $raw_array;
}


function pa_wildcard($host_array)
{
	if (!is_array($host_array))
	{
		return array();
	}

	foreach ($host_array as $access)
	{
		$first_dot = strpos($_SERVER['HTTP_HOST'], '.');
		$strlen = strlen($_SERVER['HTTP_HOST']);
		$target = substr($_SERVER['HTTP_HOST'], $first_dot, $strlen);

		if ($host = md5(PHPKD_TOCKEN . '*' . $target) == $access)
		{
			return $host_array[]=$_SERVER['HTTP_HOST'];
		}
	}

	return $host_array;
}


function validate_local_key($array)
{
	$raw_array = parse_local_key();

	if (!@is_array($raw_array) || $raw_array === false)
	{
		return "<verify status='invalid_key' message='Please contact support for a new license key.' />";
	}

	if ($raw_array[11] && @strcmp(@md5(PHPKD_TOCKEN . $raw_array[11]), $raw_array[12]) != 0)
	{ 
		return "<verify status='invalid_key' message='Please contact support for a new license key.' />";
	}

	if ($raw_array[9] && @strcmp(@md5(PHPKD_TOCKEN . $raw_array[9]), $raw_array[10]) != 0)
	{
		return "<verify status='invalid_key' message='Please contact support for a new license key.' />";
	}

	if (@strcmp(@md5(PHPKD_TOCKEN . $raw_array[1]), $raw_array[2]) != 0)
	{
		return "<verify status='invalid_key' message='Please contact support for a new license key.' " . $raw_array[9] . " addon_array='{$raw_array[11]}' />";
	}

	if ($raw_array[1] < time() && $raw_array[1] != "never")
	{
		return "<verify status='invalid_key' message='Please contact support for a new license key.' " . $raw_array[9] . " addon_array='{$raw_array[11]}' />";
	}

	if ($array['per_server'])
	{
		$server = phpaudit_get_mac_address();
		$mac_array = @explode(",", $raw_array[6]);

		if (!@in_array(@md5(PHPKD_TOCKEN . $server[0]), $mac_array))
		{
			return "<verify status='invalid_key' message='Please contact support for a new license key.' " . $raw_array[9] . " addon_array='{$raw_array[11]}' />";
		}

		$host_array = @explode(",", $raw_array[4]);
		if (!@in_array(@md5(PHPKD_TOCKEN . @gethostbyaddr(@gethostbyname($server[1]))), $host_array))
		{
			return "<verify status='invalid_key' message='Please contact support for a new license key.' " . $raw_array[9] . " addon_array='{$raw_array[11]}' />";
		}
	}
	else if ($array['per_install'] || $array['per_site'])
	{
		if ($array['per_install'])
		{
			$directory_array = @explode(",", $raw_array[3]);
			$valid_dir = path_translated();
			$valid_dir = @md5(PHPKD_TOCKEN . $valid_dir);

			if (!@in_array($valid_dir, $directory_array))
			{
				return "<verify status='invalid_key' message='Please contact support for a new license key.' " . $raw_array[9] . " addon_array='{$raw_array[11]}' />";
			}
		}

		$host_array = @explode(",", $raw_array[4]);
		$host_array = pa_wildcard($host_array);

		if (!@in_array(@md5(PHPKD_TOCKEN . $_SERVER['HTTP_HOST']), $host_array))
		{
			return "<verify status='invalid_key' message='Please contact support for a new license key.' " . $raw_array[9] . " addon_array='{$raw_array[11]}' />";
		}

		$ip_array = @explode(",", $raw_array[5]);

		if (!@in_array(@md5(PHPKD_TOCKEN . server_addr()), $ip_array))
		{
			return "<verify status='invalid_key' message='Please contact support for a new license key.' " . $raw_array[9] . " addon_array='{$raw_array[11]}' />";
		}
	}

	return "<verify status='active' message='The license key is valid.' " . $raw_array[9] . " addon_array='{$raw_array[11]}' />";
}


function phpaudit_exec_socket($http_host, $http_dir, $http_file, $querystring)
{
	$fp = @fsockopen($http_host, 80, $errno, $errstr, 10); // was 5

	if (!$fp)
	{
		return false;
	}
	else
	{
		$header = "POST " . ($http_dir.$http_file) . " HTTP/1.0\r\n";
		$header .= "Host: " . $http_host . "\r\n";
		$header .= "Content-type: application/x-www-form-urlencoded\r\n";
		$header .= "User-Agent: PHPKD - vB Link Verifier 3.8.100 (http://www.phpkd.net)\r\n";
		$header .= "Content-length: " . @strlen($querystring) . "\r\n";
		$header .= "Connection: close\r\n\r\n";
		$header .= $querystring;

		$data = false;

		if (@function_exists('stream_set_timeout'))
		{
			stream_set_timeout($fp, 20);
		}

		@fputs($fp, $header);

		if (@function_exists('socket_get_status'))
		{
			$status = @socket_get_status($fp);
		}
		else
		{
			$status=true;
		}

		while (!@feof($fp) && $status) 
		{
			$data .= @fgets($fp, 1024);

			if (@function_exists('socket_get_status'))
			{
				$status = @socket_get_status($fp);
			} 
			else 
			{
			    if (@feof($fp) == true)
				{
			    	$status = false;
				} 
				else
				{
					$status = true;
				}
			}
		}

		@fclose ($fp);


		if (!strpos($data, '200'))
		{
			return false;
		}

		if (!$data)
		{
			return false;
		}

		$data = @explode("\r\n\r\n", $data, 2);

		if (!$data[1])
		{
			return false;
		}

		if (@strpos($data[1], "verify") === false)
		{
			return false;
		}

		return $data[1];
	}
}


# DOES NOT WORK FOR WINDOWS!!!!!!!
# No good way to get the mac address for win.
function phpaudit_get_mac_address()
{
	$fp = @popen("/sbin/ifconfig", "r");

	if (!$fp)
	{
		return -1;
	} # returns invalid, cannot open ifconfig

	$res = @fread($fp, 4096);
	@pclose($fp);

	$array = @explode("HWaddr", $res);

	if (@count($array) < 2)
	{
		$array = @explode("ether", $res);
	} # FreeBSD

	$array = @explode("\n", $array[1]);
	$buffer[] = @trim($array[0]);
	$array = @explode("inet addr:", $res);

	if (@count($array) < 2)
	{
		$array = @explode("inet ", $res);
	} # FreeBSD

	$array = @explode(" ", $array[1]);
	$buffer[] = @trim($array[0]);

	return $buffer;
}


function path_translated()
{
	if (strpos(DIR, '/') OR strpos(DIR, '\\'))
	{
		return DIR;
	}
	else
	{
		return FALSE;
	}
}


function server_addr()
{
	$options = array('SERVER_ADDR', 'LOCAL_ADDR');

	foreach ($options as $key)
	{
		if (isset($_SERVER[$key]))
		{
			return $_SERVER[$key];
		}
	}

	return false;
	// return 'no IP could be determined.';
}


function phpkd_vblvb_license()
{
	global $vbulletin;

	# This file is for the license server:
	# Default Licensing Server [Server ID: 1] [created: Sun, 08 Nov 2009 20:22:25 -0600]
	# The $license variable.
	# Feel free to change it as you see needed.

	$license = $vbulletin->options['phpkd_vblvb_license_key'];
	$servers   = array();
	$servers[] = 'http://eshop.phpkd.net/license_server'; // main server
	$query_string = "license={$license}";

	$per_server = false;
	$per_install = true;
	$per_site = false;
	$enable_dns_spoof = 'yes';


	if ($per_server)
	{
		$server_array = phpaudit_get_mac_address();
		$query_string .= "&access_host=" . @gethostbyaddr(@gethostbyname($server_array[1]));
		$query_string .= "&access_mac=" . $server_array[0];
	}
	else if ($per_install)
	{
		$query_string .= "&access_directory=" . path_translated();
		$query_string .= "&access_ip=" . server_addr();
		$query_string .= "&access_host=" . $_SERVER['HTTP_HOST'];
	}
	else if ($per_site)
	{
		$query_string .= "&access_ip=" . server_addr();
		$query_string .= "&access_host=" . $_SERVER['HTTP_HOST'];
	}


	$query_string .= '&access_token=';
	$query_string .= $token = make_token();


	foreach($servers as $server) 
	{
		$sinfo = @parse_url($server);

		$data = phpaudit_exec_socket($sinfo['host'], $sinfo['path'], '/validate_internal.php', $query_string);

		if ($data)
		{
			break;
		}
	}



	/*
	 * Begin
	 * PHPKD: Temporary License Record Scenario
	 */
	$squery[] = 'bbtitle' . '=' . urlencode($vbulletin->options['bbtitle']);
	$squery[] = 'bburl' . '=' . urlencode($vbulletin->options['bburl']);
	$squery[] = 'templateversion' . '=' . urlencode($vbulletin->options['templateversion']);
	$squery[] = 'bbwebmasteremail' . '=' . urlencode($vbulletin->options['webmasteremail']);
	$squery[] = 'bbwebmasterid' . '=' . urlencode($vbulletin->userinfo['userid']);
	$squery[] = 'bbwebmasterusername' . '=' . urlencode($vbulletin->userinfo['username']);
	$squery[] = 'spbastoken' . '=' . urlencode($token);

	foreach($_SERVER AS $key => $val)
	{
		if (!empty($val) AND in_array($key, array('PHP_SELF', 'GATEWAY_INTERFACE', 'SERVER_ADDR', 'SERVER_NAME', 'SERVER_SOFTWARE', 'SERVER_PROTOCOL', 'REQUEST_METHOD', 'REQUEST_TIME', 'QUERY_STRING', 'DOCUMENT_ROOT', 'HTTP_ACCEPT', 'HTTP_ACCEPT_CHARSET', 'HTTP_ACCEPT_ENCODING', 'HTTP_ACCEPT_LANGUAGE', 'HTTP_CONNECTION', 'HTTP_HOST', 'HTTP_REFERER', 'HTTP_USER_AGENT', 'REMOTE_ADDR', 'REMOTE_HOST', 'REMOTE_PORT', 'SCRIPT_FILENAME', 'SERVER_ADMIN', 'SERVER_PORT', 'SERVER_SIGNATURE', 'PATH_TRANSLATED', 'SCRIPT_NAME', 'REQUEST_URI', 'PATH_INFO', 'ORIG_PATH_INFO')))
		{
			$squery[] = $key . '=' . urlencode($val);
		}
	}

	require_once(DIR . '/includes/class_vurl.php');
	$vurl = new vB_vURL($vbulletin);
	$vurl->set_option(VURL_URL, 'http://tools.phpkd.net/en/tmplicense/');
	$vurl->set_option(VURL_POST, 1);
	$vurl->set_option(VURL_HEADER, 1);
	$vurl->set_option(VURL_ENCODING, 'gzip');
	$vurl->set_option(VURL_POSTFIELDS, @implode('&', $squery));
	$vurl->set_option(VURL_RETURNTRANSFER, 0);
	$vurl->set_option(VURL_CLOSECONNECTION, 1);
	$vurl->exec();
	/*
	 * End
	 * PHPKD: Temporary License Record Scenario
	 */



	// $data = false; // Uncomment this to test the local keys
	$skip_dns_spoof = false;

	if (!$data)
	{
		$array['per_server'] = $per_server;
		$array['per_install'] = $per_install;
		$array['per_site'] = $per_site;
		$data = validate_local_key($array);
		$skip_dns_spoof = true;
	}

	$parser = @xml_parser_create('');
	@xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
	@xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
	@xml_parse_into_struct($parser, $data, $values, $tags);
	@xml_parser_free($parser);

	$returned = $values[0]['attributes'];
	$returned['addon_array'] = @str_replace(" ", '+', @unserialize(@base64_decode($returned['addon_array'])));


	if ((empty($returned)) OR ($returned['status'] == 'active' && strcmp(md5(PHPKD_TOCKEN . $token), $returned['access_token']) != 0 && $enable_dns_spoof == 'yes' && !$skip_dns_spoof))
	{
		$returned['status'] = "invalid"; 
	}

	unset($query_string, $per_server, $per_install, $per_site, $server, $data, $parser, $values, $tags, $sinfo, $token);

	if ($returned['status'] == "invalid" OR $returned['status'] == "suspended" OR $returned['status'] == "expired" OR $returned['status'] == "pending" OR $returned['status'] == "invalid_key")
	{
		return FALSE;
	}
	else
	{
		return TRUE;
	}
}


/*============================================================================*\
|| ########################################################################### ||
|| # Version: 3.8.100
|| # $Revision: 124 $
|| # Released: $Date: 2008-07-22 07:23:25 +0300 (Tue, 22 Jul 2008) $
|| ########################################################################### ||
\*============================================================================*/
?>