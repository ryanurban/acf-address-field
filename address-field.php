<?php
/*
 * Copyright (c) 2012, CAMPUS CRUSADE FOR CHRIST
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 * 
 *     Redistributions of source code must retain the above copyright notice, this
 *         list of conditions and the following disclaimer.
 *     Redistributions in binary form must reproduce the above copyright notice,
 *         this list of conditions and the following disclaimer in the documentation
 *         and/or other materials provided with the distribution.
 *     Neither the name of CAMPUS CRUSADE FOR CHRIST nor the names of its
 *         contributors may be used to endorse or promote products derived from this
 *         software without specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 * IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT,
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE
 * OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED
 * OF THE POSSIBILITY OF SUCH DAMAGE.
 */
?>
<?php

if( !class_exists( 'ACF_Address_Field' ) && class_exists( 'acf_Field' ) ) :

/**
 * Global ConneXion - Advanced Custom Fields - Address Field
 * 
 * This addon to Advanced Custom Fields adds the capability for
 * a multi-field address input. It has the ability to customize the
 * individual fields and the layout of the address block.
 * 
 * This addon is self loading, just include the address-field.php file
 * in your functions.php or plugin and it will register itself with
 * Advanced Custom Fields.
 * 
 * @author Brian Zoetewey <brian.zoetewey@ccci.org>
 * @version 1.0
 */
class ACF_Address_Field extends acf_Field {
	/**
	 * WordPress Localization Text Domain
	 * 
	 * Used in wordpress localization and translation methods.
	 * @var string
	 */
	const L10N_DOMAIN = 'acf-address-field';
	
	/**
	 * Base directory
	 * @var string
	 */
	private $base_dir;
	
	/**
	 * Relative Uri from the WordPress ABSPATH constant
	 * @var string
	 */
	private $base_uri_rel;
	
	/**
	 * Absolute Uri
	 * 
	 * This is used to create urls to CSS and JavaScript files.
	 * @var string
	 */
	private $base_uri_abs;
	
	/**
	 * Address Field Defaults
	 * 
	 * This variable is instantiated in the class constructor in
	 * order to make use of WordPress localization methods.
	 * @var array
	 */
	private $address_defaults; 
	
	/**
	 * Address Field Default Layout
	 * @var array
	 */
	private $address_default_layout = array(
		0 => array( 0 => 'address1' ),
		1 => array( 0 => 'address2' ),
		2 => array( 0 => 'address3' ),
		3 => array( 0 => 'city', 1 => 'state', 2 => 'postal_code', 3 => 'country' ),
	);
	
