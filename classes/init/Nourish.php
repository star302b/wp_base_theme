<?php
namespace theme\init;
define('NOURISH_PATH',get_template_directory());

class NourishDefaults {
	protected $more_string = '...';
	protected $excerpt_length = 20;
	protected $thumbnail_size = array(110, 72, true);
	protected $theme_support_custom = array();

	private $default_customize_sections = array(
		'nourish_logos' => array(
			'title' => 'Logos',
			'settings' => array(
				'nourish_header_logo_vector' => array(
					'type' => 'upload',
					'label' => 'Header Logo Vector',
				),
				'nourish_header_logo' => array(
					'type' => 'image',
					'label' => 'Header Logo',
				),
				'nourish_footer_logo_vector' => array(
					'type' => 'upload',
					'label' => 'Footer Logo Vector',
				),
				'nourish_footer_logo' => array(
					'type' => 'image',
					'label' => 'Footer Logo',
				),
			),
		),
		'nourish_colors' => array(
			'title' => 'Colors',
			'settings' => array(
				'nourish_link_color' => array(
					'type' => 'color',
					'label' => 'Link Color',
					'default' => 'blue',
				),
				'nourish_hover_color' => array(
					'type' => 'color',
					'label' => 'Hover Color',
					'default' => 'purple',
				),
			),
		),
		'nourish_contact_settings' => array(
			'title' => 'Contact Settings',
			'settings' => array(
				'nourish_address' => array(
					'type' => 'text',
					'label' => 'Address',
					'section' => 'nourish_contact_settings',
					'option' => true,
				),
				'nourish_phone' => array(
					'type' => 'text',
					'label' => 'Phone',
					'section' => 'nourish_contact_settings',
					'option' => true,
				),
				'nourish_fax' => array(
					'type' => 'text',
					'label' => 'Fax',
					'section' => 'nourish_contact_settings',
					'option' => true,
				),
				'nourish_email' => array(
					'type' => 'text',
					'label' => 'Email',
					'section' => 'nourish_contact_settings',
					'option' => true,
				),
				'nourish_twitter' => array(
					'type' => 'text',
					'label' => 'Twitter',
					'section' => 'nourish_contact_settings',
					'option' => true,
				),
				'nourish_linkedin' => array(
					'type' => 'text',
					'label' => 'LinkedIn',
					'section' => 'nourish_contact_settings',
					'option' => true,
				),
				'nourish_facebook' => array(
					'type' => 'text',
					'label' => 'Facebook',
					'section' => 'nourish_contact_settings',
					'option' => true,
				),
			),
		),
	);

	private $default_custom_post_types = array();
	private $default_custom_taxonomies = array();
	private $default_custom_fields = array();

	private $default_sidebars = array(
		'sidebar-1' => 'Main Sidebar',
		'footer-column-1' => 'Footer Column 1',
		'footer-column-2' => 'Footer Column 2',
		'footer-column-3' => 'Footer Column 3',
	);

	private $default_nav_menus = array(
		'primary' => 'Primary Menu',
		'secondary' => 'Secondary Menu',
		'footer' => 'Footer Menu',
	);

	private $default_actions = array(
		'after_setup_theme' => 'after_setup_theme',
		'widgets_init' => 'widgets_init',
		'wp_enqueue_scripts' => 'wp_enqueue_scripts',
		'customize_register' => 'customize_register',
		'wp_head' => 'wp_head',
	);

	private $default_shortcodes = array(

	);

	private $default_image_sizes = array(
		'small' => array(280, 180, true),
		'medium' => array(600, 460, true),
		'large' => array(940, 700, true),
	);

	private $theme_supports = array(
		'automatic-feed-links',
		'post-thumbnails',
	);

	public function __construct() {
		$this->add_actions();
		$this->add_shortcodes();
		new NourishCustomPostTypeHandler($this->get_custom_post_types(), $this->get_custom_taxonomies(), $this->get_custom_fields());
	}

	protected function add_actions() {
		foreach ($this->get_actions() as $action => $function) {
			if (is_array($function)) {
				foreach($function as $f) {
					add_action($action, array($this, $f));
				}
			} else {
				add_action($action, array($this, $function));
			}
		}
		add_filter('excerpt_length', array($this, 'excerpt_length_function'), 999);
		add_filter('wp_trim_excerpt', array($this, 'excerpt_more'), 999);
	}

	public function excerpt_more($excerpt) {
		return (str_replace('[...]', $this->more_string, $excerpt));
	}

	public function excerpt_length_function($length) {
		return $this->excerpt_length;
	}


	protected function add_shortcodes() {
		foreach ($this->get_shortcodes() as $shortcode => $function) {
			if (is_array($function)) {
				foreach ($function as $f) {
					add_shortcode($shortcode, array($this, $f));
				}
			} else {
				add_shortcode($shortcode, array($this, $shortcode));
			}
		}
	}

	public function after_setup_theme() {
		foreach ($this->get_nav_menus() as $id => $name) {
			register_nav_menu( $id, __( $name, 'nourish' ) );
		}

		foreach ($this->theme_supports as $theme_support) {
			add_theme_support($theme_support);
		}

		foreach ($this->theme_support_custom as $theme_support_item){
            add_theme_support($theme_support_item);
        }

		set_post_thumbnail_size( $this->thumbnail_size[0], $this->thumbnail_size[1], $this->thumbnail_size[2]);
		foreach ($this->get_image_sizes() as $id => $size) {
			add_image_size($id, $size[0], $size[1], $size[2]);
		}
	}

