<section class="title">
	<h4><?php echo lang('streams.streams');?></h4>
</section>

<section class="item">

<?php if (!empty($streams)): ?>
			
<table class="table-list">
	<thead>
		<tr>
		    <th><?php echo lang('streams.stream_name');?></th>
		    <th><?php echo lang('streams.stream_slug');?></th>
		    <th><?php echo lang('streams.about');?></th>
		    <th><?php echo lang('streams.total_entries');?></th>
		    <th></th>
		</tr>
	</thead>
	<tbody>
	<?php foreach ($streams as $stream):?>

	<?php
	
	// Does this table exist?
	if($this->db->table_exists(STR_PRE.$stream->stream_slug)):
	
		$table_exists = true;
		echo '<tr>';
	
	else:

		$table_exists = false;
		echo '<tr class="inactive">';
	
	endif;
	
	?>
			<td><?php echo $stream->stream_name; ?></td>
			<td><?php echo $stream->stream_slug; ?></td>
			<td><?php echo $stream->about; ?></td>

			<td><?php if($table_exists): echo number_format($this->streams_m->count_stream_entries($stream->stream_slug)); endif; ?></td>
			
			<td class="actions">
				<?php if(group_has_role('streams', 'admin_streams')): echo anchor('admin/streams/manage/' . $stream->id, lang('streams.manage'), 'class="btn orange edit"'); endif; ?> 
				<?php echo anchor('admin/streams/entries/index/' . $stream->id, lang('streams.entries'), 'class="btn orange edit"');?> 
				<?php echo anchor('admin/streams/entries/add/'.$stream->id, lang('streams.new_entry'), 'class="btn green"');?> 
			
			</td>
		</tr>
	<?php endforeach;?>
	</tbody>
</table>

<?php echo $pagination['links']; ?>

<?php else: ?>
	<div class="no_data"><?php echo lang('streams.start.no_streams');?> <?php echo anchor('admin/streams/add', lang('streams.start.adding_one')); ?>.</div>
<?php endif;?>

</section>
