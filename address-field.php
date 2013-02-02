<?php
/*
Plugin Name:  Advanced Custom Fields - Address Field add-on
Plugin URI:   https://github.com/GCX/acf-address-field
Description:  Adds an Address Field to Advanced Custom Fields. Pick and choose the components and layout of the address.
Version:      1.0.2
Author:       Brian Zoetewey
Author URI:   https://github.com/GCX
Contributors: Ryan Urban <ryan@fringewebdevelopment.com>
Text Domain:  acf-address-field
Domain Path:  /languages/
License:      Modified BSD

	Copyright (c) 2012, CAMPUS CRUSADE FOR CHRIST
	All rights reserved.
	
	Redistribution and use in source and binary forms, with or without modification,
	are permitted provided that the following conditions are met:
 
	Redistributions of source code must retain the above copyright notice, this
	list of conditions and the following disclaimer.
	Redistributions in binary form must reproduce the above copyright notice,
	this list of conditions and the following disclaimer in the documentation
	and/or other materials provided with the distribution.
	Neither the name of CAMPUS CRUSADE FOR CHRIST nor the names of its
	contributors may be used to endorse or promote products derived from this
	software without specific prior written permission.
 
	THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
	ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
	WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
	IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT,
	INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
	BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
	DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF
	LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE
	OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED
	OF THE POSSIBILITY OF SUCH DAMAGE.
	
 */

if( !class_exists( 'ACF_Address_Field' ) && class_exists( 'acf_Field' ) ) :

/**
 * Global ConneXion - Advanced Custom Fields - Address Field
 * 
 * This addon to Advanced Custom Fields adds the capability for
 * a multi-component address input. It has the ability to customize the
 * individual components and the layout of the address block, and uses
 * the microformats Address specification as well. You can also choose
 * whether or not to use a standard HTML container or an HTML5 address
 * container.
 * 
 * @author Brian Zoetewey <brian.zoetewey@ccci.org>
 * @contributor Ryan Urban <ryan@fringewebdevelopment.com>
 * @version 1.0.1
 */
class ACF_Address_Field extends acf_Field {

	/**
	* WordPress Localization Text Domain
	*
	* The textdomain for the field is controlled by the helper class.
	* @var string
	*/
	private $l10n_domain;
	
	/**
	 * Class Constructor - Instantiates a new Address Field
	 * @param Acf $parent Parent Acf class
	 */
	public function __construct( $parent ) {
		parent::__construct( $parent );
		
		// Get the textdomain from the Helper class
		$this->l10n_domain = ACF_Address_Field_Helper::L10N_DOMAIN;
		
		// Our ACF add-on name & title
		$this->name  = 'address-field';
		$this->title = __( 'Address', 'acf-address-field' );
		
		add_action( 'admin_print_scripts', array( &$this, 'admin_print_scripts' ), 12, 0 );
		add_action( 'admin_print_styles', array( &$this, 'admin_print_styles' ), 12, 0 );
	}
	
	/**
	 * Registers and enqueues necessary CSS
	 * 
	 * This method is called by ACF when rendering a post add or edit screen.
	 * We also call this method on the Acf Field Options screen as well in order
	 * to style out Field options
	 * 
	 * @see acf_Field::admin_print_styles()
	 */
	public function admin_print_styles() {
		global $pagenow;

		wp_register_style( 'acf-address-field', plugins_url( 'address-field.css', __FILE__ ) );
		
		if( in_array( $pagenow, array( 'post.php', 'post-new.php', 'admin.php' ) ) ) {
			wp_enqueue_style( 'acf-address-field' );
		}
	}
	
	/**
	 * Registers and enqueues necessary JavaScript
	 * 
	 * This method is called by ACF when rendering a post add or edit screen.
	 * We also call this method on the Acf Field Options screen as well in order
	 * to add the necessary JavaScript for address layout.
	 * 
	 * @see acf_Field::admin_print_scripts()
	 */
	public function admin_print_scripts() {
		global $pagenow;
		wp_register_script( 'acf-address-field', plugins_url( 'address-field.js', __FILE__ ), array( 'jquery-ui-sortable' ) );
		
		if( in_array( $pagenow, array( 'post.php', 'post-new.php', 'admin.php' ) ) ) {
			wp_enqueue_script( 'acf-address-field' );
		}
	}
	
