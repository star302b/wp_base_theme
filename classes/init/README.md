wordpress-theme-class
=====================

A helper class to initialize all the settings a Wordpress theme might need, and allow easy definition of custom post types and fields in code.

Instead of remembering which functions to call inside which Wordpress hooks, just override some class variables.

This class will help with:

* Sidebars
* Nav menus
* Image sizes & Default thumbnail size
* Customize theme page
* Custom post types
* Custom fields
* Custom taxonomies
* Shortcodes
* Action hooks

Examples
========

Basic example; sets sensible defaults for your theme:
    
		include_once('Nourish.php');
				
		class MyTheme extends Nourish {
		}

		new MyTheme();

Sidebars & Nav Menus
--------------------

Add more sidebars, navigation menus and image sizes, set the default thumbnail size and change the excerpt length:
    
		include_once('Nourish.php');

		class MyTheme extends Nourish {
			protected $sidebars = array(
				'left-sidebar' => 'Left Sidebar',
				'right-sidebar' => 'Right Sidebar',
			);

			protected $nav_menus = array(
				'tertiary' => 'Tertiary Menu',
			);

			protected $image_sizes = array(
				'banner' => array(960, 150, true),
			);

			protected $thumbnail_size = array(150, 150, true);

			protected $excerpt_length = 40;
		}

		new MyTheme();

Customize Menu
--------------

Add sections and options to the Wordpress Customize page:
    
		include_once('Nourish.php');

		class MyTheme extends Nourish {
			protected $customize_sections = array(
				'mytheme_header_settings' => array(
					'title' => 'MyTheme Header Settings',
					'settings' => array(
						'mytheme_header_message' => array(
							'type' => 'text',
							'label' => 'Header Message',
							'default' => 'Welcome to MyTheme',
						),
					),
				),
				'mytheme_footer_settings' => array(
					'title' => 'MyTheme Footer Settings',
					'settings' => array(
						'mytheme_footer_message' => array(
							'type' => 'text',
							'label' => 'Footer Message',
							'default' => '&copy; Copyright 2014',
						),
						'mytheme_footer_message_color' => array(
							'type' => 'color',
							'label' => 'Footer Message Color',
							'default' => '#000000',
						),
						'mytheme_footer_logo' => array(
							'type' => 'image',
							'label' -> 'Footer Logo',
						),
						'mytheme_pdf_download' => array(
							'type' => 'upload',
							'label' => 'PDF Download',
							'option' => true		// Use with get_option() instead of get_theme_mod()
						),
					),
				),
			);
		}

		new MyTheme();

Custom Post Types and Fields
----------------------------

Add custom post types with various custom fields:
    
		include_once('Nourish.php');
		
		class MyTheme extends Nourish {
			protected $custom_post_types = array(
				'staff' => array(
					'singular' => 'Member of Staff',
					'plural' => 'Members of Staff',
					'taxonomies' => array(
						'department',
					),
					'fields' => array(
						'phone' => array(
							'type' => 'text',
							'label' => 'Telephone',
							'default' => '',
							'single' => true,
						),
						'email' => array(
							'type' => 'email',
							'label' => 'Email',
							'default' => '',
							'single' => true,
						),
						'linkedin' => array(
							'type' => 'text',
							'label' => 'LinkedIn',
							'default' => '',
							'single' => true,
						),
						'recognition' => array(
							'type' => 'key_value',
							'label' => 'Recognition',
							'default' => array(),
							'single' => false,
						),
					),
				),
			);
		}

		new MyTheme();

Add custom fields to existing post types:

		include_once('Nourish.php');

		class MyTheme extends Nourish {
			protected $custom_fields = array(
				'page' => array(
					'extrainfo' => array(
						'type' => 'text',
						'label' => 'Extra page info',
						'default' => '',
						'single' => true,
					),
					'region' => array(
						'type' => 'select',
						'label' => 'Region',
						'default' => 'us',
						'single' => true,
						'options' => array(
							'us' => 'US',
							'eu' => 'EU',
						),
					),
				),
			);
		}

		new MyTheme();

Custom fields are accessed using get\_post\_meta(), and the key is 'nourish\_' + the key you set for the array defining the field. 

TODO: Add a nicer way to do this.

Custom Taxonomies
-----------------

Add custom taxonomies:

		include_once('Nourish.php');

		class MyTheme extends Nourish {
			protected $custom_taxonomies = array(
				'department' => array(
					'singular' => 'Department',
					'plural' => 'Departments',
					'post_types' => array(),
					'slug' => 'department',
				),
			);
		}

		new MyTheme();

Apply a cusom taxonomy to a post type by listing it in the post\_types array (useful for existing post types), or listing the taxonomy in the taxonomies array of a custom post type (see above).

Shortcodes
----------

Add shortcodes to your theme:

		include_once('Nourish.php');

		class MyTheme extends Nourish {
			protected $shortcodes = array(
				'myShortcode' => 'myShortcodeFunc',
				'anotherShortcode' => 'anotherShortcodeFunc',
			);

			public function myShortcodeFunc( $atts, $content = '' ) {
				return $content . ' is the content';
			}

			public function anotherShortcodeFunc( $atts, $content = '' ) {
				return $content . ' is also the content';
			}
		}

		new MyTheme();

Action Hooks
------------

Finally if you just need to add some action hooks:

		include_once('Nourish.php');

		class MyTheme extends Nourish {
			protected $actions = array(
					'wp_init' => 'my_wp_init',
					'after_theme_setup' => 'my_after_theme_setup',
					);

			public function my_wp_init() {
			}

			public function my_after_theme_setup() {
			}
		}

		new MyTheme();

TODO: Add the same functionality for filter hooks.

Bonus
-----

It should also enqueue your theme style with a version number based on the last time you edited style.css.

TODO: Check it actually does
