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


if (!defined('VB_AREA'))
{
	echo 'Can not be called from outside vBulletin Framework!';
	exit;
}

define('PHPKD_PRODUCT',        'phpkd_vblvb');
define('PHPKD_VBLVB_VERSION',  '4.0.132');
define('PHPKD_VBLVB_SVERSION', '40132');
define('PHPKD_VBLVB_TOCKEN',   '7efad4a065eb29fb5ac56d57bc2c090c');


/**
 * Core class
 *
 * @package vB Link Verifier Bot 'Pro' Edition
 * @author PHP KingDom Development Team
 * @version $Revision$
 * @since $Date$
 * @copyright PHP KingDom (PHPKD)
 */
class PHPKD_VBLVB
{
	/**
	* Array of valid hosts to be checked
	*
	* @var	array
	*/
	var $hosts = array();


	/**
	* Array of valid masks to be detected
	*
	* @var	array
	*/
	var $masks = array();


	/**
	* Array of valid punishment methods to be applied
	*
	* @var	array
	*/
	var $punishments = array();


	/**
	* Array of valid staff reports to be sent/posted
	*
	* @var	array
	*/
	var $staff_reports = array();


	/**
	* Array of valid user reports to be sent/posted
	*
	* @var	array
	*/
	var $user_reports = array();


	/**
	* Array of valid protocols
	*
	* @var	array
	*/
	var $protocols = array();


	/**
	* Array of valid Thread Modes
	*
	* @var	array
	*/
	var $threadmodes = array();


	/**
	* Array of valid Post Modes
	*
	* @var	array
	*/
	var $postmodes = array();


	/**
	* Array of valid BBCodes
	*
	* @var	array
	*/
	var $bbcodes = array();


	/**
	* Array of valid hooks
	*
	* @var	array
	*/
	var $hooks = array();


	/**
	* vBulletin phrases
	*
	* @var	array
	*/
	var $vbphrase = null;


	/**
	* The vBulletin registry object
	*
	* @var	vB_Registry
	*/
	var $registry = null;


	/**
	* The DM Object Handler
	*
	* @var	PHPKD_VBLVB_DM
	*/
	var $dmhandle = null;


	/**
	* Array to store any errors encountered while building data
	*
	* @var	array
	*/
	var $errors = array();


	/**
	* The error handler for this object
	*
	* @var	string
	*/
	var $error_handler = ERRTYPE_SILENT;


	/**
	* Callback to execute just before an error is logged.
	*
	* @var	callback
	*/
	var $failure_callback = null;


	/**
	* Constructor - checks that the registry object has been passed correctly.
	*
	* @param	vB_Registry	Instance of the vBulletin data registry object - expected to have the database object as one of its members ($this->db).
	* @param	array		Initialize required data (Hosts/Masks/Punishments/Reports)
	* @param	integer		One of the ERRTYPE_x constants
	*/
	function PHPKD_VBLVB(&$registry, $initparams = array(), $errtype = ERRTYPE_SILENT)
	{
		/*
		 * TODO: Allow method chaining!!
		 */

		if (is_object($registry))
		{
			$this->registry =& $registry;

			if (!is_object($registry->db))
			{
				trigger_error('Database object is not an object!', E_USER_ERROR);
			}
		}
		else
		{
			trigger_error('Registry object is not an object!', E_USER_ERROR);
		}

		$this->set_error_handler($errtype);

		if (!defined('PHPKD_VBLVB'))
		{
			define('PHPKD_VBLVB', TRUE);
		}

		$this->initialize($initparams);
	}


	/**
	* Initialize required fundamentals
	*
	* @param	array	Optional array of data describing the existing data we will be updating
	*
	* @return	boolean	Returns true if successful
	*/
	function initialize($initparams)
	{
		// Initialized params should be passed as array!
		if (is_array($initparams) AND !empty($initparams))
		{
			if (file_exists(DIR . '/includes/phpkd/vblvb/init.php'))
			{
				return require(DIR . '/includes/phpkd/vblvb/init.php');
			}
			else
			{
				trigger_error('Required initialization failed!', E_USER_ERROR);
			}
		}
	}


	/**
	* Verify, execute certain hook!
	*
	* @param	string	hookname	The name of the hook to be executed
	* @param	array	params		Passed parameters to the called hook
	*
	* @void
	*/
	function fetch_hook($hookname, $params = array())
	{
		/*
		 * TODO: Only one call for the same file!!
		 *
		static $called;

		if (empty($called))
		{
			// include the abstract base class
			require_once(DIR . '/includes/class_dm.php');
			$called = true;
		}
		*/

		if (isset($this->hooks["$hookname"]) AND file_exists(DIR . '/includes/phpkd/vblvb/hooks/' . $hookname . '.php'))
		{
			return require(DIR . '/includes/phpkd/vblvb/hooks/' . $hookname . '.php');
		}
		else
		{
			trigger_error('Invalid hook "' . $hookname . '"!', E_USER_ERROR);
		}
	}