	/**
	* Populates the fields array with defaults for this field type
	*
	* @param array $field
	* @return array
	*/
	private function set_field_defaults( &$field ) {
		$component_defaults = array(
			'address1'    => array(
				'label'         => __( 'Address 1', 'acf-address-field' ),
				'default_value' => '',
				'enabled'       => 1,
				'class'         => 'street-address',
				'separator'     => '',
			),
			'address2'    => array(
				'label'         => __( 'Address 2', 'acf-address-field' ),
				'default_value' => '',
				'enabled'       => 1,
				'class'         => 'extended-address',
				'separator'     => '',
			),
			'address3'    => array(
				'label'         => __( 'Address 3', 'acf-address-field' ),
				'default_value' => '',
				'enabled'       => 1,
				'class'         => 'extended-address',
				'separator'     => '',
			),
			'city'        => array(
				'label'         => __( 'City', 'acf-address-field' ),
				'default_value' => '',
				'enabled'       => 1,
				'class'         => 'locality',
				'separator'     => ',',
			),
			'state'       => array(
				'label'         => __( 'State', 'acf-address-field' ),
				'default_value' => '',
				'enabled'       => 1,
				'class'         => 'region',
				'separator'     => '',
			),
			'postal_code' => array(
				'label'         => __( 'Postal Code', 'acf-address-field' ),
				'default_value' => '',
				'enabled'       => 1,
				'class'         => 'postal-code',
				'separator'     => '',
			),
			'country'     => array(
				'label'         => __( 'Country', 'acf-address-field' ),
				'default_value' => '',
				'enabled'       => 1,
				'class'         => 'country-name',
				'separator'     => '',
			),
		);

		$layout_defaults = array(
			0 => array( 0 => 'address1' ),
			1 => array( 0 => 'address2' ),
			2 => array( 0 => 'address3' ),
			3 => array( 0 => 'city', 1 => 'state', 2 => 'postal_code', 3 => 'country' ),
		);
		
		$field[ 'address_components' ] = ( array_key_exists( 'address_components' , $field ) && is_array( $field[ 'address_components' ] ) ) ?
			wp_parse_args( (array) $field[ 'address_components' ], $component_defaults ) :
			$component_defaults;
		
		$field[ 'address_layout' ] = ( array_key_exists( 'address_layout', $field ) && is_array( $field[ 'address_layout' ] ) ) ?
			(array) $field[ 'address_layout' ] : $layout_defaults;
			
		$field['container'] = isset($field['container']) ? $field['container'] : 'address';
		
		return $field;
	}
	
	/**
	 * Creates the address field for inside post metaboxes
	 * 
	 * @see acf_Field::create_field()
	 */
	public function create_field( $field ) {
		$this->set_field_defaults( $field );
		
		$components = $field[ 'address_components' ];
		$layout = $field[ 'address_layout' ];
		$values = (array) $field[ 'value' ];

		?>
		<div class="address">
		<?php foreach( $layout as $layout_row ) : if( empty($layout_row) ) continue; ?>
			<div class="address_row">
			<?php foreach( $layout_row as $name ) : if( empty( $name ) || !$components[ $name ][ 'enabled' ] ) continue; ?>
				<label class="<?php echo $components[ $name ][ 'class' ]; ?>">
					<?php echo $components[ $name ][ 'label' ]; ?>
					<input type="text" id="<?php echo $field[ 'name' ]; ?>[<?php echo $name; ?>]" name="<?php echo $field[ 'name' ]; ?>[<?php echo $name; ?>]" value="<?php echo esc_attr( $values[ $name ] ); ?>" />
				</label>
				<?php endforeach; ?>
			</div>
		<?php endforeach; ?>
		</div>
		<div class="clear"></div>
		<?php
	}
	
