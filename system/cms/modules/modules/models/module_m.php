<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Modules model
 *
 * @author 		PyroCMS Development Team
 * @package 	PyroCMS
 * @subpackage 	Modules
 * @category	Modules
 * @since 		v1.0
 */
class Module_m extends MY_Model
{
	protected $_table = 'modules';
	private $_module_exists = array();

	/**
	 * Constructor method
	 * @access public
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();

		$this->load->helper(array('date', 'modules/module'));
	}

	/**
	 * Get
	 *
	 * Return an array containing module data
	 *
	 * @access	public
	 * @param	string	$module		The name of the module to load
	 * @return	array
	 */
	public function get($slug = '')
	{
		// Have to return an associative array of NULL values for backwards compatibility.
		$null_array = array(
			'name' => NULL,
			'slug' => NULL,
			'version' => NULL,
			'description' => NULL,
			'skip_xss' => NULL,
			'is_frontend' => NULL,
			'is_backend' => NULL,
			'menu' => FALSE,
			'enabled' => 1,
			'sections' => array(),
			'shortcuts' => array(),
			'is_core' => NULL,
			'is_current' => NULL,
			'current_version' => NULL,
			'updated_on' => NULL
		);

		if (is_array($slug) || empty($slug))
		{
			return $null_array;
		}

		$row = $this->db
			->where('slug', $slug)
			->get($this->_table)
			->row();

		if ($row)
		{
			// Let's get REAL
			if ( ! $module = $this->_spawn_class($slug, $row->is_core))
			{
				return FALSE;
			}
			
			list($class, $location) = $module;
			$info = $class->info();
			
			// Return FALSE if the module is disabled
			if ($row->enabled == 0)
			{
				return FALSE;
			}

			$name = ! isset($info['name'][CURRENT_LANGUAGE]) ? $info['name']['en'] : $info['name'][CURRENT_LANGUAGE];
			$description = ! isset($info['description'][CURRENT_LANGUAGE]) ? $info['description']['en'] : $info['description'][CURRENT_LANGUAGE];

			return array(
				'name' => $name,
				'slug' => $row->slug,
				'version' => $row->version,
				'description' => $description,
				'skip_xss' => $row->skip_xss,
				'is_frontend' => $row->is_frontend,
				'is_backend' => $row->is_backend,
				'menu' => $row->menu,
				'enabled' => $row->enabled,
				'sections' => ! empty($info['sections']) ? $info['sections'] : array(),
				'shortcuts' => ! empty($info['shortcuts']) ? $info['shortcuts'] : array(),
				'is_core' => $row->is_core,
				'is_current' => version_compare($row->version, $this->version($row->slug),  '>='),
				'current_version' => $this->version($row->slug),
				'path' => $location,
				'updated_on' => $row->updated_on
			);
		}

		return $null_array;
	}
	
	/**
	 * Get Modules
	 *
	 * Return an array of objects containing module related data
	 *
	 * @param   array   $params             The array containing the modules to load
	 * @param   bool    $return_disabled    Whether to return disabled modules
	 * @access  public
	 * @return  array
	 */
	public function get_all($params = array(), $return_disabled = FALSE)
	{
		$modules = array();

		// We have some parameters for the list of modules we want
		if ($params)
		{
			foreach ($params as $field => $value)
			{
				if (in_array($field, array('is_frontend', 'is_backend', 'menu', 'is_core')))
				{
					$this->db->where($field, $value);
				}
			}
		}

		// Skip the disabled modules
		if ($return_disabled === FALSE)
		{
			$this->db->where('enabled', 1);
		}

		$result = $this->db->get($this->_table)->result();

		foreach ($result as $row)
		{
			// Let's get REAL
			if ( ! $module = $this->_spawn_class($row->slug, $row->is_core))
			{
				// If module is not able to spawn a class, 
				// just forget about it and move on, man.
				continue;
			}
			
			list($class, $location) = $module;
			$info = $class->info();
			
			$name = ! isset($info['name'][CURRENT_LANGUAGE]) ? $info['name']['en'] : $info['name'][CURRENT_LANGUAGE];
			$description = ! isset($info['description'][CURRENT_LANGUAGE]) ? $info['description']['en'] : $info['description'][CURRENT_LANGUAGE];

			$module = array(
				'name'				=> $name,
				'slug'				=> $row->slug,
				'version'			=> $row->version,
				'description'		=> $description,
				'skip_xss'			=> $row->skip_xss,
				'is_frontend'		=> $row->is_frontend,
				'is_backend'		=> $row->is_backend,
				'menu'				=> $row->menu,
				'enabled'			=> $row->enabled,
				'installed'			=> $row->installed,
				'is_core'			=> $row->is_core,
				'is_current'		=> version_compare($row->version, $this->version($row->slug),  '>='),
				'current_version'	=> $this->version($row->slug),
				'path' 				=> $location,
				'updated_on'		=> $row->updated_on
			);
			
			if ( ! empty($params['is_backend']))
			{
				// This user has no permissions for this module
				if ( $this->current_user->group !== 'admin' AND empty($this->permissions[$row->slug]) )
				{
					continue;
				}
			}

			$modules[$module['name']] = $module;
		}
		
		ksort($modules);

		return array_values($modules);
	}

