<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * PyroStreams Streams Model
 *
 * @package		PyroStreams
 * @author		Parse19
 * @copyright	Copyright (c) 2011 - 2012, Parse19
 * @license		http://parse19.com/pyrostreams/docs/license
 * @link		http://parse19.com/pyrostreams
 */
class Streams_m extends MY_Model {

	public $table;

    // --------------------------------------------------------------------------
    // Caches
    // This is data stored in the class at runtime
    // and saved/checked so we don't keep going back to
    // the database.
    // --------------------------------------------------------------------------
	
	/**
	 * Stream fields cache.
	 * Stored by id ie array('id'=>data)
	 */
	public $stream_fields_cache = array();
	
	/**
	 * Streams cache
	 * Stored by slug
	 */
	public $streams_cache = array();

    // --------------------------------------------------------------------------

	/**
	 * Streams Validation
	 */
	public $streams_validation = array(
		array(
			'field'	=> 'stream_name',
			'label' => 'Steam Name',
			'rules'	=> 'trim|required|max_length[60]'
		),
		array(
			'field'	=> 'stream_slug',
			'label' => 'Steam Slug',
			'rules'	=> 'trim|required|max_length[60]|slug_safe'
		),
		array(
			'field'	=> 'about',
			'label' => 'About This Stream',
			'rules'	=> 'trim|max_length[255]'
		)
	);
	
    // --------------------------------------------------------------------------

	function __construct()
	{
		$this->table = STREAMS_TABLE;
		
		// We just grab all the streams now.
		// That way we don't have to do a separate DB
		// call for each.
		$obj = $this->db->get($this->table);
		
		foreach($obj->result() as $stream):
		
			if( trim($stream->view_options) == '' ):
			
				$stream->view_options = array();
			
			else:
	
				$stream->view_options = unserialize($stream->view_options);
				
				// Just in case we get bad data
				if(!is_array($stream->view_options)) $stream->view_options = array();
			
			endif;

			$this->streams_cache[$stream->id] = $stream;	
		
		endforeach; 
	}
    
    // --------------------------------------------------------------------------
    
    /**
     * Get streams
     *
     * @access	public
     * @param	int limit
     * @param	int offset
     * @return	obj
     */
    public function get_streams( $limit = 25, $offset = 0 )
	{
		$this->db->order_by('stream_name', 'ASC');
		
		$obj = $this->db->get($this->table, $limit, $offset);

		if( $obj->num_rows() == 0 ):
		
			return FALSE;
		
		else:
		
			return $obj->result();

		endif;
	}

    // --------------------------------------------------------------------------
    
    /**
     * Count total streams
     *
     * @access	public
     * @return	int
     */
	public function total_streams()
	{
    	return $this->db->count_all($this->table);
	}

    // --------------------------------------------------------------------------

	/**
	 * Count entries in a stream
	 *
	 * @access	public
	 * @param	string
	 * @return	int
	 */
	public function count_stream_entries($stream_slug)
	{
		return $this->db->count_all(STR_PRE.$stream_slug);
	}

    // --------------------------------------------------------------------------

	/**
	 * Create a new stream
	 *
	 * @access	public
	 * @param	string - name of the stream
	 * @param	string - stream slug
	 * @param	[string - about the stream]
	 * @return	bool
	 */
	public function create_new_stream($stream_name, $stream_slug, $about = null)
	{		
		// See if table exists. You never know if it sneaked past validation
		if($this->db->table_exists(STR_PRE.$stream_slug)):
		
			return FALSE;
		
		endif;
	
		// Create the db table
		$this->load->dbforge();
		
		$this->dbforge->add_field('id');
		
		// Add in our standard fields		
		$standard_fields = array(
	        'created' 			=> array('type' => 'DATETIME'),
            'updated'	 		=> array('type' => 'DATETIME', 'null' => true),
            'created_by'		=> array('type' => 'INT', 'constraint' => '11', 'null' => true ),
            'ordering_count'	=> array('type' => 'INT', 'constraint' => '11' )
		);
		
		$this->dbforge->add_field($standard_fields);
		
		if( !$this->dbforge->create_table(STR_PRE.$stream_slug) ):
		
			return FALSE;
		
		endif;
		
		// Add data into the streams table
		$insert_data['stream_slug']			= $stream_slug;
		$insert_data['stream_name']			= $stream_name;
		$insert_data['about']				= $about;
		$insert_data['title_column']		= null;
		
		// Since this is a new stream, we are going to add a basic view profile
		// with data we know will be there.	
		$insert_data['view_options']		= serialize(array('id', 'created'));
		
		return $this->db->insert($this->table, $insert_data);
	}