	/**
	 * Builds the field options
	 * 
	 * @see acf_Field::create_options()
	 * @param string $key
	 * @param array $field
	 */
	public function create_options( $key, $field ) {
		$this->set_field_defaults( $field );
		
		$components = $field[ 'address_components' ];
		$layout = $field[ 'address_layout' ];
		$missing = array_keys( $components );
		
		?>
			<tr class="field_option field_option_<?php echo $this->name; ?>">
				<td class="label">
					<label><?php _e( 'Address Components' , 'acf-address-field' ); ?></label>
					<p class="description">
						<strong><?php _e( 'Enabled', 'acf-address-field' ); ?></strong>: <?php _e( 'Is this component used.', 'acf-address-field' ); ?><br />
						<strong><?php _e( 'Label', 'acf-address-field' ); ?></strong>: <?php _e( 'Used on the add or edit a post screen.', 'acf-address-field' ); ?><br />
						<strong><?php _e( 'Default Value', 'acf-address-field' ); ?></strong>: <?php _e( 'Default value for this component.', 'acf-address-field' ); ?><br />
						<strong><?php _e( 'CSS Class', 'acf-address-field' ); ?></strong>: <?php _e( 'Class added to the component when using the api. Includes the <a href="http://microformats.org/wiki/adr" rel="external">microformat class per the Address spec</a> by default. For multiple classes, separate with a space', 'acf-address-field' ); ?><br />
						<strong><?php _e( 'Separator', 'acf-address-field' ); ?></strong>: <?php _e( 'Text placed after the component when using the api.', 'acf-address-field' ); ?><br />
					</p>
				</td>
				<td>
					<table>
						<thead>
							<tr>
								<th><?php _e( 'Field', 'acf-address-field' ); ?></th>
								<th><?php _e( 'Enabled', 'acf-address-field' ); ?></th>
								<th><?php _e( 'Label', 'acf-address-field' ); ?></th>
								<th><?php _e( 'Default Value', 'acf-address-field' ); ?></th>
								<th><?php _e( 'CSS Class', 'acf-address-field' ); ?></th>
								<th><?php _e( 'Separator', 'acf-address-field' ); ?></th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<th><?php _e( 'Field', 'acf-address-field' ); ?></th>
								<th><?php _e( 'Enabled', 'acf-address-field' ); ?></th>
								<th><?php _e( 'Label', 'acf-address-field' ); ?></th>
								<th><?php _e( 'Default Value', 'acf-address-field' ); ?></th>
								<th><?php _e( 'CSS Class', 'acf-address-field' ); ?></th>
								<th><?php _e( 'Separator', 'acf-address-field' ); ?></th>
							</tr>
						</tfoot>
						<tbody>
							<?php foreach( $components as $name => $settings ) : ?>
								<tr>
									<td><?php echo $name; ?></td>
									<td>
										<?php
											$this->parent->create_field( array(
												'type'  => 'true_false',
												'name'  => "fields[{$key}][address_components][{$name}][enabled]",
												'value' => $settings[ 'enabled' ],
												'class' => 'address_enabled',
											) );
										?>
									</td>
									<td>
										<?php
											$this->parent->create_field( array(
												'type'  => 'text',
												'name'  => "fields[{$key}][address_components][{$name}][label]",
												'value' => $settings[ 'label' ],
												'class' => 'address_label',
											) );
										?>
									</td>
									<td>
										<?php
											$this->parent->create_field( array(
												'type'  => 'text',
												'name'  => "fields[{$key}][address_components][{$name}][default_value]",
												'value' => $settings[ 'default_value' ],
												'class' => 'address_default_value',
											) );
										?>
									</td>
									<td>
										<?php
											$this->parent->create_field( array(
												'type'  => 'text',
												'name'  => "fields[{$key}][address_components][{$name}][class]",
												'value' => $settings[ 'class' ],
												'class' => 'address_class',
											) );
										?>
									</td>
									<td>
										<?php
											$this->parent->create_field( array(
												'type'  => 'text',
												'name'  => "fields[{$key}][address_components][{$name}][separator]",
												'value' => $settings[ 'separator' ],
												'class' => 'address_separator',
											) );
										?>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</td>
			</tr>
			<tr class="field_option field_option_<?php echo $this->name; ?>">
				<td class="label">
					<label><?php _e( 'Address Layout' , 'acf-address-field' ); ?></label>
					<p class="description"><?php _e( 'Drag address components to the desired location. This controls the layout of the address in post metaboxes and the get_field() api method.', 'acf-address-field' ); ?></p>
					<input type="hidden" name="address_layout_key" value="<?php echo $key; ?>" />
				</td>
				<td>
					<div class="address_layout">
						<?php
							$row = 0;
							foreach( $layout as $layout_row ) :
								if( count( $layout_row ) <= 0 ) continue;
						?>
							<label><?php printf( __( 'Line %d:', 'acf-address-field' ), $row + 1 ); ?></label>
							<ul class="row">
								<?php
									$col = 0;
									foreach( $layout_row as $name ) :
										if( empty( $name ) ) continue;
										if( !$components[ $name ][ 'enabled' ] ) continue;
										
										if( ( $index = array_search( $name, $missing, true ) ) !== false )
											array_splice( $missing, $index, 1 );
								?>
									<li class="item" name="<?php echo $name; ?>">
										<?php echo $components[ $name ][ 'label' ]; ?>
										<input type="hidden" name="<?php echo "fields[{$key}][address_layout][{$row}][{$col}]"?>" value="<?php echo $name; ?>" />
									</li>
								<?php
										$col++;
									endforeach;
								?>
							</ul>
						<?php
								$row++;
							endforeach;
							for( ; $row < 4; $row++ ) :
						?>
							<label><?php printf( __( 'Line %d:', 'acf-address-field' ), $row + 1 ); ?></label>
							<ul class="row">
							</ul>
						<?php endfor; ?>
						<label><?php _e( 'Not Displayed:', 'acf-address-field' ); ?></label>
						<ul class="row missing">
							<?php foreach( $missing as $name ) : ?>
								<li class="item <?php echo $components[ $name ][ 'enabled' ] ? '' : 'disabled'; ?>" name="<?php echo $name; ?>">
									<?php echo $components[ $name ][ 'label' ]; ?>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				</td>
			</tr>
			<tr class="field_option field_option_<?php echo $this->name; ?>">
			<td class="label">
				<label><?php _e('Address Parent Container', 'acf-address-field'); ?></label>
				<p class="description"><?php _e('Would you like to use the <abbr title="">HTML5</abbr> <code>address</code> tag?', 'acf-address-field'); ?></p>
			</td>
			<td>
				<?php 
				$this->parent->create_field(array(
					'type' => 'radio',
					'name' => 'fields['.$key.'][container]',
					'value' => $field['container'],					
					'layout' => 'horizontal',
					'choices' => array(
						'address' => __('Yes', 'acf-location-field'),
						'div' => __('No', 'acf-location-field')
					)					
				));
				?>
			</td>
			</tr>
		<?php
	}
	