	/**
	 * Add
	 *
	 * Adds a module to the database
	 *
	 * @access	public
	 * @param	array	$module		Information about the module
	 * @return	object
	 */
	public function add($module)
	{
		return $this->db->replace($this->_table, array(
			'name'			=> serialize($module['name']),
			'slug'			=> $module['slug'],
			'version'		=> $module['version'],
			'description'	=> serialize($module['description']),
			'skip_xss'		=> ! empty($module['skip_xss']),
			'is_frontend'	=> ! empty($module['frontend']),
			'is_backend'	=> ! empty($module['backend']),
			'menu'			=> ! empty($module['menu']) ? $module['menu'] : FALSE,
			'enabled'		=> ! empty($module['enabled']),
			'installed'		=> ! empty($module['installed']),
			'is_core'		=> ! empty($module['is_core']),
			'updated_on'	=> now()
		));
	}

	/**
	 * Update
	 *
	 * Updates a module in the database
	 *
	 * @access  public
	 * @param   array   $slug   Module slug to update
	 * @param   array   $module Information about the module
	 * @return  object
	 */
	public function update($slug, $module)
	{
		$module['updated_on'] = now();

		return $this->db->where('slug', $slug)->update($this->_table, $module);
	}

	/**
	 * Delete
	 *
	 * Delete a module from the database
	 *
	 * @param	array	$slug	The module slug
	 * @access	public
	 * @return	object
	 */
	public function delete($slug)
	{
		return $this->db->delete($this->_table, array('slug' => $slug));
	}

	/**
	 * Exists
	 *
	 * Checks if a module exists
	 *
	 * @param	string	$module	The module slug
	 * @return	bool
	 */
	public function exists($module)
	{
		$this->_module_exists = array();

		if ( ! $module)
		{
			return FALSE;
		}

		// We already know about this module
		if (isset($this->_module_exists[$module]))
		{
			return $this->_module_exists[$module];
		}

		return $this->_module_exists[$module] = $this->db
			->where('slug', $module)
			->count_all_results($this->_table) > 0;
	}

