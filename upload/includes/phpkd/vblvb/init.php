<?php
/*==================================================================================*\
|| ################################################################################ ||
|| # Product Name: vB Link Verifier Bot 'Ultimate'               Version: 4.0.132 # ||
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


foreach ($initparams AS $key => $value)
{
	if (!$value)
	{
		continue;
	}

	switch ($key)
	{
		case 'vbphrase':
			$this->vbphrase = $value;
			break;


		// Initialize Hooks
		case 'hooks':
			$this->hooks = array(
				'admin_options_print'      => TRUE,
				'admin_options_processing' => TRUE,
				'editpost_update_process'  => TRUE,
				'newpost_process'          => TRUE
			);
			break;


		// Checked Hosts
		case 'hosts':
			$rawhosts = array(
				// Last sylable of the link is optional & doesn't affect the URL validity either if not included or if entered wrong, so we can make it optional in the check regex also soon!
				'HOSTS1_2SHARED_COM'          => array('bitfield' => 1,         'active' => 1, 'status' => 'alive', 'urlmatch' => "2shared\.com\/file\/[0-9]+/[0-9a-z]+",                    'contentmatch' => "javascript:startDownload"),

				// Last sylable of the link is optional & doesn't affect the URL validity ONLY ONLY if not included -BUT BUT not if entered wrong-, so we can make it optional in the check regex also soon!
				'HOSTS1_4SHARED_COM'          => array('bitfield' => 2,         'active' => 1, 'status' => 'alive', 'urlmatch' => "4shared\.com\/(file|video|audio|photo|document|get)(\/[0-9]+)?\/[0-9a-z]+",             'contentmatch' => "(fileNameTextSpan|downloadDelayTimeSec)"),

				// Last sylable of the link is optional & doesn't affect the URL validity either if not included or if entered wrong, BUT BUT REQUIRES trailing slash '/' after the first numeric sylable if the second sylable didn't entered!.
				'HOSTS1_9Q9Q_NET'             => array('bitfield' => 4,         'active' => 1, 'status' => 'alive', 'urlmatch' => "9q9q\.net\/Download\/[0-9]+",                             'contentmatch' => "ReportAbuse"),

				'HOSTS1_ADRIVE_COM'           => array('bitfield' => 8,         'active' => 1, 'status' => 'alive', 'urlmatch' => "adrive\.com\/public\/[0-9a-z]+",                          'contentmatch' => "(download\.adrive\.com\/public\/view\/|Public File Busy)"),
				'HOSTS1_ARABSH_COM'           => array('bitfield' => 16,        'active' => 1, 'status' => 'alive', 'urlmatch' => "arabsh\.com\/[0-9a-z]+",                                  'contentmatch' => "(file_slot|download1info)"),
				'HOSTS1_AXIFILE_COM'          => array('bitfield' => 32,        'active' => 1, 'status' => 'alive', 'urlmatch' => "axifile\.com\/\?[0-9]+",                                  'contentmatch' => "mydownload\.php\?",                                          'urlsearch' => "@com\?@i",                                    'urlreplace' => "com/?"),
				'HOSTS1_BADONGO_COM'          => array('bitfield' => 64,        'active' => 1, 'status' => 'alive', 'urlmatch' => "badongo\.com\/([a-z]{2}\/)?(file|vid)\/[0-9]+",           'contentmatch' => "(fileBoxMenu|fileBoxDLink)",                                'urlsearch' => "@com\/([a-z]{2}\/)?(file|vid)\/@i",           'urlreplace' => "com/en/\\2/"),            

				// Last sylable of the link is optional & doesn't affect the URL validity either if not included or if entered wrong, BUT BUT if last sylable removed, the trailing slash '/' after the first sylable should be omited!.
				'HOSTS1_BITROAD_NET'          => array('bitfield' => 128,       'active' => 1, 'status' => 'alive', 'urlmatch' => "bitroad\.net\/download\/[0-9a-z]+\/",                     'contentmatch' => "download3\.php"),

				// This host's links should NOT end with a trailing slash '/'
				'HOSTS1_BOX_NET'              => array('bitfield' => 256,       'active' => 1, 'status' => 'alive', 'urlmatch' => "box\.net\/shared\/[0-9a-z]+",                             'contentmatch' => "box_download_shared_file"),

				'HOSTS1_COCOSHARE_CC'         => array('bitfield' => 512,       'active' => 1, 'status' => 'dead',  'urlmatch' => "cocoshare\.cc\/[0-9]+\/"),

				// Last sylable of the link is optional & doesn't affect the URL validity ONLY ONLY if not included -BUT BUT not if entered wrong-, so we can make it optional in the check regex also soon!
				'HOSTS1_CRAZYUPLOAD_COM'      => array('bitfield' => 1024,      'active' => 1, 'status' => 'alive', 'urlmatch' => "crazyupload\.com\/[0-9a-z]+",                             'contentmatch' => "(download1|download2)"),

				'HOSTS1_DEPOSITFILES_COM'     => array('bitfield' => 2048,      'active' => 1, 'status' => 'alive', 'urlmatch' => "depositfiles\.com\/([a-z]{2}\/)?files\/[0-9a-z]+",        'contentmatch' => "gateway_result|show_gold_offer",                                                 'urlsearch' => "@com\/([a-z]{2}\/)?files\/@i",                'urlreplace' => "com/en/files/"),

				// This host's links should NOT end with trailing slashe '/', Just put 'launch' after the 'download' phrase & you'l go to the download page directly without waiting!
				'HOSTS1_DIVSHARE_COM'         => array('bitfield' => 4096,      'active' => 1, 'status' => 'alive', 'urlmatch' => "divshare\.com\/download\/?[0-9-]+",                       'contentmatch' => "file_name"),

				// Last sylable of the link is optional & doesn't affect the URL validity ONLY ONLY if not included -BUT BUT not if entered wrong-, in such case the link should NOT contain a trailing slash '/' after the first sylable!
				'HOSTS1_DIVXDEN_COM'          => array('bitfield' => 8192,      'active' => 1, 'status' => 'alive', 'urlmatch' => "divxden\.com\/[0-9a-z]+",                                 'contentmatch' => "(download1|download2)"),

				// Last sylable of the link is optional & doesn't affect the URL validity ONLY ONLY if not included -BUT BUT not if entered wrong-, in such case the link should NOT contain a trailing slash '/' after the first sylable!
				'HOSTS1_DUALSHARE_COM'        => array('bitfield' => 16384,     'active' => 1, 'status' => 'dead', 'urlmatch' => "dualshare\.com\/[0-9a-z]+",                               'contentmatch' => "(download1|download2)"),

				// Last sylable -file name- required, but not included in the regex!
				'HOSTS1_DUCKLOAD_COM'         => array('bitfield' => 32768,     'active' => 1, 'status' => 'alive', 'urlmatch' => "duckload\.com\/download\/[0-9]+",            'contentmatch' => "index\.php\?Modul=download"),

				// If the '.html' has been removed from the URL, then the URL should NOT ends with a trailing slash '/'
				'HOSTS1_EARTHFILEZ_COM'       => array('bitfield' => 65536,     'active' => 1, 'status' => 'alive', 'urlmatch' => "earthfilez\.com\/[0-9a-z]+",                              'contentmatch' => "(download1|download2)"),

				// Last sylable of the link is optional & doesn't affect the URL validity either if not included or if entered wrong, so it's not required in the check regex!
				'HOSTS1_EASY_SHARE_COM'       => array('bitfield' => 131072,    'active' => 1, 'status' => 'alive', 'urlmatch' => "easy-share\.com\/[0-9]+",                                 'contentmatch' => "wcontent"),

				'HOSTS1_EGOSHARE_COM'         => array('bitfield' => 262144,    'active' => 1, 'status' => 'alive', 'urlmatch' => "egoshare\.com\/download.php\?id=[0-9a-z]+",               'contentmatch' => "download\.php"),

				// Last sylable of the link is optional & doesn't affect the URL validity ONLY ONLY if not entered (but not if entered wrong) AND in that case we should NOT include a trailing slash!
				'HOSTS1_ENTERUPLOAD_COM'      => array('bitfield' => 524288,    'active' => 1, 'status' => 'alive', 'urlmatch' => "enterupload\.com\/[0-9a-z]+",                             'contentmatch' => "(download1|download2)"),

				// Last sylable of the link is optional & doesn't affect the URL validity either if not included or if entered wrong, ONLY the first 12 characters are critical, other characters are ignored, so we can make it optional in the check regex also soon!
				'HOSTS1_EVILSHARE_COM'        => array('bitfield' => 1048576,   'active' => 1, 'status' => 'alive', 'urlmatch' => "evilshare\.com\/[0-9a-z]+",                               'contentmatch' => "(download1|download2)"),

				// This host's links should NOT end with a trailing slash '/'
				'HOSTS1_FILE2BOX_COM'         => array('bitfield' => 2097152,   'active' => 1, 'status' => 'alive', 'urlmatch' => "file2box\.com\/[0-9a-z]+",                                'contentmatch' => "(download1|download2)"),

				// Last sylable -file name- required, but not included in the regex!
				'HOSTS1_FILEBASE_TO'          => array('bitfield' => 4194304,   'active' => 1, 'status' => 'alive', 'urlmatch' => "filebase\.to\/files\/[0-9]+",                'contentmatch' => "(dl_free|dl_premium)"),

				// Note: HTTPS links seems to be not working!
				'HOSTS1_FILEBOX_COM'          => array('bitfield' => 8388608,   'active' => 1, 'status' => 'dead', 'urlmatch' => "filebox\.com\/[0-9a-z]+",                                 'contentmatch' => "splash_filedInfo"),

				// Last sylable of the link is optional & doesn't affect the URL validity ONLY ONLY if not included -BUT BUT not if entered wrong-, so we can make it optional in the check regex also soon!
				'HOSTS1_FILEDIP_COM'          => array('bitfield' => 16777216,  'active' => 1, 'status' => 'dead', 'urlmatch' => "filedip\.com\/[0-9a-z]+",                                 'contentmatch' => "(download1|download2)"),

				'HOSTS1_FILEDROPPER_COM'      => array('bitfield' => 33554432,  'active' => 1, 'status' => 'alive', 'urlmatch' => "filedropper\.com\/[0-9a-z_-]+",                           'contentmatch' => "\/processing\/filedownload\.php\?id="),

				// Last sylable of the link is optional & doesn't affect the URL validity either if not included or if entered wrong, so we can make it optional in the check regex also soon!
				'HOSTS1_FILEFACTORY_COM'      => array('bitfield' => 67108864,  'active' => 1, 'status' => 'alive', 'urlmatch' => "filefactory\.com\/file\/[0-9a-z]+",                       'contentmatch' => "metadata"),

				// This host's links shouldn't end with a trailing slash '/'
				'HOSTS1_FILEFLYER_COM'        => array('bitfield' => 134217728, 'active' => 1, 'status' => 'alive', 'urlmatch' => "fileflyer\.com\/view\/[0-9a-z]+",                         'contentmatch' => "(td_filename|td_filesize)"),

				// Last sylable of the link is optional & doesn't affect the URL validity either if not included or if entered wrong, so we can make it optional in the check regex also soon! This host sometimes return blank screen regardless it's status!!
				'HOSTS1_FILEFRONT_COM'        => array('bitfield' => 268435456, 'active' => 1, 'status' => 'alive', 'urlmatch' => "filefront\.com\/[0-9]+",                                  'contentmatch' => "fileinfotable"),

				'HOSTS1_FILEMOJO_COM'         => array('bitfield' => 536870912, 'active' => 1, 'status' => 'dead', 'urlmatch' => "filemojo\.com\/downloads\/[0-9a-z_-]+",                   'contentmatch' => "spnDownload"),



				// Last sylable of the link is optional & doesn't affect the URL validity ONLY ONLY if not included -BUT BUT not if entered wrong-, so we can make it optional in the check regex also soon!
				'HOSTS2_FILERAMA_COM'         => array('bitfield' => 1,         'active' => 1, 'status' => 'dead', 'urlmatch' => "filerama\.com\/[0-9a-z]+",                                'contentmatch' => "(download1|download2)"),

				'HOSTS2_FILESERVER_CC'        => array('bitfield' => 2,         'active' => 1, 'status' => 'alive', 'urlmatch' => "fileserver\.cc\/[0-9a-z]+",                               'contentmatch' => "(download1|download2)"),
				'HOSTS2_FILESOVERMILES_COM'   => array('bitfield' => 4,         'active' => 1, 'status' => 'alive', 'urlmatch' => "filesovermiles\.com\/[0-9a-z]+",                          'contentmatch' => "fileinfo"),
				'HOSTS2_FILESPLASH_COM'       => array('bitfield' => 8,         'active' => 1, 'status' => 'alive', 'urlmatch' => "filesplash\.com\/[0-9a-z]+",                              'contentmatch' => "file_slot"),
				'HOSTS2_FILES_TO'             => array('bitfield' => 16,        'active' => 1, 'status' => 'alive', 'urlmatch' => "files\.to\/get\/[0-9]+\/[0-9a-z]+",                       'contentmatch' => "downloadname"),
				'HOSTS2_FLYUPLOAD_COM'        => array('bitfield' => 32,        'active' => 1, 'status' => 'dead',  'urlmatch' => "flyupload\.com\/get\?fid=[0-9]+"),
				'HOSTS2_GIGASIZE_COM'         => array('bitfield' => 64,        'active' => 1, 'status' => 'alive', 'urlmatch' => "gigasize\.com\/get\.php\?d=[0-9a-z]+",                    'contentmatch' => "dldcontent"),

				'HOSTS2_HOSTUJE_NET'          => array('bitfield' => 128,       'active' => 1, 'status' => 'alive', 'urlmatch' => "hostuje\.net\/file\.php\?id=[0-9a-z]+",                   'contentmatch' => "obraz\.php"),

				// Last sylable of the link is optional & doesn't affect the URL validity either if not included or if entered wrong, BUT BUT REQUIRES trailing slash '/' after the second sylable if the third sylable didn't entered!.
				'HOSTS2_HOTFILE_COM'          => array('bitfield' => 256,       'active' => 1, 'status' => 'alive', 'urlmatch' => "hotfile\.com\/dl\/[0-9]+\/[0-9a-z]+",                     'contentmatch' => "downloading"),

				'HOSTS2_HULKSHARE_COM'        => array('bitfield' => 512,       'active' => 1, 'status' => 'alive', 'urlmatch' => "hulkshare\.com\/[0-9a-z]+",                               'contentmatch' => "file_slot"),

				// Last sylable of the link is optional & doesn't affect the URL validity either if not included or if entered wrong, so we can make it optional in the check regex also soon!
				'HOSTS2_IFILE_IT'             => array('bitfield' => 1024,      'active' => 0, 'status' => 'dead', 'urlmatch' => "ifile\.it\/[0-9a-z]+",                                    'contentmatch' => "req_btn"),

				'HOSTS2_IFOLDER_RU'           => array('bitfield' => 2048,      'active' => 1, 'status' => 'alive', 'urlmatch' => "ifolder\.ru\/[0-9]+",                                     'contentmatch' => "confirmed_number"),
				'HOSTS2_JUMBOFILES_COM'       => array('bitfield' => 4096,      'active' => 1, 'status' => 'alive', 'urlmatch' => "jumbofiles\.com\/[0-9a-z]+",                              'contentmatch' => "(download1|download2)"),

				// Last sylable of the link doesn't affect URL validity ONLY ONLY if entered wrong -but not if ignored-, AND AND REQUIRES trailing '.html' after the last sylable!.
				'HOSTS2_LETITBIT_NET'         => array('bitfield' => 8192,      'active' => 1, 'status' => 'alive', 'urlmatch' => "letitbit\.net\/download\/[0-9]+\.[0-9a-z]+\/[0-9a-z_-]+", 'contentmatch' => "download4\.php"),

				// Last sylable of the link is optional & doesn't affect the URL validity either if not included or if entered wrong, BUT BUT REQUIRES trailing slash '/' after the first numeric sylable if the second sylable didn't entered!.
				'HOSTS2_LOAD_TO'              => array('bitfield' => 16384,     'active' => 1, 'status' => 'alive', 'urlmatch' => "load\.to\/[0-9a-z]+",                                     'contentmatch' => "download_table"),

				'HOSTS2_LOOMBO_COM'           => array('bitfield' => 32768,     'active' => 1, 'status' => 'alive', 'urlmatch' => "loombo\.com\/[0-9a-z]+",                                  'contentmatch' => "(download1|download2)"),
				'HOSTS2_M5ZN_COM'             => array('bitfield' => 65536,     'active' => 1, 'status' => 'alive', 'urlmatch' => "m5zn\.com\/files-[0-9a-z]+",                          'contentmatch' => "download_code"),
				'HOSTS2_MEDIAFIRE_COM'        => array('bitfield' => 131072,    'active' => 1, 'status' => 'alive', 'urlmatch' => "mediafire\.com\/(download\.php)?\?[0-9a-z]+",             'contentmatch' => "download_file_title",   'urlsearch' => "@download\.php@i", 'urlreplace' => ""),
				'HOSTS2_MEGASHARE_COM'        => array('bitfield' => 262144,    'active' => 0, 'status' => 'dead', 'urlmatch' => "megashare\.com\/[0-9]+",                                  'contentmatch' => "(PremDz|FreePremDz|FreeDz)"),

				// After massive checking for multiple links, megaupload restrict access for the same IP address for a specific time period, the following message displayed:
				// We have detected an elevated number of requests from your IP address. This may be caused by improperly designed third-party software. A temporary access restriction is in place. Please check back in XX minutes or log in using your premium account. (IP: 41.196.231.1)
				'HOSTS2_MEGAUPLOAD_COM'       => array('bitfield' => 524288,    'active' => 1, 'status' => 'alive', 'urlmatch' => "megaupload\.com\/\?d=[0-9a-z]+(&setlang=[a-z]{2}\/)?",    'contentmatch' => "(javascript:checkcaptcha|All download slots assigned to your country)",   'urlsearch' => "@&setlang=[a-z]{2}@i", 'urlreplace' => "&setlang=en", 'downmatch' => "temporary access restriction is in place"),

				'HOSTS2_MIDUPLOAD_COM'        => array('bitfield' => 1048576,   'active' => 0, 'status' => 'dead', 'urlmatch' => "midupload\.com\/[0-9a-z]+",                               'contentmatch' => "(download1|download2)"),

				// Last sylable of the link is optional & doesn't affect the URL validity ONLY ONLY if not included -BUT BUT not if entered wrong-, so we can make it optional in the check regex also soon!
				'HOSTS2_MLFAT4ARAB_COM'       => array('bitfield' => 2097152,   'active' => 1, 'status' => 'alive', 'urlmatch' => "mlfat4arab\.com\/[0-9a-z]+",                              'contentmatch' => "(download1|download2)"),

				'HOSTS2_MOIDISK_RU'           => array('bitfield' => 4194304,   'active' => 1, 'status' => 'alive', 'urlmatch' => "moidisk\.ru\/[0-9a-z]+",                                  'contentmatch' => "(download1|download2)"),
				'HOSTS2_MOMUPLOAD_COM'        => array('bitfield' => 8388608,   'active' => 1, 'status' => 'dead',  'urlmatch' => "momupload\.com\/files\/"),

				// Last sylable of the link is optional & doesn't affect the URL validity ONLY ONLY if not included -BUT BUT not if entered wrong-, so we can make it optional in the check regex also soon!
				'HOSTS2_MY2SHARE_COM'         => array('bitfield' => 16777216,  'active' => 1, 'status' => 'alive', 'urlmatch' => "my2share\.com\/[0-9a-z]+",                                'contentmatch' => "(download1|download2)"),

				// Last sylable of the link is optional & doesn't affect the URL validity either if not included or if entered wrong, BUT BUT REQUIRES .htm after the first sylable if the second sylable didn't entered!.
				'HOSTS2_NETLOAD_IN'           => array('bitfield' => 33554432,  'active' => 1, 'status' => 'alive', 'urlmatch' => "netload\.in\/datei[0-9a-z]+",                             'contentmatch' => "dl_first_file_download", 'urlsearch' => "@&lang=[a-zA-Z]{2}@i", 'urlreplace' => "&lang=en"),

				// Last sylable of the link is optional & doesn't affect the URL validity ONLY ONLY if not included -BUT BUT not if entered wrong-, so we can make it optional in the check regex also soon!
				'HOSTS2_ONLINE_SHARING_NET'   => array('bitfield' => 67108864,  'active' => 1, 'status' => 'dead', 'urlmatch' => "online-sharing\.net\/[0-9a-z]+",                          'contentmatch' => "(download1|download2)"),

				// Last sylable of the link is optional & doesn't affect the URL validity ONLY ONLY if not included -BUT BUT not if entered wrong-, so we can make it optional in the check regex also soon!
				'HOSTS2_PRODDL_COM'           => array('bitfield' => 134217728, 'active' => 1, 'status' => 'alive', 'urlmatch' => "proddl\.com\/[0-9a-z]+",                                  'contentmatch' => "(download1|download2)"),

				'HOSTS2_PRZEKLEJ_PL'          => array('bitfield' => 268435456, 'active' => 1, 'status' => 'alive', 'urlmatch' => "przeklej\.pl\/plik\/[0-9a-z_-]+",                         'contentmatch' => "download-file"),
				'HOSTS2_PRZESLIJ_NET'         => array('bitfield' => 536870912, 'active' => 1, 'status' => 'alive', 'urlmatch' => "przeslij\.net\/download\.php\?file=[0-9a-z_-]+",          'contentmatch' => "countdown"),



				// Last sylable of the link is optional & doesn't affect the URL validity ONLY ONLY if not included -BUT BUT not if entered wrong-, so we can make it optional in the check regex also soon!
				'HOSTS3_QUICKUPLOAD_NET'      => array('bitfield' => 1,         'active' => 1, 'status' => 'alive', 'urlmatch' => "quickupload\.net\/[0-9a-z]+",                             'contentmatch' => "(download1|download2)"),

				'HOSTS3_RAPIDSHARE_COM'       => array('bitfield' => 2,         'active' => 1, 'status' => 'alive', 'urlmatch' => "rapidshare\.com\/files\/[0-9]+\/[0-9a-z_-]+",             'contentmatch' => "downloadlink"),
				'HOSTS3_RAPIDSHARE_DE'        => array('bitfield' => 4,         'active' => 1, 'status' => 'dead', 'urlmatch' => "rapidshare\.de\/files\/[0-9]+\/[0-9a-z_-]+",              'contentmatch' => "dl\.start"),

				// Last sylable of the link is optional & doesn't affect the URL validity either if not included or if entered wrong, BUT BUT REQUIRES trailing slash '/' after the first numeric sylable if the second sylable didn't entered!.
				'HOSTS3_RNBLOAD_COM'          => array('bitfield' => 8,         'active' => 1, 'status' => 'alive', 'urlmatch' => "rnbload\.com\/file\/[0-9]+",                              'contentmatch' => "downloadfile"),

				'HOSTS3_SAVEFILE_COM'         => array('bitfield' => 16,        'active' => 1, 'status' => 'dead',  'urlmatch' => "savefile\.com\/files\/"),
				'HOSTS3_SENDSPACE_COM'        => array('bitfield' => 32,        'active' => 1, 'status' => 'alive', 'urlmatch' => "sendspace\.com\/file\/[0-9a-z]+",                         'contentmatch' => "REGULAR DOWNLOAD"),
				'HOSTS3_SENDUIT_COM'          => array('bitfield' => 64,        'active' => 1, 'status' => 'alive', 'urlmatch' => "senduit\.com\/[0-9a-z]+",                                 'contentmatch' => "\/file\/"),
				'HOSTS3_SPEEDYSHARE_COM'      => array('bitfield' => 128,       'active' => 1, 'status' => 'alive', 'urlmatch' => "speedyshare\.com\/files\/[0-9]+\/[0-9a-z_-]+",            'contentmatch' => "\/download\/"),

				// Last sylable of the link is optional & doesn't affect the URL validity either if not included or if entered wrong, so we can make it optional in the check regex also soon!
				'HOSTS3_STORAGE_TO'           => array('bitfield' => 256,       'active' => 1, 'status' => 'alive', 'urlmatch' => "storage\.to\/get\/[0-9a-z]+",                             'contentmatch' => "download_container", 'urlsearch' => "@\?language=[a-zA-Z]{2}@i", 'urlreplace' => "?language=en"),

				// Last sylable of the link is optional & doesn't affect the URL validity ONLY ONLY if not included -BUT BUT not if entered wrong-, so we can make it optional in the check regex also soon!
				'HOSTS3_SUPERFASTFILE_COM'    => array('bitfield' => 512,       'active' => 1, 'status' => 'alive', 'urlmatch' => "superfastfile\.com\/[0-9a-z]+",                           'contentmatch' => "(download1|download2)"),

				// Last sylable of the link is optional & doesn't affect the URL validity ONLY ONLY if not included -BUT BUT not if entered wrong-, in such case the link should NOT contain a trailing slash '/' after the first sylable!
				'HOSTS3_TERADEPOT_COM'        => array('bitfield' => 1024,      'active' => 1, 'status' => 'alive', 'urlmatch' => "teradepot\.com\/[0-9a-z]+",                               'contentmatch' => "(download1|download2)"),

				'HOSTS3_TRANSFERBIGFILES_COM' => array('bitfield' => 2048,      'active' => 1, 'status' => 'alive', 'urlmatch' => "transferbigfiles\.com\/[0-9a-z-]+\?",         'contentmatch' => "downFileList"),
				'HOSTS3_TURBOUPLOAD_COM'      => array('bitfield' => 4096,      'active' => 1, 'status' => 'alive', 'urlmatch' => "turboupload\.com\/[0-9a-z]+",                             'contentmatch' => "(download1|download2)"),

				// Last sylable of the link is optional & doesn't affect the URL validity ONLY ONLY if entered wrong -BUT BUT not if ignored-, it should has at least one character, so we can make it optional in the check regex also soon!
				'HOSTS3_UPFORDOWN_COM'        => array('bitfield' => 8192,      'active' => 1, 'status' => 'alive', 'urlmatch' => "upfordown\.com\/public\/pdownload\/[0-9]+\/[0-9a-z_-]+",  'contentmatch' => "divDLStart"),

				'HOSTS3_UPLOADED_TO'          => array('bitfield' => 16384,     'active' => 1, 'status' => 'alive', 'urlmatch' => "(uploaded\.to|ul\.to)\/(\?id=|file\/)?[0-9a-z]+",         'contentmatch' => "(download_form|inputActive)"),

				// Last sylable of the link is optional & doesn't affect the URL validity either if not included or if entered wrong, so we can make it optional in the check regex also soon!
				// This host sometimes goes down & display the following message: Service Not Available. We're sorry. Our service is temporarily unavailable. Everything is fine, but we'd appreciate your patience while our techs correct the situation. Please try accessing your account in a few minutes.
				'HOSTS3_UPLOADING_COM'        => array('bitfield' => 32768,     'active' => 1, 'status' => 'alive', 'urlmatch' => "uploading\.com\/files\/[0-9a-z]+",                        'contentmatch' => "downloadform", 'downmatch' => "Service Not Available"),

				'HOSTS3_UPLOADPALACE_COM'     => array('bitfield' => 65536,     'active' => 1, 'status' => 'dead',  'urlmatch' => "uploadpalace\.com\/[a-zA-Z]{2}\/file\/[0-9]+\/"),
				'HOSTS3_USAUPLOAD_NET'        => array('bitfield' => 131072,    'active' => 1, 'status' => 'dead',  'urlmatch' => "usaupload\.net\/d\/"),

				// This host's links shouldn't end with a trailing slash '/'
				'HOSTS3_USERSHARE_NET'        => array('bitfield' => 262144,    'active' => 1, 'status' => 'alive', 'urlmatch' => "usershare\.net\/[0-9a-z]+",                               'contentmatch' => "(download1|download2)"),

				// Last sylable of the link is optional & doesn't affect the URL validity ONLY ONLY if not included -BUT BUT not if entered wrong-, in such case the link should NOT contain a trailing slash '/' after the first sylable!
				'HOSTS3_USEUPLOAD_COM'        => array('bitfield' => 524288,    'active' => 1, 'status' => 'alive', 'urlmatch' => "useupload\.com\/[0-9a-z]+",                               'contentmatch' => "(download1|download2)"),

				// Last sylable of the link doesn't affect URL validity ONLY ONLY if entered wrong -but not if ignored-, AND AND REQUIRES trailing '.html' after the last sylable!.
				'HOSTS3_VIP_FILE_COM'         => array('bitfield' => 1048576,   'active' => 1, 'status' => 'alive', 'urlmatch' => "vip-file\.com\/download\/[0-9]+\.[0-9a-z]+\/[0-9a-z_-]+", 'contentmatch' => "(getfreelink|sms\/check\.php)"),

				// Only first siz characters are critical, any other added characters are ignored!!
				'HOSTS3_X7_TO'                => array('bitfield' => 2097152,   'active' => 1, 'status' => 'alive', 'urlmatch' => "x7\.to\/[0-9a-z]+",                                       'contentmatch' => "requestTicket"),

				// Last sylable of the link is optional & doesn't affect the URL validity ONLY ONLY if not included -BUT BUT not if entered wrong-, so we can make it optional in the check regex also soon!
				'HOSTS3_XINONY_COM'           => array('bitfield' => 4194304,   'active' => 1, 'status' => 'alive', 'urlmatch' => "xinony\.com\/[0-9a-z]+",                                  'contentmatch' => "(download1|download2)"),

				// There's three different formats for this host's URLs
				'HOSTS3_YOUSENDIT_COM'        => array('bitfield' => 8388608,   'active' => 1, 'status' => 'alive', 'urlmatch' => "yousendit\.com\/((download\/|transfer\.php\?action=batch_download&batch_id=)[0-9a-z]+|transfer\.php\?action=batch_download&send_id=[0-9]+&email=[0-9a-z]+)", 'contentmatch' => "download-button"),

				// Last sylable of the link is optional & doesn't affect the URL validity either if not included or if entered wrong, BUT BUT REQUIRES trailing slash '/' after the first numeric sylable if the second sylable didn't entered!.
				'HOSTS3_ZIDDU_COM'            => array('bitfield' => 16777216,  'active' => 1, 'status' => 'alive', 'urlmatch' => "ziddu\.com\/download\/[0-9]+",                            'contentmatch' => "downloads\.ziddu\.com\/downloadfile"),

				'HOSTS3_ZIPPYSHARE_COM'       => array('bitfield' => 33554432,  'active' => 1, 'status' => 'alive', 'urlmatch' => "zippyshare\.com\/(v\/[0-9]+\/file.html|view.jsp\?locale=[a-zA-Z]{2}&key=[0-9]+)",                   'contentmatch' => "wait", 'urlsearch' => "@view.jsp\?locale=[a-zA-Z]{2}@i",           'urlreplace' => "view.jsp?locale=en"),
				'HOSTS3_ZOMGUPLOAD_COM'       => array('bitfield' => 67108864,  'active' => 1, 'status' => 'alive', 'urlmatch' => "zomgupload\.com\/[0-9a-z]+",                              'contentmatch' => "(download1|download2)"),
				'HOSTS3_ZSHARE_NET'           => array('bitfield' => 134217728, 'active' => 1, 'status' => 'alive', 'urlmatch' => "zshare\.net\/(download|audio|video)\/[0-9a-z]+",          'contentmatch' => "download\.gif")
			);

			foreach ($rawhosts AS $key => $value)
			{
				if (!$value['active'])
				{
					continue;
				}

				$switch = substr($key, 0, 6);
				switch ($switch)
				{
					case 'HOSTS1':
						if ($this->registry->options['phpkd_vblvb_hosts'] & $value['bitfield'])
						{
							$this->hosts[] = array('urlmatch' => $value['urlmatch'], 'active' => $value['active'], 'status' => $value['status'], 'contentmatch' => $value['contentmatch'], 'urlsearch' => $value['urlsearch'], 'urlreplace' => $value['urlreplace']);
						}
						break;
					case 'HOSTS2':
						if ($this->registry->options['phpkd_vblvb_hosts2'] & $value['bitfield'])
						{
							$this->hosts[] = array('urlmatch' => $value['urlmatch'], 'active' => $value['active'], 'status' => $value['status'], 'contentmatch' => $value['contentmatch'], 'urlsearch' => $value['urlsearch'], 'urlreplace' => $value['urlreplace']);
						}
						break;
					case 'HOSTS3':
						if ($this->registry->options['phpkd_vblvb_hosts3'] & $value['bitfield'])
						{
							$this->hosts[] = array('urlmatch' => $value['urlmatch'], 'active' => $value['active'], 'status' => $value['status'], 'contentmatch' => $value['contentmatch'], 'urlsearch' => $value['urlsearch'], 'urlreplace' => $value['urlreplace']);
						}
						break;
				}
			}
			break;


		// Detected Masks
		case 'masks':
			$rawmasks = array(
				'anonym.to'      => array('bitfield' => 1, 'urlmatch' => "#^(http)\:\/\/(www\.)?anonym\.to\/\?#i"),
				'lix.in'         => array('bitfield' => 2, 'urlmatch' => "#^(http)\:\/\/(www\.)?lix\.in\/#i"),
				'linkbucks.com'  => array('bitfield' => 4, 'urlmatch' => "#^(http)\:\/\/(www\.)?linkbucks\.com\/link\/#i"),
				'rapidshare.com' => array('bitfield' => 8, 'urlmatch' => "#rapidshare\.com\/users\/#i")
			);

			foreach ($rawmasks AS $key => $value)
			{
				if ($this->registry->options['phpkd_vblvb_masks'] & $value['bitfield'])
				{
					$this->masks[$key] = $value['urlmatch'];
				}
			}
			break;


		// Staff Reports
		case 'staff_reports':
			$rawrprts = array(
				'RPRTS_PM'     => array('bitfield' => 1),
				'RPRTS_EMAIL'  => array('bitfield' => 2),
				'RPRTS_THREAD' => array('bitfield' => 4),
				'RPRTS_REPLY'  => array('bitfield' => 8)
			);

			foreach ($rawrprts AS $key => $value)
			{
				if ($this->registry->options['phpkd_vblvb_rprts'] & $value['bitfield'])
				{
					$this->staff_reports[$key] = $value['bitfield'];
				}
			}
			break;


		// User Reports
		case 'user_reports':
			$rawrprtu = array(
				'RPRTU_PM'     => array('bitfield' => 1),
				'RPRTU_EMAIL'  => array('bitfield' => 2)
			);

			foreach ($rawrprtu AS $key => $value)
			{
				if ($this->registry->options['phpkd_vblvb_rprtu'] & $value['bitfield'])
				{
					$this->user_reports[$key] = $value['bitfield'];
				}
			}
			break;


		// TODO: Punishment methods
		case 'punishments':
			$rawpunishments = array(
				'PUNISH_MODERATE' => array('bitfield' => 1),
				'PUNISH_CLOSE'    => array('bitfield' => 2),
				'PUNISH_MOVE'     => array('bitfield' => 4),
			//	'PUNISH_DELETE'   => array('bitfield' => 8)
			);

			foreach ($rawpunishments AS $key => $value)
			{
				if ($this->registry->options['phpkd_vblvb_punish'] & $value['bitfield'])
				{
					$this->punishments[] = $key;
				}
			}
			break;


		// TODO: Checked protocols
		case 'protocols':
			$rawprotocols = array(
				'HTTP'   => array('bitfield' => 1),
				'HTTPS'  => array('bitfield' => 2),
				'FTP'    => array('bitfield' => 4),
				// 'GOPHER' => array('bitfield' => 8),
				// 'NEWS'   => array('bitfield' => 16),
				// 'TELNET' => array('bitfield' => 32)
			);

			foreach ($rawprotocols AS $key => $value)
			{
				if ($this->registry->options['phpkd_vblvb_protocols'] & $value['bitfield'])
				{
					$this->protocols[] = $key;
				}
			}
			break;


		// Checked thread modes!
		case 'threadmodes':
			$rawthreadmodes = array(
				'OPENED'     => array('bitfield' => 1),
				'CLOSED'     => array('bitfield' => 2),
				'MODERATED'  => array('bitfield' => 4),
				'STICKY'     => array('bitfield' => 8),
				'DELETED'    => array('bitfield' => 16),
				'REDIRECTED' => array('bitfield' => 16)
			);

			foreach ($rawthreadmodes AS $key => $value)
			{
				if ($this->registry->options['phpkd_vblvb_threadmodes'] & $value['bitfield'])
				{
					$this->threadmodes[] = $key;
				}
			}
			break;


		// Checked post modes!
		case 'postmodes':
			$rawpostmodes = array(
				'OPENED'    => array('bitfield' => 1),
				'MODERATED' => array('bitfield' => 2),
				'DELETED'   => array('bitfield' => 4)
			);

			foreach ($rawpostmodes AS $key => $value)
			{
				if ($this->registry->options['phpkd_vblvb_postmodes'] & $value['bitfield'])
				{
					$this->postmodes[] = $key;
				}
			}
			break;


		// Checked BBCodes
		case 'bbcodes':
			$rawbbcodes = array(
				'BASIC'  => array('bitfield' => 1,    'open' => '\[b|\[i|\[u',                              'close' => '\[/b|\[/i|\[/u'),
				'COLOR'  => array('bitfield' => 2,    'open' => '\[color|\[highlight',                      'close' => '\[/color|\[/highlight'),
				'SIZE'   => array('bitfield' => 4,    'open' => '\[size',                                   'close' => '\[/size'),
				'FONT'   => array('bitfield' => 8,    'open' => '\[font',                                   'close' => '\[/font'),
				'ALIGN'  => array('bitfield' => 16,   'open' => '\[left|\[center|\[right|\[indent|\[align', 'close' => '\[/left|\[/center|\[/right|\[/indent|\[/align'),
				'LIST'   => array('bitfield' => 32,   'open' => '\[\*'),
				'LINK'   => array('bitfield' => 64),
				'CODE'   => array('bitfield' => 128,  'open' => '\[code',                                   'close' => '\[/code'),
				'PHP'    => array('bitfield' => 256,  'open' => '\[php',                                    'close' => '\[/php'),
				'HTML'   => array('bitfield' => 512,  'open' => '\[html',                                   'close' => '\[/html'),
				'QUOTE'  => array('bitfield' => 1024, 'open' => '\[quote',                                  'close' => '\[/quote'),
				'HIDE'   => array('bitfield' => 2048, 'open' => '\[hide',                                   'close' => '\[/hide'),
				'CHARGE' => array('bitfield' => 4096, 'open' => '\[charge',                                 'close' => '\[/charge'),
			);

			foreach ($rawbbcodes AS $key => $value)
			{
				if ($this->registry->options['phpkd_vblvb_bbcodes'] & $value['bitfield'])
				{
					$this->bbcodes[$key] = array('open' => $value['open'], 'close' => $value['close']);
				}
			}
			break;


		default:
			// Do nothing!
			break;
	}
}


/*============================================================================*\
|| ########################################################################### ||
|| # Version: 4.0.132
|| # $Revision$
|| # Released: $Date$
|| ########################################################################### ||
\*============================================================================*/