	/**
	 * Returns the values of the field
	 * 
	 * @see acf_Field::get_value()
	 * @param int $post_id
	 * @param array $field
	 * @return array  
	 */
	public function get_value( $post_id, $field ) {
		$this->set_field_defaults( $field );
		
		$components = $field[ 'address_components' ];
		
		$defaults = array();
		foreach( $components as $name => $settings )
			$defaults[ $name ] = $settings[ 'default_value' ];
		
		$value = (array) parent::get_value( $post_id, $field );
		$value = wp_parse_args($value, $defaults);
		
		return $value;
	}
	
	/**
	 * Returns the value of the field for the advanced custom fields API
	 * 
	 * @see acf_Field::get_value_for_api()
	 * @param int $post_id
	 * @param array $field
	 */
	public function get_value_for_api( $post_id, $field ) {
		$this->set_field_defaults( $field );
		
		$components = $field[ 'address_components' ];
		$layout = $field[ 'address_layout' ];
		$values = $this->get_value( $post_id, $field );
		$check_values = array_filter( $values );
		
		$output = '';
		if ( !empty( $check_values ) ) {
			$output .= ($field['container'] == 'address') ? '<address class="adr">' : '<div class="adr">';
			foreach( $layout as $layout_row ) {
				// Check if first or last address element in row				
				$i = 0; $rows = count( $layout_row );
				foreach( $layout_row as $name ) {
					if( $values[ $name ] && $components[ $name ][ 'enabled' ] ) {
						
						if ($i == 0) // first element in row
        					$output .= '<div class="address_row">';
						
						$output .= sprintf(
							'<span %2$s>%1$s</span>%3$s ',
							$values[ $name ],
							$components[ $name ][ 'class' ] ? 'class="' . esc_attr( $components[ $name ][ 'class' ] ) . '"' : '',
							$components[ $name ][ 'separator' ] ? esc_html( $components[ $name ][ 'separator' ] ) : ''
						);
							
						if ($i == $rows - 1) // last element in row
        					$output .= '</div><!-- .address-row -->';
    						
    					$i++;
							
					}
				}
			}
			$output .= ($field['container'] == 'address') ? '</address><!-- .adr -->' : '</div><!-- .adr -->';
		}
		
		return $output;
	}
}