	/**
	 * Class Constructor - Instantiates a new Address Field
	 * @param Acf $parent Parent Acf class
	 */
	public function __construct( $parent ) {
		parent::__construct( $parent );
		
		$this->base_dir = rtrim( dirname( realpath( __FILE__ ) ), '/' );
		
		//Build the base relative uri by searching backwards until we encounter the wordpress ABSPATH
		$root = array_pop( explode( '/', rtrim( ABSPATH, '/' ) ) );
		$path_parts = explode( '/', $this->base_dir );
		$parts = array();
		while( $part = array_pop( $path_parts ) ) {
			if( $part == $root )
				break;
			array_unshift( $parts, $part );
		}
		$this->base_uri_rel = '/' . implode( '/', $parts );
		$this->base_uri_abs = get_site_url( null, $this->base_uri_rel );
		
		$this->name        = 'gcx_acf_address';
		$this->title       = __( 'Address', self::L10N_DOMAIN );
		
		$this->address_defaults = array(
			'address1'    => array(
				'label'         => __( 'Address 1', self::L10N_DOMAIN ),
				'default_value' => '',
				'enabled'       => 1,
				'class'         => 'address1',
				'separator'     => '',
			),
			'address2'    => array(
				'label'         => __( 'Address 2', self::L10N_DOMAIN ),
				'default_value' => '',
				'enabled'       => 1,
				'class'         => 'address2',
				'separator'     => '',
			),
			'address3'    => array(
				'label'         => __( 'Address 3', self::L10N_DOMAIN ),
				'default_value' => '',
				'enabled'       => 1,
				'class'         => 'address3',
				'separator'     => '',
			),
			'city'        => array(
				'label'         => __( 'City', self::L10N_DOMAIN ),
				'default_value' => '',
				'enabled'       => 1,
				'class'         => 'city',
				'separator'     => ',',
			),
			'state'       => array(
				'label'         => __( 'State', self::L10N_DOMAIN ),
				'default_value' => '',
				'enabled'       => 1,
				'class'         => 'state',
				'separator'     => '',
			),
			'postal_code' => array(
				'label'         => __( 'Postal Code', self::L10N_DOMAIN ),
				'default_value' => '',
				'enabled'       => 1,
				'class'         => 'postal_code',
				'separator'     => '',
			),
			'country'     => array(
				'label'         => __( 'Country', self::L10N_DOMAIN ),
				'default_value' => '',
				'enabled'       => 1,
				'class'         => 'country',
				'separator'     => '',
			),
		);
		
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

		wp_register_style( 'acf-address-field', $this->base_uri_abs . '/address-field.css' );
		
		if( in_array( $pagenow, array( 'post.php', 'post-new.php' ) ) ) {
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
		wp_register_script( 'acf-address-field', $this->base_uri_abs . '/address-field.js', array( 'jquery-ui-sortable' ) );
		
		if( in_array( $pagenow, array( 'post.php', 'post-new.php' ) ) ) {
			wp_enqueue_script( 'acf-address-field' );
		}
	}
	
	/**
	 * Creates the address field for inside post metaboxes
	 * 
	 * @see acf_Field::create_field()
	 */
	public function create_field( $field ) {
		$fields = ( array_key_exists( 'address_fields' , $field ) && is_array( $field[ 'address_fields' ] ) ) ?
			wp_parse_args( (array) $field[ 'address_fields' ], $this->address_defaults ) :
			$this->address_defaults;
		
		$layout = ( array_key_exists( 'address_layout', $field ) && is_array( $field[ 'address_layout' ] ) ) ?
			(array) $field[ 'address_layout' ] : $this->address_default_layout;

		$values = (array) $field[ 'value' ];

		?>
		<div class="address">
		<?php foreach( $layout as $layout_row ) : if( empty($layout_row) ) continue; ?>
			<div class="address_row">
			<?php foreach( $layout_row as $name ) : if( empty( $name ) || !$fields[ $name ][ 'enabled' ] ) continue; ?>
				<label class="<?php echo $fields[ $name ][ 'class' ]; ?>">
					<?php echo $fields[ $name ][ 'label' ]; ?>
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
		$fields = ( array_key_exists( 'address_fields' , $field ) && is_array( $field[ 'address_fields' ] ) ) ?
			wp_parse_args( (array) $field[ 'address_fields' ], $this->address_defaults ) :
			$this->address_defaults;
		
		$layout = ( array_key_exists( 'address_layout', $field ) && is_array( $field[ 'address_layout' ] ) ) ?
			(array) $field[ 'address_layout' ] : $this->address_default_layout;
		
		$missing = array_keys( $fields );
		
		?>
			<tr class="field_option field_option_<?php echo $this->name; ?>">
				<td class="label">
					<label><?php _e( 'Address Fields' , self::L10N_DOMAIN ); ?></label>
					<p class="description"><?php _e( 'Fields, labels and default values', self::L10N_DOMAIN ); ?></p>
				</td>
				<td>
					<table>
						<thead>
							<tr>
								<th><?php _e( 'Field', self::L10N_DOMAIN ); ?></th>
								<th><?php _e( 'Enabled', self::L10N_DOMAIN ); ?></th>
								<th><?php _e( 'Label', self::L10N_DOMAIN ); ?></th>
								<th><?php _e( 'Default Value', self::L10N_DOMAIN ); ?></th>
								<th><?php _e( 'CSS Class', self::L10N_DOMAIN ); ?></th>
								<th><?php _e( 'Separator', self::L10N_DOMAIN ); ?></th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<th><?php _e( 'Field', self::L10N_DOMAIN ); ?></th>
								<th><?php _e( 'Enabled', self::L10N_DOMAIN ); ?></th>
								<th><?php _e( 'Label', self::L10N_DOMAIN ); ?></th>
								<th><?php _e( 'Default Value', self::L10N_DOMAIN ); ?></th>
								<th><?php _e( 'CSS Class', self::L10N_DOMAIN ); ?></th>
								<th><?php _e( 'Separator', self::L10N_DOMAIN ); ?></th>
							</tr>
						</tfoot>
						<tbody>
							<?php foreach( $fields as $name => $settings ) : ?>
								<tr>
									<td><?php echo $name; ?></td>
									<td>
										<?php
											$this->parent->create_field( array(
												'type'  => 'true_false',
												'name'  => "fields[{$key}][address_fields][{$name}][enabled]",
												'value' => $settings[ 'enabled' ],
												'class' => 'address_enabled',
											) );
										?>
									</td>
									<td>
										<?php
											$this->parent->create_field( array(
												'type'  => 'text',
												'name'  => "fields[{$key}][address_fields][{$name}][label]",
												'value' => $settings[ 'label' ],
												'class' => 'address_label',
											) );
										?>
									</td>
									<td>
										<?php
											$this->parent->create_field( array(
												'type'  => 'text',
												'name'  => "fields[{$key}][address_fields][{$name}][default_value]",
												'value' => $settings[ 'default_value' ],
												'class' => 'address_default_value',
											) );
										?>
									</td>
									<td>
										<?php
											$this->parent->create_field( array(
												'type'  => 'text',
												'name'  => "fields[{$key}][address_fields][{$name}][class]",
												'value' => $settings[ 'class' ],
												'class' => 'address_class',
											) );
										?>
									</td>
									<td>
										<?php
											$this->parent->create_field( array(
												'type'  => 'text',
												'name'  => "fields[{$key}][address_fields][{$name}][separator]",
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
					<label><?php _e( 'Address Layout' , $this->l10n_domain ); ?></label>
					<p class="description"><?php _e( 'Drag address peices to the desired location. This controls the layout of the address in post metaboxes and the get_field() api method.', 'acf' ); ?></p>
					<input type="hidden" name="address_layout_key" value="<?php echo $key; ?>" />
				</td>
				<td>
					<div class="address_layout">
						<?php
							$row = 0;
							foreach( $layout as $layout_row ) :
								if( count( $layout_row ) <= 0 ) continue;
						?>
							<label><?php printf( __( 'Line %d:', self::L10N_DOMAIN ), $row + 1 ); ?></label>
							<ul class="row">
								<?php
									$col = 0;
									foreach( $layout_row as $name ) :
										if( empty( $name ) ) continue;
										if( !$fields[ $name ][ 'enabled' ] ) continue;
										
										if( ( $index = array_search( $name, $missing, true ) ) !== false )
											array_splice( $missing, $index, 1 );
								?>
									<li class="item" name="<?php echo $name; ?>">
										<?php echo $fields[ $name ][ 'label' ]; ?>
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
							<label><?php printf( __( 'Line %d:', self::L10N_DOMAIN ), $row + 1 ); ?></label>
							<ul class="row">
							</ul>
						<?php endfor; ?>
						<label><?php _e( 'Not Displayed:', self::L10N_DOMAIN ); ?></label>
						<ul class="row missing">
							<?php foreach( $missing as $name ) : ?>
								<li class="item <?php echo $fields[ $name ][ 'enabled' ] ? '' : 'disabled'; ?>" name="<?php echo $name; ?>">
									<?php echo $fields[ $name ][ 'label' ]; ?>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
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
		$fields = ( array_key_exists( 'address_fields' , $field ) && is_array( $field[ 'address_fields' ] ) ) ?
			wp_parse_args( (array) $field[ 'address_fields' ], $this->address_defaults ) :
			$this->address_defaults;
		
		$defaults = array();
		foreach( $fields as $name => $settings )
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
		$fields = ( array_key_exists( 'address_fields' , $field ) && is_array( $field[ 'address_fields' ] ) ) ?
			wp_parse_args( (array) $field[ 'address_fields' ], $this->address_defaults ) :
			$this->address_defaults;
		
		$layout = ( array_key_exists( 'address_layout', $field ) && is_array( $field[ 'address_layout' ] ) ) ?
			(array) $field[ 'address_layout' ] : $this->address_default_layout;
		
		$values = $this->get_value( $post_id, $field );
		
		$output = '';
		foreach( $layout as $layout_row ) {
			if( empty( $layout_row ) ) continue;
			$output .= '<div class="address_row">';
			foreach( $layout_row as $name ) {
				if( empty( $name ) || !$fields[ $name ][ 'enabled' ] ) continue;
					$output .= sprintf(
						'<span %2$s>%1$s%3$s </span>',
						$values[ $name ],
						$fields[ $name ][ 'class' ] ? 'class="' . esc_attr( $fields[ $name ][ 'class' ] ) . '"' : '',
						$fields[ $name ][ 'separator' ] ? esc_html( $fields[ $name ][ 'separator' ] ) : ''
					);
			}
			$output .= '</div>';
		}
		
		return $output;
	}
}

endif; //class_exists 'ACF_Address_Field'

if( !class_exists( 'ACF_Address_Field_Loader' ) ) :

/**
 * Global ConneXion - Advanced Custom Fields - Address Field Loader
 * 
 * This class is a singleton thats primary job is to register the Address Field
 * with Advanced Custom Fields. Developers using this field do not need to worry
 * about how to register it with Advanced Custom Fields. Simply include this 
 * php file and the ACF_Address_Field_Loader does the rest.
 * <code> include_once( rtrim( dirname( __FILE__ ), '/' ) . '/acf-address-field/address-field.php' ); </code>
 * 
 * @author Brian Zoetewey <brian.zoetewey@ccci.org>
 */
class ACF_Address_Field_Loader {
	/**
	 * Singleton instance
	 * @var ACF_Address_Field_Loader
	 */
	private static $instance;
	
	/**
	 * Returns the ACF_Address_Field_Loader singleton
	 * 
	 * <code>$obj = ACF_Address_Field_Loader::singleton();</code>
	 * @return ACF_Address_Field_Loader
	 */
	public static function singleton() {
		if( !isset( self::$instance ) ) {
			$class = __CLASS__;
			self::$instance = new $class();
		}
		return self::$instance;
	}
	
	/**
	 * Prevent cloning of the ACF_Address_Field_Loader object
	 * @internal
	 */
	private function __clone() {
	}
	
	/**
	 * Constructor
	 */
	private function __construct() {
		add_action( 'init', array( &$this, 'register_address_field' ), 5, 0 );
	}
	
	/**
	 * Registers the Address Field with Advanced Custom Fields
	 */
	public function register_address_field() {
		if( function_exists( 'register_field' ) ) {
			register_field( 'ACF_Address_Field', __FILE__ );
		}
	}
}
endif; //class_exists 'ACF_Address_Field_Loader'

//Instantiate the Addon Loader class
ACF_Address_Field_Loader::singleton();