	// --------------------------------------------------------------------------

	/**
	 * Update Stream
	 *
	 * @access	public
	 * @param	int
	 * @param	array - update_data
	 * @return	bool
	 */
	public function update_stream($stream_id, $data)
	{
		// See if the stream slug is different
		$stream = $this->get_stream($stream_id);
		
		if( $stream->stream_slug != $data['stream_slug'] ):
		
			// Okay looks like we need to alter the table name.			
			// Check to see if there is a table, then alter it.
			if( $this->db->table_exists(STR_PRE.$data['stream_slug']) ):
			
				show_error(sprintf(lang('streams.table_exists'), $data['stream_slug']));
			
			endif;
			
			$this->load->dbforge();
			
			// Using the PyroStreams DB prefix because rename_table
			// does not prefix the table name properly, it would seem
			if( !$this->dbforge->rename_table(STR_PRE.$stream->stream_slug, STR_PRE.$data['stream_slug']) ):
			
				return FALSE;
			
			endif;
		
			$update_data['stream_slug']	= $data['stream_slug'];
		
		endif;
		
		$update_data['stream_name']		= $data['stream_name']; 
		$update_data['about']			= $data['about'];
		$update_data['sorting']			= $data['sorting'];
		
		// We won't always have a title column. If we don't have
		// any fields yet, for instance, it will not exist.
		if( isset($data['title_column']) ):
		
			$update_data['title_column'] = $data['title_column'];
		
		endif;
		
		$this->db->where('id', $stream_id);
		return $this->db->update($this->table, $update_data);
	}

	// --------------------------------------------------------------------------
	
	/**
	 * Delete a stream
	 *
	 * @access	public
	 * @param	obj
	 * @return	bool
	 */
	public function delete_stream($stream)
	{
		// -------------------------------------
		// Get assignments and run destructs
		// -------------------------------------

		$assignments = $this->fields_m->get_assignments_for_stream($stream->id);
		
		if(is_array($assignments)):

			foreach($assignments as $assignment):
		
				// Run the destruct
				if(method_exists($this->type->types->{$assignment->field_type}, 'field_assignment_destruct')):
				
					$this->type->types->{$assignment->field_type}->field_assignment_destruct($this->fields_m->get_field($assignment->field_id), $this->streams_m->get_stream($assignment->stream_slug, true));
			
				endif;		
			
			endforeach;
		
		endif;

		// -------------------------------------
		// Delete actual table
		// -------------------------------------
		
		$this->load->dbforge();
		
		if( !$this->dbforge->drop_table(STR_PRE.$stream->stream_slug) ):
		
			return FALSE;
		
		endif;

		// -------------------------------------
		// Delete from assignments
		// -------------------------------------
		
		$this->db->where('stream_id', $stream->id);
		
		if( !$this->db->delete(ASSIGN_TABLE) ):
		
			return FALSE;
		
		endif;

		// -------------------------------------
		// Delete from streams table
		// -------------------------------------
		
		return $this->db->where('id', $stream->id)->delete($this->table);
	}

	// --------------------------------------------------------------------------

	/**
	 * Get the ID for a stream from the slug
	 *
	 * @access	public
	 * @param	string
	 * @return	mixed
	 */	
	public function get_stream_id_from_slug($slug)
	{
		$db = $this->db->limit(1)->where('stream_slug', $slug)->get($this->table);
		
		if( $db->num_rows() == 0 ):
		
			return FALSE;
		
		else:
		
			$row = $db->row();
			
			return $row->id;
		
		endif;
	}

	// --------------------------------------------------------------------------
	
	/**
	 * Get a data for a single stream
	 *
	 * @access	public
	 * @param	int
	 * @param	bool
	 * @return	mixed
	 */
	public function get_stream($stream_id, $by_slug = FALSE)
	{
		// Check for cache. We only cache by slug
		if( !$by_slug and is_numeric($stream_id) ):
		
			if(isset($this->streams_cache[$stream_id])):
			
				return $this->streams_cache[$stream_id];
			
			endif;
			
		endif;
	
		$this->db->limit(1);
		
		if( $by_slug == TRUE ):

			$this->db->where('stream_slug', $stream_id);		
		
		else:
		
			$this->db->where('id', $stream_id);
		
		endif;

		$obj = $this->db->get($this->table);
		
		if( $obj->num_rows() == 0 ):
		
			return FALSE;
		
		endif;
		
		$stream = $obj->row();
		
		if( trim($stream->view_options) == '' ):
		
			$stream->view_options = array();
		
		else:

			$stream->view_options = unserialize($stream->view_options);
		
		endif;
		
		// Save to cache
		$this->streams_cache[$stream_id] = $stream;
		
		return $stream;
	}