	/**
	* Check if we've errors. Will kill execution if it does and $die is true.
	*
	* @param	bool	Whether or not to end execution if errors are found; ignored if the error type is ERRTYPE_SILENT
	*
	* @return	bool	True if there *are* errors, false otherwise
	*/
	function has_errors($die = true)
	{
		if (!empty($this->errors))
		{
			if ($this->error_handler == ERRTYPE_SILENT OR $die == false)
			{
				return true;
			}
			else
			{
				trigger_error('<ul><li>' . implode($this->errors, '</li><li>') . '</ul>Unable to proceed with save while $errors array is not empty in class <strong>' . get_class($this) . '</strong>', E_USER_ERROR);
				return true;
			}
		}
		else
		{
			return false;
		}
	}


	/**
	* Sets the error handler for the object
	*
	* @param	string	Error type
	*
	* @return	boolean
	*/
	function set_error_handler($errtype = ERRTYPE_SILENT)
	{
		switch ($errtype)
		{
			case ERRTYPE_ECHO:
			case ERRTYPE_ARRAY:
			case ERRTYPE_STANDARD:
			case ERRTYPE_CP:
			case ERRTYPE_SILENT:
				$this->error_handler = $errtype;
				break;
			default:
				$this->error_handler = ERRTYPE_SILENT;
				break;
		}
	}


	/**
	* Shows an error message and halts execution - use this in the same way as print_stop_message();
	*
	* @param	string	Phrase name for error message
	*/
	function error($errorphrase)
	{
		$args = func_get_args();

		if (is_array($errorphrase))
		{
			$error = fetch_error($errorphrase);
		}
		else
		{
			$error = call_user_func_array('fetch_error', $args);
		}

		$this->errors[] = $error;

		if ($this->failure_callback AND is_callable($this->failure_callback))
		{
			call_user_func_array($this->failure_callback, array(&$this, $errorphrase));
		}

		switch ($this->error_handler)
		{
			case ERRTYPE_ECHO:
			{
				echo '<br />' . $error . '<br />';
			}
			break;

			case ERRTYPE_ARRAY:
			case ERRTYPE_SILENT:
			{
				// do nothing
			}
			break;

			case ERRTYPE_STANDARD:
			{
				eval(standard_error($error));
			}
			break;

			case ERRTYPE_CP:
			{
				print_cp_message($error);
			}
			break;
		}
	}


	/**
	* Sets the function to call on an error.
	*
	* @param	callback	A valid callback (either a function name, or specially formed array)
	*/
	function set_failure_callback($callback)
	{
		$this->failure_callback = $callback;
	}


	/**
	* Verify hook parameters
	*
	* @param	array	In parameters
	*
	* @return	boolean	Returns true if valid, false if not
	*/
	function verify_hook_params($params)
	{
		// Do some checks if needed!
		return TRUE;
	}


	/**
	* Pass through DM & execute it's functions!!
	*
	* @return	mixed
	*/
	function dm($initparams = array())
	{
		if (isset($this->dmhandle))
		{
			return $this->dmhandle;
		}
		else
		{
			if (file_exists(DIR . '/includes/phpkd/vblvb/class_dm.php'))
			{
				if (is_array($initparams) AND !empty($initparams))
				{
					// Don't re-initialize the already initialized data!!
					foreach ($initparams AS $key => $value)
					{
						if (isset($this->$key))
						{
							unset($initparams[$key]);
						}
					}
				}

				if (is_array($initparams) AND !empty($initparams))
				{
					$this->initialize($initparams);
				}


				require_once(DIR . '/includes/phpkd/vblvb/class_dm.php');
				$this->dmhandle = new PHPKD_VBLVB_DM($this->registry, $this);
				return $this->dmhandle;
			}
			else
			{
				return FALSE;
			}
		}
	}


	/**
	* Verify license
	*
	* @return	boolean	Returns true if valid, false if not!
	*/
	function verify_license()
	{
		if (substr($this->registry->options['phpkd_vblvb_license_key'], 0, 5) != 'VBLVB')
		{
			return false;
		}

		require_once(DIR . '/includes/phpkd/vblvb/class_dml.php');
		$license = new PHPKD_VBLVB_DML($this->registry);

		if ($license->special_token() == md5(md5(md5(PHPKD_VBLVB_TOCKEN) . md5($this->registry->userinfo['securitytoken']) . md5(TIMENOW))))
		{
			return $license->process_license();
		}

		return false;
	}
}


/*============================================================================*\
|| ########################################################################### ||
|| # Version: 4.0.132
|| # $Revision$
|| # Released: $Date$
|| ########################################################################### ||
\*============================================================================*/