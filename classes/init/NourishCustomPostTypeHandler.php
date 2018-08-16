<?php
namespace theme\init;

class NourishCustomPostTypeHandler {
	private $custom_post_types;
	private $custom_taxonomies;
	private $custom_fields;

	public function __construct($post_types = array(), $taxonomies = array(), $fields = array()) {
		$this->custom_post_types = $post_types;
		$this->custom_taxonomies = $taxonomies;
		$this->custom_fields = $fields;

		add_action('init', array($this, 'init'));
		add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
		add_action('save_post', array($this, 'save_post'), 10);
	}

	public function init() {
		$this->register_taxonomies();
		$this->register_post_types();
	}

	protected function register_taxonomies() {
		foreach ($this->custom_taxonomies as $id => $taxonomy) {
			$labels = array(
				'name' => _x( $taxonomy['plural'], 'taxonomy general name' ),
				'singular_name' => _x( $taxonomy['singular'], 'taxonomy singular name' ),
				'search_items' =>  __( 'Search ' . $taxonomy['plural'] ),
				'all_items' => __( 'All '.$taxonomy['plural'] ),
				'edit_item' => __( 'Edit '.$taxonomy['singular'] ), 
				'update_item' => __( 'Update '.$taxonomy['singular'] ),
				'add_new_item' => __( 'Add New '.$taxonomy['singular'] ),
				'new_item_name' => __( 'New '.$taxonomy['singular'].' Name' ),
				'menu_name' => __( $taxonomy['plural'] ),
			); 	
			register_taxonomy($id, $taxonomy['post_types'], array(
				'hierarchical' => true,
				'labels' => $labels,
				'show_ui' => true,
				'query_var' => true,
				'rewrite' => array( 'slug' => $taxonomy['slug'] ),
			));
		}
	}

	protected function register_post_types() {
		foreach ($this->custom_post_types as $slug => $type) {
			$labels = array(
				'name' => _x($type['plural'], 'post type general name'),
				'singular_name' => _x($type['singular'], 'post type singular name'),
				'add_new' => _x('Add New', $slug),
				'add_new_item' => __('Add New ' . $type['singular']),
				'edit_item' => __('Edit '. $type['singular']),
				'new_item' => __('New '. $type['singular']),
				'view_item' => __('View '. $type['singular']),
				'search_items' => __('Search '. $type['plural']),
				'not_found' =>  __('No '. $type['singular'] . ' Found'),
				'not_found_in_trash' => __('No '. $type['singular'] . ' Found in Trash'), 
				'parent_item_colon' => ''
			);
			$args = array(
				'labels' => $labels,
				'public' => true,
				'publicly_queryable' => true,
				'show_ui' => true, 
				'query_var' => true,
				'rewrite' => true,
				'capability_type' => 'post',
				'hierarchical' => isset($type['hierarchical']) && $type['hierarchical'],
				'menu_position' => 6,
				'rewrite' => array('slug'),
				'supports' => array('title', 'editor', 'thumbnail', 'page-attributes'),
				'taxonomies' => $type['taxonomies'],
				'has_archive' => true,
				'exclude_from_search' => false,
			); 
			register_post_type($slug, $args);
		}
	}

	public function add_meta_boxes() {
		static $i = 0;
		foreach ($this->custom_post_types as $slug => $type) {
			if (isset($type['fields'])) {
				add_meta_box( $slug . '_' . $i++ . '_nourish_meta_box', __( $type['singular'] . ' Info', 'nourish' ), array($this, 'meta_box'), $slug, 'normal', 'default', $type['fields']);
			}
		}
		foreach ($this->custom_fields as $type => $fields) {
			add_meta_box($type . '_' . $i++ . '_nourish_meta_box', __('Fields', 'nourish'), array($this, 'meta_box'), $type, 'normal', 'default', $fields);
		}
	}

	public function meta_box($post, $args) {
		wp_nonce_field( plugin_basename( __FILE__ ), 'nourish_nonce' );

		echo '<table class="form-table">';
		foreach($args['args'] as $slug => $field) {
?>
	<tr valign="top">
		<th scope="row">
			<label for="nourish_<?php echo $slug; ?>">
				<?php echo __($field['label'], 'nourish'); ?>:
			</label>
		</th>
		<td>
<?php
			$default = get_post_meta($post->ID, 'nourish_' . $slug);
			if ($default != array()) {
				$field['default'] = $default[0];
			}
			switch ($field['type']) {
			case 'text':
				$this->text_box($slug, $field);
				break;
			case 'email':
				$this->text_box($slug, $field, true);
				break;
			case 'key_value':
				$this->key_value($slug, $field);
				break;
			case 'boolean':
				$this->checkbox($slug, $field);
				break;
			case 'select':
				$this->select($slug, $field);
			default:
				break;
			}
?>
		</td>
	</tr>
<?php
		}
		echo '</table>';
	}