endif; //class_exists 'ACF_Address_Field'

if( !class_exists( 'ACF_Address_Field_Helper' ) ) :

/**
 * Advanced Custom Fields - Address Field Helper
 * 
 * This class is a Helper for the ACF_Address_Field class.
 * 
 * It provides:
 * Localization support and registering the textdomain with WordPress.
 * Registering the address field with Advanced Custom Fields. There is no need in your plugin or theme
 * to manually call the register_field() method, just include this file.
 * <code> include_once( rtrim( dirname( __FILE__ ), '/' ) . '/acf-address-field/address-field.php' ); </code>
 * 
 * @author Brian Zoetewey <brian.zoetewey@ccci.org>
 * @todo Provide shortcode support for address fields
 */
class ACF_Address_Field_Helper {
	
	/**
	 * Singleton instance
	 * @var ACF_Address_Field_Helper
	 */
	private static $instance;
	
	/**
	 * Returns the ACF_Address_Field_Helper singleton
	 * 
	 * <code>$obj = ACF_Address_Field_Helper::singleton();</code>
	 * @return ACF_Address_Field_Helper
	 */
	public static function singleton() {
		if( !isset( self::$instance ) ) {
			$class = __CLASS__;
			self::$instance = new $class();
		}
		return self::$instance;
	}
	
	/**
	 * Prevent cloning of the ACF_Address_Field_Helper object
	 * @internal
	 */
	private function __clone() {
	}
	
	/**
	 * WordPress Localization Text Domain
	 *
	 * Used in wordpress localization and translation methods.
	 * @var string
	 */
	const L10N_DOMAIN = 'acf-address-field';
	
	/**
	 * Language directory path
	 * 
	 * Used to build the path for WordPress localization files.
	 * @var string
	 */
	private $lang_dir;
	
	/**
	 * Constructor
	 */
	private function __construct() {
		$this->lang_dir = rtrim( dirname( realpath( __FILE__ ) ), '/' ) . '/languages';
		
		add_action( 'init', array( &$this, 'register_address_field' ), 5, 0 );
		add_action( 'init', array( &$this, 'load_textdomain' ),        2, 0 );
	}
	
	/**
	 * Registers the Address Field with Advanced Custom Fields
	 */
	public function register_address_field() {
		if( function_exists( 'register_field' ) ) {
			register_field( 'ACF_Address_Field', __FILE__ );
		}
	}
	
	/**
	* Loads the textdomain for the current locale if it exists
	*/
	public function load_textdomain() {
		$locale = get_locale();
		$mofile = $this->lang_dir . '/' . self::L10N_DOMAIN . '-' . $locale . '.mo';
		load_textdomain( self::L10N_DOMAIN, $mofile );
	}
}
endif; //class_exists 'ACF_Address_Field_Helper'

//Instantiate the Addon Helper class
ACF_Address_Field_Helper::singleton();