	public function widgets_init() {
		foreach($this->get_sidebars() as $id => $name) {
			register_sidebar(array(
				'name' => __( $name, 'nourish' ),
				'id' => $id,
				'before_widget' => '<aside id="%1$s" class="widget %2$s"><div class="aside-inner">',
				'after_widget' => '</div></aside>',
				'before_title' => '<h3 class="widget-title">',
				'after_title' => '</h3>',
			));
		}
	}

	public function wp_enqueue_scripts() {
		wp_enqueue_style( 'nourish-style', get_stylesheet_uri(), array(), filemtime(NOURISH_PATH . '/style.css') + filemtime(get_stylesheet_directory() . '/style.css') );
	}

	public function customize_register($wp_customize) {
		$i = 0;
		foreach ($this->get_customize_sections() as $id => $section) {
			$wp_customize->add_section($id, array(
				'title' => __($section['title'], 'nourish'),
				'priority' => $i+=10,
			));
			foreach ($section['settings'] as $option => $args) {
				$wp_customize->add_setting($option, array(
					'default' => isset($args['default']) ? $args['default'] : '',
					'type' => isset($args['option']) && $args['option'] ? 'option' : 'theme_mod',
				));
				switch($args['type']) {
				case 'text':
					$wp_customize->add_control(new \WP_Customize_Control($wp_customize, $option, array(
						'label' => __($args['label'], 'nourish'),
						'section' => $id,
						'settings' => $option,		
					)));
					break;
				case 'image':
					$wp_customize->add_control(new \WP_Customize_Image_Control($wp_customize, $option, array(
						'label' => __($args['label'], 'nourish'),
						'section' => $id,
						'settings' => $option,		
					)));
					break;
				case 'upload':
					$wp_customize->add_control(new \WP_Customize_Upload_Control($wp_customize, $option, array(
						'label' => __($args['label'], 'nourish'),
						'section' => $id,
						'settings' => $option,
					)));
					break;
				case 'color':
					$wp_customize->add_control(new \WP_Customize_Color_Control($wp_customize, $option, array(
						'label' => __($args['label'], 'nourish'),
						'section' => $id,
						'settings' => $option,
					)));
					break;
				default:
					break;
				}
			}
		}
	}

	public function wp_head() {
		$this->customize_css();
	}

	protected function customize_css() {
		$linkcolor = get_theme_mod('nourish_link_color');
		$hovercolor = get_theme_mod('nourish_hover_color');
//        include(NOURISH_PATH . '/css/custom.php');
	}

	protected function get_shortcodes() {
		return $this->default_shortcodes;
	}

	protected function get_custom_post_types() {
		return $this->default_custom_post_types;
	}

	protected function get_custom_taxonomies() {
		return $this->default_custom_taxonomies;
	}

	protected function get_custom_fields() {
		return $this->default_custom_fields;
	}

	protected function get_customize_sections() {
		return $this->default_customize_sections;
	}

	protected function get_sidebars() {
		return $this->default_sidebars;
	}

	protected function get_nav_menus() {
		return $this->default_nav_menus;
	}

	protected function get_actions() {
		return $this->default_actions;
	}

	protected function get_image_sizes() {
		return $this->default_image_sizes;
	}

}

class Nourish extends NourishDefaults {
	protected $customize_sections = array();
	protected $custom_post_types = array();
	protected $custom_taxonomies = array();
	protected $custom_fields = array();
	protected $sidebars = array();
	protected $nav_menus = array();
	protected $actions = array();
	protected $image_sizes = array();
	protected $shortcodes = array();

	protected function get_custom_post_types() {
		$post_types = parent::get_custom_post_types();
		return array_merge($post_types, $this->custom_post_types);
	}

	protected function get_custom_taxonomies() {
		$taxonomies = parent::get_custom_taxonomies();
		return array_merge($taxonomies, $this->custom_taxonomies);
	}

	protected function get_custom_fields() {
		$fields = parent::get_custom_fields();
		return array_merge($fields, $this->custom_fields);
	}

	protected function get_customize_sections() {
		$sections = parent::get_customize_sections();
		return array_merge_recursive($sections, $this->customize_sections);
	}

	protected function get_sidebars() {
		$sidebars = parent::get_sidebars();
		return array_merge_recursive($sidebars, $this->sidebars);
	}

	protected function get_nav_menus() {
		$nav_menus = parent::get_nav_menus();
		return array_merge($nav_menus, $this->nav_menus);
	}

	protected function get_actions() {
		$actions = parent::get_actions();
		return array_merge_recursive($actions, $this->actions);
	}

	protected function get_image_sizes() {
		$image_sizes = parent::get_image_sizes();
		return array_merge($image_sizes, $this->image_sizes);
	}

	protected function get_shortcodes() {
		$shortcodes = parent::get_shortcodes();
		return array_merge_recursive($shortcodes, $this->shortcodes);
	}
}