	// --------------------------------------------------------------------------
	
	/**
	 * Get data from a stream.
	 *
	 * Only really shown on the back end.
	 *
	 * @access	public
	 * @param	obj
	 * @param	obj
	 * @param	int
	 * @param	int
	 * @return 	obj
	 */
	public function get_stream_data($stream, $stream_fields, $limit, $offset = 0)
	{
		$this->load->config('streams');

		// -------------------------------------
		// Set Ordering
		// -------------------------------------

		if($stream->sorting == 'title' and ($stream->title_column != '' and $this->db->field_exists($stream->title_column, $this->config->item('stream_prefix').$stream->stream_slug))):
								
			if($stream->title_column != '' and $this->db->field_exists($stream->title_column, STR_PRE.$stream->stream_slug)):
			
				$this->db->order_by($stream->title_column, 'ASC');
			
			endif;
		
		elseif($stream->sorting == 'custom'):
		
			$this->db->order_by('ordering_count', 'ASC');
			
		else:
		
			$this->db->order_by('created', 'DESC');
		
		endif;

		// -------------------------------------
		// Get Data
		// -------------------------------------
	
		$this->db->limit($limit, $offset);
		$obj = $this->db->get(STR_PRE.$stream->stream_slug);		
		$items = $obj->result();
		
		// -------------------------------------
		// Get Format Profile
		// -------------------------------------

		$stream_fields = $this->streams_m->get_stream_fields($stream->id);

		// -------------------------------------
		// Run formatting
		// -------------------------------------
		
		if( count($items) != 0 ):
		
			$fields = new stdClass;
	
			foreach( $items as $id => $item ):
			
				$fields->$id = $this->row_m->format_row($item, $stream_fields, $stream);
			
			endforeach;
		
		else:
		
			$fields = FALSE;

		endif;
		
		return $fields;
	}

	// --------------------------------------------------------------------------
	
	/**
	 * Get the assigned fields for a stream
	 *
	 * @access	public
	 * @param	int
	 * @param	int
	 * @param	int
	 * @return	mixed
	 */
	public function get_stream_fields($stream_id, $limit = FALSE, $offset = FALSE)
	{	
		// Check and see if there is a cache
		if(isset($this->stream_fields_cache[$stream_id]) and !$limit and !$offset):
		
			return $this->stream_fields_cache[$stream_id];
		
		endif;
	
		if( !is_numeric($stream_id) ):
		
			return FALSE;
		
		endif;
	
		$this->db->select(ASSIGN_TABLE.'.id as assign_id, '.STREAMS_TABLE.'.*, '.ASSIGN_TABLE.'.*, '.FIELDS_TABLE.'.*');
		$this->db->order_by(ASSIGN_TABLE.'.sort_order', 'asc');
		
		if(is_numeric($limit)):
		
			if(is_numeric($offset)):
			
				$this->db->limit($limit, $offset);
				
			else:
			
				$this->db->limit($limit);
			
			endif;
		
		endif;
		
		$this->db->where(STREAMS_TABLE.'.id', $stream_id);
		$this->db->join(ASSIGN_TABLE, STREAMS_TABLE.'.id='.ASSIGN_TABLE.'.stream_id');
		$this->db->join(FIELDS_TABLE, FIELDS_TABLE.'.id='.ASSIGN_TABLE.'.field_id');
		
		$obj = $this->db->get(STREAMS_TABLE);
		
		if( $obj->num_rows() == 0 ):
		
			return FALSE;
		
		else:
		
			$streams = new stdClass;
		
			$raw = $obj->result();
			
			foreach( $raw as $item ):
			
				$node = $item->field_slug;
			
				$streams->$node = $item;
				
				$streams->$node->field_data = unserialize($item->field_data);
			
			endforeach;
			
			// Save for cache
			if( !$limit and !$offset ) $this->stream_fields_cache[$stream_id] = $streams;
			
			return $streams;
		
		endif;
	}

	// --------------------------------------------------------------------------
	