	/**
	 * Enable
	 *
	 * Enables a module
	 *
	 * @param	string	$module	The module slug
	 * @return	bool
	 */
	public function enable($module)
	{
		if ($this->exists($module))
		{
			$this->db->where('slug', $module)->update($this->_table, array('enabled' => 1));
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * Disable
	 *
	 * Disables a module
	 *
	 * @param	string	$slug	The module slug
	 * @return	bool
	 */
	public function disable($slug)
	{
		if ($this->exists($slug))
		{
			$this->db->where('slug', $slug)->update($this->_table, array('enabled' => 0));
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * Install
	 *
	 * Installs a module
	 *
	 * @param	string	$slug	The module slug
	 * @return	bool
	 */
	public function install($slug, $is_core = FALSE, $insert = FALSE)
	{
		if ( ! $module = $this->_spawn_class($slug, $is_core))
		{
			return FALSE;
		}
		
		list($class) = $module;
		
		// They've just finished uploading it so we need to make a record
		if ($insert)
		{
			// Get some info for the db
			$input = $class->info();
	
			// Now lets set some details ourselves
			$input['slug']			= $slug;
			$input['version']		= $class->version;
			$input['enabled']		= $is_core; // enable if core
			$input['installed']	= $is_core; // install if core
			$input['is_core']		= $is_core; // is core if core
	
			// It's a valid module let's make a record of it
			$this->add($input);
		}

		// TURN ME ON BABY!
		$this->db->where('slug', $slug)->update('modules', array('enabled' => 1, 'installed' => 1));
		
		// set the site_ref and upload_path for third-party devs
		$class->site_ref 	= SITE_REF;
		$class->upload_path	= 'uploads/'.SITE_REF.'/';

		// Run the install method to get it into the database
		return $class->install();
	}

	/**
	 * Uninstall
	 *
	 * Unnstalls a module
	 *
	 * @param	string	$module	The module slug
	 * @return	bool
	 */
	public function uninstall($slug, $is_core = FALSE)
	{
		if ( ! $module = $this->_spawn_class($slug, $is_core))
		{
			// the files are missing so let's clean the "modules" table
			return $this->delete($slug);
		}
		
		list($class) = $module;
			
		// set the site_ref and upload_path for third-party devs
		$class->site_ref 	= SITE_REF;
		$class->upload_path	= 'uploads/'.SITE_REF.'/';

		// Run the uninstall method to drop the module's tables
		if ( ! $class->uninstall())
		{
			return FALSE;
		}

		if ($this->delete($slug))
		{
			// Get some info for the db
			$input = $class->info();
	
			// Now lets set some details ourselves
			$input['slug']			= $slug;
			$input['version']		= $class->version;
			$input['enabled']		= $is_core; // enable if core
			$input['installed']		= $is_core; // install if core
			$input['is_core']		= $is_core; // is core if core
	
			// We record it again here. If they really want to get rid of it they'll use Delete
			return $this->add($input);
		}
		return FALSE;
	}
	
	/**
	 * Upgrade
	 *
	 * Upgrade a module
	 *
	 * @param	string	$module	The module slug
	 * @return	bool
	 */
	public function upgrade($slug)
	{
		// Get info on the new module
		if ( ! ($module = $this->_spawn_class($slug, TRUE) OR $module = $this->_spawn_class($slug, FALSE)))
		{
			return FALSE;
		}
		
		// Get info on the old module
		if ( ! $old_module = $this->get($slug))
		{
			return FALSE;
		}
		
		list($class) = $module;
		
		// Get the old module version number
		$old_version = $old_module['version'];
		
		// set the site_ref and upload_path for third-party devs
		$class->site_ref 	= SITE_REF;
		$class->upload_path	= 'uploads/'.SITE_REF.'/';
		
		// Run the update method to get it into the database
		if ($class->upgrade($old_version))
		{
			// Update version number
			$this->db->where('slug', $slug)->update('modules', array('version' => $class->version));
			
			return TRUE;
		}
		
		// The upgrade failed
		return FALSE;
	}
	
	public function import_unknown()
    {
    	$modules = array();

		$is_core = TRUE;

		$known = $this->get_all(array(), TRUE);
		$known_array = array();
		$known_mtime = array();

		// Loop through the known array and assign it to a single dimension because
		// in_array can not search a multi array.
		if (is_array($known) && count($known) > 0)
		{
			foreach ($known as $item)
			{
				array_unshift($known_array, $item['slug']);
				$known_mtime[$item['slug']] = $item;
			}
		}

		foreach (array(APPPATH, ADDONPATH, SHARED_ADDONPATH) as $directory)
    	{
			// some servers return false instead of an empty array
			if ( ! $directory) continue;

			foreach (glob($directory.'modules/*', GLOB_ONLYDIR) as $path)
			{
				$slug = basename($path);

				// Yeah yeah we know
				if (in_array($slug, $known_array))
				{
					$details_file = $directory . 'modules/' . $slug . '/details'.EXT;

					if (file_exists($details_file) &&
						filemtime($details_file) > $known_mtime[$slug]['updated_on'] &&
						$module = $this->_spawn_class($slug, $is_core))
					{
						list($class) = $module;
						
						// Get some basic info
						$input = $class->info();

						$this->update($slug, array(
							'name'			=> serialize($input['name']),
							'description'	=> serialize($input['description']),
							'is_frontend'	=> ! empty($input['frontend']),
							'is_backend'	=> ! empty($input['backend']),
							'skip_xss'		=> ! empty($input['skip_xss']),
							'menu'			=> ! empty($input['menu']) ? $input['menu'] : FALSE,
							'updated_on'	=> now()
						));

						log_message('debug', sprintf('The information of the module "%s" has been updated', $slug));
					}

					continue;
				}

				// This doesnt have a valid details.php file! :o
				if ( ! $module = $this->_spawn_class($slug, $is_core))
				{
					continue;
				}
				
				list ($class) = $module;

				// Get some basic info
				$input = $class->info();

				// Now lets set some details ourselves
				$input['slug']			= $slug;
				$input['version']		= $class->version;
				$input['enabled']		= $is_core; // enable if core
				$input['installed']		= $is_core; // install if core
				$input['is_core']		= $is_core; // is core if core

				// Looks like it installed ok, add a record
				$this->add($input);
			}

			// Going back around, 2nd time is addons
			$is_core = FALSE;
		}

		return TRUE;
	}


	/**
	 * Spawn Class
	 *
	 * Checks to see if a details.php exists and returns a class
	 *
	 * @param	string	$slug	The folder name of the module
	 * @access	private
	 * @return	array
	 */
	private function _spawn_class($slug, $is_core = FALSE)
	{
		$path = $is_core ? APPPATH : ADDONPATH;

		// Before we can install anything we need to know some details about the module
		$details_file = $path . 'modules/' . $slug . '/details'.EXT;

		// Check the details file exists
		if ( ! is_file($details_file))
		{
			$details_file = SHARED_ADDONPATH . 'modules/' . $slug . '/details'.EXT;
			
			if ( ! is_file($details_file))
			{
				return FALSE;
			}
		}

		// Sweet, include the file
		include_once $details_file;

		// Now call the details class
		$class = 'Module_'.ucfirst(strtolower($slug));

		// Now we need to talk to it
		return class_exists($class) ? array(new $class, dirname($details_file)) : FALSE;
	}

	/**
	 * Help
	 *
	 * Retrieves help string from details.php
	 *
	 * @param	string	$slug	The module slug
	 * @return	bool
	 */
	public function help($slug)
	{
		foreach (array(0, 1) as $is_core)
    	{
			$languages = $this->config->item('supported_languages');
			$default = $languages[$this->config->item('default_language')]['folder'];
		
			//first try it as a core module
			if ($module = $this->_spawn_class($slug, $is_core))
			{
				list ($class, $location) = $module;
			
				// Check for a hep language file, if not show the default help text from the details.php file
				if (file_exists($location.'/language/'.$default.'/help_lang.php'))
				{
					$this->lang->load($slug.'/help');

					if (lang('help_body'))
					{
						return lang('help_body');
					}
				}
				else
				{
					return $class->help();
				}
			}
		}

		return FALSE;
	}

	/**
	 * Roles
	 *
	 * Retrieves roles for a specific module
	 *
	 * @param	string	$slug	The module slug
	 * @return	bool
	 */
	public function roles($slug)
	{
		foreach (array(0, 1) as $is_core)
    	{
			//first try it as a core module
			if ($module = $this->_spawn_class($slug, $is_core))
			{
				list($class) = $module;
				$info = $class->info();

				if ( ! empty($info['roles']))
				{
					$this->lang->load($slug . '/permission');
					return $info['roles'];
				}
			}
		}

		return array();
	}
	
	/**
	 * Help
	 *
	 * Retrieves version number from details.php
	 *
	 * @param   string  $slug   The module slug
	 * @return  bool
	 */
	public function version($slug)
	{
		if ($module = $this->_spawn_class($slug, TRUE) OR $module = $this->_spawn_class($slug))
		{
			list($class) = $module;
			return $class->version;
		}

		return FALSE;
	}
}