<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * PyroStreams User Field Type
 *
 * @package		PyroStreams
 * @author		Parse19
 * @copyright	Copyright (c) 2011 - 2012, Parse19
 * @license		http://parse19.com/pyrostreams/docs/license
 * @link		http://parse19.com/pyrostreams
 */
class Field_user
{
	public $field_type_slug			= 'user';
	
	public $db_col_type				= 'int';

	public $custom_parameters		= array('restrict_group');

	public $version					= '1.0';

	public $author					= array('name'=>'Parse19', 'url'=>'http://parse19.com');

	// --------------------------------------------------------------------------

	// Run-time cache of users.
	private $cache 					= array();
	
	// --------------------------------------------------------------------------

	/**
	 * Output form input
	 *
	 * @param	array
	 * @param	array
	 * @return	string
	 */
	public function form_output($params, $entry_id, $field)
	{
		$this->CI->db->select('username, id');
	
		if( is_numeric($params['custom']['restrict_group']) ):
		
			$this->CI->db->where('group_id', $params['custom']['restrict_group']);
		
		endif;
		
		$this->CI->db->order_by('username', 'asc');
		$db_obj = $this->CI->db->get('users');
		
		$users_raw = $db_obj->result();
		
		$users = array();

		// If this is not required, then
		// let's allow a null option
		if($field->is_required == 'no'):
		
			$users[null] = $this->CI->config->item('dropdown_choose_null');
		
		endif;
		
		// Get user choices
		foreach( $users_raw as $user ):
		
			$users[$user->id] = $user->username;
		
		endforeach;
	
		return form_dropdown($params['form_slug'], $users, $params['value'], 'id="'.$params['form_slug'].'"');
	}

	// --------------------------------------------------------------------------

	/**
	 * Restrict to Group
	 */
	public function param_restrict_group($value = '')
	{
		$this->CI->db->order_by('name', 'asc');
		
		$db_obj = $this->CI->db->get('groups');
		
		$groups = array('no' => lang('streams.user.dont_restrict_groups'));
		
		$groups_raw = $db_obj->result();
		
		foreach( $groups_raw as $group ):
		
			$groups[$group->id] = $group->name;
		
		endforeach;
	
		return form_dropdown('restrict_group', $groups, $value);
	}

	// --------------------------------------------------------------------------

	/**
	 * Process before outputting for the plugin
	 *
	 * This creates an array of data to be merged with the
	 * tag array so relationship data can be called with
	 * a {field.column} syntax
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @param	array
	 * @return	array
	 */
	public function pre_output_plugin($input, $params)
	{
		// Can't do anything without an input
		if(!is_numeric($input)) return;
	
		// Check run-time cache
		if(isset($this->cache[$input])) return $this->cache[$input];
	
		$this->CI->load->model('users/user_m');
		
		$user = $this->CI->user_m->get( array('id' => $input) );

		$return = array(
			'user_id'			=> $user->user_id,
			'display_name'		=> $user->display_name,
			'first_name'		=> $user->first_name,
			'last_name'			=> $user->last_name,
			'company'			=> $user->company,
			'lang'				=> $user->lang,
			'email'				=> $user->email,
			'username'			=> $user->username,
		);
		
		$this->cache[$input] = $return;
		
		return $return;
	}
}
/* End of file field.user.php */