	/**
	 * Get total stream fields
	 *
	 * @access	public
	 * @param	int
	 * @return	int
	 */
	public function total_stream_fields($stream_id)
	{
		$query = $this->db->where('stream_id', $stream_id)->get(ASSIGN_TABLE);
		
		return $query->num_rows();
	}

	// --------------------------------------------------------------------------
	
	/**
	 * Add Field to Stream
	 *
	 * Runs all the processess necessary to add a field to a stream including
	 * Creating the necessary column in the database
	 *
	 * @access  public
	 * @param	int
	 * @param	int
	 * @param	array - data
	 * @return	bool
	 */
	public function add_field_to_stream($field_id, $stream_id, $data)
	{
		// -------------------------------------
		// Get the field data
		// -------------------------------------
		
		$field = $this->fields_m->get_field($field_id);
		
		if( !$field ) return FALSE;
		
		// -------------------------------------
		// Get stream info
		// -------------------------------------
		
		$stream = $this->get_stream($stream_id);
		
		if( !$stream ) return FALSE;

		// -------------------------------------
		// Load the field type
		// -------------------------------------
		
		$field_type = $this->type->types->{$field->field_type};
		
		if( !$field_type ) return FALSE;
		
		// Do we have a pre-add function?
		if(method_exists($field_type, 'field_assignment_construct')):
		
			$field_type->field_assignment_construct($field, $stream);
		
		endif;
		
		// -------------------------------------
		// Create database column
		// -------------------------------------
		
		$this->load->dbforge();
		
		$field_data = array();
		
		$field_data['field_slug']				= $field->field_slug;
		
		if( isset( $field->field_data['max_length'] ) ):
			
			$field_data['max_length']			= $field->field_data['max_length'];
		
		endif;

		if( isset( $field->field_data['default_value'] ) ):
			
			$field_data['default_value']		= $field->field_data['default_value'];
		
		endif;
		
		$field_to_add[$field->field_slug] 	= $this->fields_m->field_data_to_col_data( $field_type, $field_data );
		
		if($field_type->db_col_type !== FALSE):
		
			if(!isset($field_type->alt_process) or !$field_type->alt_process):
		
				if( ! $this->dbforge->add_column(STR_PRE.$stream->stream_slug, $field_to_add) ) return FALSE;
			
			endif;
		
		endif;
		
		// -------------------------------------
		// Check for title column
		// -------------------------------------
		// See if this should be made the title column
		// -------------------------------------

		if( isset($data['title_column']) and $data['title_column'] == 'yes' ):
		
			$update_data['title_column'] = $field->field_slug;
		
			$this->db->where('id', $stream->id );
			$this->db->update(STREAMS_TABLE, $update_data);
		
		endif;
		
		// -------------------------------------
		// Create record in assignments
		// -------------------------------------
		
		$insert_data['stream_id'] 		= $stream_id;
		$insert_data['field_id']		= $field_id;
		
		if(isset($data['instructions'])):
		
			$insert_data['instructions']	= $data['instructions'];
		
		else:
		
			$insert_data['instructions']	= NULL;
		
		endif;
		
		// +1 for ordering.
		$this->db->select('MAX(sort_order) as top_num')->where('stream_id', $stream->id);
		$query = $this->db->get(ASSIGN_TABLE);
		
		if($query->num_rows() == 0):
		
			// First one! Make it 1
			$insert_data['sort_order'] = 1;
		
		else:
			
			$row = $query->row();
			$insert_data['sort_order'] = $row->top_num+1;
		
		endif;
		
		// Is Required
		if( isset($data['is_required']) and $data['is_required'] == 'yes' ):
		
			$insert_data['is_required']		= 'yes';

		endif;
		
		// Unique		
		if( isset($data['is_unique']) and  $data['is_unique'] == 'yes' ):
		
			$insert_data['is_unique']		= 'yes';

		endif;
		
		if( !$this->db->insert(ASSIGN_TABLE, $insert_data) ):
		
			return FALSE;
		
		endif;
		
		return TRUE;
	}

	// --------------------------------------------------------------------------
	