	public function save_post($post_id) {
		if (defined( 'DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;
		if (wp_is_post_revision($post_id)) return;
		if (!wp_verify_nonce( $_POST['nourish_nonce'], plugin_basename(__FILE__))) return;

		$post_type = get_post_type($post_id);
		$post_types = $this->custom_post_types;
		if (isset($post_types[$post_type]) && isset($post_types[$post_type]['fields'])) {
			foreach($post_types[$post_type]['fields'] as $slug => $field) {
				switch ($field['type']) {
					case 'text':
					case 'email':
						$this->save_text($post_id, $slug, $field);
						break;
					case 'key_value':
						$this->save_key_value($post_id, $slug, $field);
						break;
					case 'boolean':
						$this->save_checkbox($post_id, $slug, $field);
						break;
					case 'select':
						$this->save_select($post_id, $slug, $field);
						break;
					default:
						break;
				}
			}
		}
		$fields = $this->custom_fields;
		if (isset($fields[$post_type])) {
			foreach($fields[$post_type] as $slug => $field) {
				switch ($field['type']) {
					case 'text':
					case 'email':
						$this->save_text($post_id, $slug, $field);
						break;
					case 'key_value':
						$this->save_key_value($post_id, $slug, $field);
						break;
					case 'boolean':
						$this->save_checkbox($post_id, $slug, $field);
						break;
					case 'select':
						$this->save_select($post_id, $slug, $field);
						break;
					default:
						break;
				}
			}
		}
	}

	protected function text_box($id, $args, $email = false) {
		$type = $email ? 'email' : 'text';
		if ($args['single']): ?>
			<input type="<?php echo $type; ?>" id="nourish_<?php echo $id; ?>" name="nourish_<?php echo $id; ?>" value="<?php echo $args['default']; ?>" <?php if (isset($args['editable']) && !$args['editable']) echo 'disabled'; ?> />
		<?php else: ?>
			<ul id="nourish_<?php echo $id; ?>">	
				<?php foreach ($args['default'] as $key => $value): ?>
					<li><input type="<?php echo $type; ?>" id="nourish_<?php echo $id; ?>_<?php echo $key; ?>" name="nourish_<?php echo $id; ?>[<?php echo $key; ?>]" value="<?php echo $value; ?>" <?php if (isset($args['editable']) && !$args['editable']) echo 'disabled'; ?> /></li>
				<?php	endforeach; ?>
				<?php if (!isset($args['editable']) || $args['editable']): ?>
					<li><input type="<?php echo $type; ?>" id="nourish_<?php echo $id; ?>_<?php echo $key + 1; ?>" name="nourish_<?php echo $id; ?>[<?php echo $key + 1; ?>]" value="" /></li>
			</ul>
			<a id="nourish_<?php echo $id; ?>_add" class="button">Add Value</a>
			<?php else: ?>
			</ul>
			<?php endif; ?>
			<script type="text/javascript">
				jQuery('#nourish_<?php echo $id; ?>_add').click(function() {
					var key = jQuery('#nourish_<?php echo $id; ?> li').length;
					jQuery('#nourish_<?php echo $id; ?>').append('<li><input type="<?php echo $type; ?>" id="nourish_<?php echo $id; ?>_'+key+'" name="nourish_<?php echo $id; ?>['+key+']" value="" /></li>'); 
					return false;
				});
			</script>
		<?php	endif; 
	}

	public function select($id, $args) {
?>
			<select id="nourish_<?php echo $id; ?>" name="nourish_<?php echo $id; ?>">
<?php foreach($args['options'] as $key => $value): ?>
				<option value="<?php echo $key; ?>"<?php if ($key == $args['default']) echo ' selected'; ?>><?php echo $value; ?></option>
<?php endforeach; ?>
			</select>
<?php
	}

	protected function key_value($id, $args) {
		if ($args['single']): ?>
			<input type="text" id="nourish_<?php echo $id; ?>_key" name="nourish_<?php echo $id; ?>[0][key]" value="<?php echo $args['default']['key']; ?>" <?php if (isset($args['editable']) && !$args['editable']) echo 'disabled'; ?> />
			<input type="text" id="nourish_<?php echo $id; ?>_value" name="nourish_<?php echo $id; ?>[0][value]" value="<?php echo $args['default']['value']; ?>" <?php if (isset($args['editable']) && !$args['editable']) echo 'disabled'; ?> />
		<?php else: ?>
			<ul id="nourish_<?php echo $id; ?>">	
				<?php $key = -1; ?>
				<?php foreach ($args['default'] as $key => $value): ?>
					<li>
						<input type="text" id="nourish_<?php echo $id; ?>_<?php echo $key; ?>_key" name="nourish_<?php echo $id; ?>[<?php echo $key; ?>][key]" value="<?php echo $value['key']; ?>" <?php if (isset($args['editable']) && !$args['editable']) echo 'disabled'; ?> />
						<input type="text" id="nourish_<?php echo $id; ?>_<?php echo $key; ?>_value" name="nourish_<?php echo $id; ?>[<?php echo $key; ?>][value]" value="<?php echo $value['value']; ?>" <?php if (isset($args['editable']) && !$args['editable']) echo 'disabled'; ?> />
					</li>
				<?php	endforeach; ?>
				<?php if (!isset($args['editable']) || $args['editable']): ?>
					<li>
						<input type="text" id="nourish_<?php echo $id; ?>_<?php echo $key + 1; ?>_key" name="nourish_<?php echo $id; ?>[<?php echo $key + 1; ?>][key]" value="" />
						<input type="text" id="nourish_<?php echo $id; ?>_<?php echo $key + 1; ?>_value" name="nourish_<?php echo $id; ?>[<?php echo $key + 1; ?>][value]" value="" />
					</li>
			</ul>
			<a id="nourish_<?php echo $id; ?>_add" class="button">Add Value</a>
			<?php else: ?>
			</ul>
			<?php endif; ?>
			<script type="text/javascript">
				jQuery('#nourish_<?php echo $id; ?>_add').click(function() {
					var key = jQuery('#nourish_<?php echo $id; ?> li').length;
					jQuery('#nourish_<?php echo $id; ?>').append('<li><input type="text" id="nourish_<?php echo $id; ?>_'+key+'_key" name="nourish_<?php echo $id; ?>['+key+'][key]" value="" /><input type="text" id="nourish_<?php echo $id; ?>_'+key+'_value" name="nourish_<?php echo $id; ?>['+key+'][value]" value="" /></li>'); 
					return false;
				});
			</script>
		<?php	endif; 
	}

	protected function checkbox($id, $args) {
?>
		<input type="checkbox" id="nourish_<?php echo $id; ?>" name="nourish_<?php echo $id; ?>" <?php if ($args['default']) echo 'checked'; ?> <?php if (isset($args['editable']) && !$args['editable']) echo 'disabled'; ?> />
<?php
	}

	protected function save_select($post_id, $field_id, $args) {
		$value = $args['default'];
		$submitted = $_POST['nourish_'.$field_id];
		if ($submitted) {
			if (in_array($submitted, array_keys($args['options']))) {
				$value = $submitted;
			}
		}
		update_post_meta($post_id, 'nourish_' . $field_id, $value);
	}

	protected function save_checkbox($post_id, $field_id, $args) {
		$value = isset($_POST['nourish_' . $field_id]);
		update_post_meta($post_id, 'nourish_' . $field_id, $value);
	}

	protected function save_text($post_id, $field_id, $args) {
		if (!isset($_POST['nourish_' . $field_id])) return;
		$value = '';
		if ($args['single']) {
			$value = sanitize_text_field($_POST['nourish_' . $field_id]);
			if ($value == '') $value = $args['default'];
		} else {
			$results = array();
			foreach ($_POST['nourish_' . $field_id] as $key => $value) {
				if ($result = sanitize_text_field($value)) {
					$results[] = $result;
				}
			}
			if (count($results)) $value = $results;
		}
		update_post_meta($post_id, 'nourish_' . $field_id, $value);
	}

	protected function save_key_value($post_id, $field_id, $args) {
		if (!isset($_POST['nourish_' . $field_id])) return;
		if ($args['single']) {
			$value[0]['key'] = sanitize_text_field($_POST['nourish_' . $field_id]['key']);
			$value[0]['value'] = sanitize_text_field($_POST['nourish_' . $field_id]['value']);
		} else {
			$results = array();
			foreach ($_POST['nourish_' . $field_id] as $key => $value) {
				$result = array();
				$result['key'] = sanitize_text_field($value['key']);
				$result['value'] = sanitize_text_field($value['value']);
				if ($result['key'] != '' && $result['value'] != '') {
					$results[] = $result;
				}
			}
			$value = $results;
		}
		update_post_meta($post_id, 'nourish_' . $field_id, $value);
	}
	public function set_field($post_id, $field_id, $value) {
		update_post_meta($post_id, 'nourish_' . $field_id, $value);
	}

	public function get_field($post_id, $field_id) {
		return get_post_meta($post_id, 'nourish_' . $field_id);
	}
}