	/**
	 * Remove a field
	 *
	 * @param	obj
	 * @param	obj
	 * @param	obj
	 * @return	bool
	 */
	public function remove_field_assignment($assignment, $field, $stream)
	{
		$this->load->dbforge();

		// Do we have a destruct function
		if(method_exists($this->type->types->{$field->field_type}, 'field_assignment_destruct')):
		
			$this->type->types->{$field->field_type}->field_assignment_destruct($field, $stream, $assignment);
		
		endif;

		// -------------------------------------
		// Remove from db structure
		// -------------------------------------
		
		// Alternate method fields will not have a column, so we just
		// check for it first
		if($this->db->field_exists($field->field_slug, STR_PRE.$stream->stream_slug)):
			
			if( !$this->dbforge->drop_column(STR_PRE.$stream->stream_slug, $field->field_slug) ):
		
				return FALSE;
		
			endif;
		
		endif;

		// -------------------------------------
		// Remove from field assignments table
		// -------------------------------------
	
		$this->db->where('id', $assignment->id);
		
		if( !$this->db->delete(ASSIGN_TABLE) ) return FALSE;

		// -------------------------------------
		// Reset the ordering
		// -------------------------------------

		// Find everything above it, and take each one
		// down a peg.
		if($assignment->sort_order == '' or !is_numeric($assignment->sort_order)):
		
			$assignment->sort_order = 0;
		
		endif;
		
		$this->db->where('sort_order >', $assignment->sort_order)->select('id, sort_order');
		$ord_obj = $this->db->get(ASSIGN_TABLE);
		
		if($ord_obj->num_rows() > 0):
		
			$rows = $ord_obj->result();
			
			foreach( $rows as $update_row ):

				$update_data['sort_order'] = $update_row->sort_order-1;
				
				$this->db->where('id', $update_row->id)->update(ASSIGN_TABLE, $update_data);
				
				$update_data = array();
			
			endforeach;
		
		endif;

		// -------------------------------------
		// Remove from from field options
		// -------------------------------------
		
		if( is_array($stream->view_options) and in_array($field->field_slug, $stream->view_options) ):
		
			$options = $stream->view_options;
			
			foreach( $options as $key => $val ):
			
				if( $val == $field->field_slug ):
				
					unset($options[$key]);
				
				endif;
			
			endforeach;
			
			$update_data['view_options'] = serialize($options);
			
			$this->db->where('id', $stream->id);
		
			if( ! $this->db->update($this->table, $update_data) ):
			
				return FALSE;
			
			endif;
		
		endif;
		
		// -------------------------------------
		
		return TRUE;
	}
	
	// --------------------------------------------------------------------------	
	
	/**
	 * Delete a row
	 *
	 * @access	public
	 * @param	int
	 * @param	obj
	 * @return 	bool
	 */
	public function delete_row($row_id, $stream)
	{
		// Get the row
		$db_obj = $this->db->limit(1)->where('id', $row_id)->get(STR_PRE.$stream->stream_slug);
		
		if( $db_obj->num_rows() == 0 ) return false;
		
		// Get the ordering count
		$row = $db_obj->row();
		$ordering_count = $row->ordering_count;
		
		// Delete the actual row
		$this->db->where('id', $row_id);
		
		if( !$this->db->delete(STR_PRE.$stream->stream_slug) ):
		
			return FALSE;
		
		else:
		
			// -------------------------------------
			// Entry Destructs
			// -------------------------------------
			// Go through the assignments and call
			// entry destruct methods
			// -------------------------------------
		
			// Get the assignments
			$assignments = $this->fields_m->get_assignments_for_stream($stream->id);
			
			// Do they have a destruct function?
			foreach($assignments as $assign):
			
				if(method_exists($this->type->types->{$assign->field_type}, 'entry_destruct')):
				
					// Get the field
					$field = $this->fields_m->get_field($assign->field_id);
				
					$this->type->types->{$assign->field_type}->entry_destruct($row, $field, $stream);
				
				endif;
			
			endforeach;
		
			// -------------------------------------
			// Reset reordering
			// -------------------------------------
			// We're doing this by subtracting one to
			// everthing higher than the row's
			// order count
			// -------------------------------------
			
			$this->db->where('ordering_count >', $ordering_count)->select('id, ordering_count');
			$ord_obj = $this->db->get(STR_PRE.$stream->stream_slug);
			
			if( $ord_obj->num_rows() > 0 ):
			
				$rows = $ord_obj->result();
				
				foreach( $rows as $update_row ):

					$update_data['ordering_count'] = $update_row->ordering_count - 1;
					
					$this->db->where('id', $update_row->id);
					$this->db->update(STR_PRE.$stream->stream_slug, $update_data);
					
					$update_data = array();
				
				endforeach;
			
			endif;
			
			return TRUE;
		
		endif;
	}

}

/* End of file streams_m.php */