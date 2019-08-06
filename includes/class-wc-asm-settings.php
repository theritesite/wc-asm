<?php
/**
 * WooCommerce Advanced Shipping Methods Settings.
 *
 * @since   1.0.0
 * @package WC_ASM
 */



/**
 * WooCommerce Advanced Shipping Methods Settings class.
 *
 * @since 1.0.0
 */
class WC_ASM_Settings {
	/**
	 * Parent plugin class.
	 *
	 * @var    WC_ASM
	 * @since  1.0.0
	 */
	protected $plugin = null;

	/**
	 * Option key, and option page slug.
	 *
	 * @var    string
	 * @since  1.0.0
	 */
	protected static $key = 'wc_asm_settings'; 

	/**
	 * Options page metabox ID.
	 *
	 * @var    string
	 * @since  1.0.0
	 */
	protected static $metabox_id = 'wc_asm_settings_metabox';

	/**
	 * Options Page title.
	 *
	 * @var    string
	 * @since  1.0.0
	 */
	protected $title = '';

	/**
	 * Options Page hook.
	 *
	 * @var string
	 */
	protected $options_page = '';

	/**
	 * Constructor.
	 *
	 * @since  1.0.0
	 *
	 * @param  WC_ASM $plugin Main plugin object.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
		$this->hooks();

		// Set our title.
		$this->title = esc_attr__( 'The Rite Plugins Settings', 'wc-asm' );
	}

	/**
	 * Initiate our hooks.
	 *
	 * @since  1.0.0
	 */
	public function hooks() {

		// Hook in our actions to the admin.
		
		add_action( 'cmb2_admin_init', array( $this, 'add_options_page_metabox' ) );
		
		// Activate plugin license logic
		add_action( 'admin_init', array( $this, 'maybe_activate_wc_asm_license' ) );
		
		// Deactivate plugin license logic
		add_action( 'admin_init', array( $this, 'maybe_deactivate_wc_asm_license' ) );
		
		// Display admin notices for license screen
		add_action( 'admin_notices', array( $this, 'handle_admin_notices' ) );

	}

	/**
	 * Add custom fields to the options page.
	 *
	 * @since  1.2.0
	 */
	public function add_options_page_metabox() {

		$args = array(
			'id'           => 'the_rite_plugins_settings_page',
			'title'        => 'The Rite Plugins Settings',
			'object_types' => array( 'options-page' ),
			'option_key'   => 'the_rite_plugins_settings',
			'tab_group'    => 'the_rite_plugins_settings',
			'parent_slug'  => 'options-general.php', // Make options page a submenu item of the themes menu.
			'tab_title'    => 'Licenses',
		);
		// 'tab_group' property is supported in > 2.4.0.
		if ( version_compare( CMB2_VERSION, '2.4.0' ) ) {
			$args['display_cb'] = array( $this, 'wc_asm_options_display_with_tabs' );
		}
		$main_options = new_cmb2_box( $args );
		/**
		 * Options fields ids only need
		 * to be unique within this box.
		 * Prefix is not needed.
		 */
		$main_options->add_field( array(
			'name'    => __( 'WooCommerce Advanced Shipping Methods License Key', 'wc-asm' ),
			'desc'    => __( 'Enter your license key', 'wc-asm' ),
			'id'      => WC_ASM_LICENSE_KEY, // No prefix needed.
			'type'    => 'text',
			'default' => '',
			'after'   => array( $this, 'add_trp_activate_button'),
		) );
		
		/**
		 * Registers secondary options page, and set main item as parent.
		 * TODO: Add new settings tab for Net Profit specific settings
		 */
		/*$args = array(
			'id'           => 'trp_net_profit_options_page',
			'menu_title'   => 'Net Profit Options', // Use menu title, & not title to hide main h2.
			'object_types' => array( 'options-page' ),
			'option_key'   => 'trp_net_profit_options',
			'parent_slug'  => 'the_rite_plugins_settings',
			'tab_group'    => 'the_rite_plugins_settings',
			'tab_title'    => 'Net Profit',
		);
		// 'tab_group' property is supported in > 2.4.0.
		if ( version_compare( CMB2_VERSION, '2.4.0' ) ) {
			$args['display_cb'] = array( $this, 'wc_asm_options_display_with_tabs' );
		}
		$quad_options = new_cmb2_box( $args );
		$quad_options->add_field( array(
			'name'    => 'Test Radio',
			'desc'    => 'field description (optional)',
			'id'      => 'radio3',
			'type'    => 'radio',
			'options' => array(
				'option1' => 'Option One',
				'option2' => 'Option Two',
				'option3' => 'Option Three',
			),
		) );*/

	}

	/**
	 * Renders an html button that runs a javascript action to activate the license
	 * 
	 * @since 1.2.0
	 */
	public function add_trp_activate_button() {
		if( !is_admin() )
			return;
		$license_status = get_option( WC_ASM_LICENSE_STATUS );
		if( $license_status !== 'valid' ) {
			echo wp_nonce_field( 'trs_activate_wc_asm', 'wc_asm_nonce' );
			echo '<input type="submit" class="button-secondary" id="wc_asm_activate" name="wc_asm_activate" value="Activate" />';
		}
		else {
			echo wp_nonce_field( 'trs_deactivate_wc_asm', 'wc_asm_nonce' );
			echo '<input type="submit" class="button-secondary" id="wc_asm_deactivate" name="wc_asm_deactivate" value="Deactivate" />';
			
		}
	}

	/**
	 * Catches activation button press and attempts to activate the license for this plugin.
	 * 
	 * @since 1.2.0
	 */
	public function maybe_activate_wc_asm_license() {
		
		if( isset($_POST['wc_asm_activate']) ) {
			
			if( ! check_admin_referer( 'trs_activate_wc_asm', 'wc_asm_nonce' ) )
				return;
			
			$license = $this->get_value( WC_ASM_LICENSE_KEY );

			if( isset($_POST[WC_ASM_LICENSE_KEY]) && ($license != $_POST[WC_ASM_LICENSE_KEY]) ){
				$license = $_POST[WC_ASM_LICENSE_KEY];

				// Saves license value to metabox if activate is pressed
				$options = $this->get_value('all');
				$options[WC_ASM_LICENSE_KEY] = $license;
				update_option( 'the_rite_plugins_settings', $options );
			}

			// wp_die( $license );
			
			$api_params = array(
				'edd_action' => 'activate_license',
				'license'    => $license,
				'item_name'  => urlencode( WC_ASM_ITEM_NAME ), // the name of our product in EDD
				'url'        => home_url()
			);
			
			
			// Call the custom API.
			$response = wp_remote_post( WC_ASM_UPDATER_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );
	
			// make sure the response came back okay
			if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
	
				if ( is_wp_error( $response ) ) {
					$message = $response->get_error_message();
				} else {
					$message = __( 'An error occurred, please try again.' );
				}
	
			} else {
	
				$license_data = json_decode( wp_remote_retrieve_body( $response ) );
	
				if ( false === $license_data->success ) {
	
					switch( $license_data->error ) {
	
						case 'expired' :
	
							$message = sprintf(
								__( 'Your license key expired on %s.' ),
								date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) )
							);
							break;
	
						case 'disabled' :
						case 'revoked' :
	
							$message = __( 'Your license key has been disabled.' );
							break;
	
						case 'missing' :
	
							$message = __( 'Invalid license.' );
							break;
	
						case 'invalid' :
						case 'site_inactive' :
	
							$message = __( 'Your license is not active for this URL.' );
							break;
	
						case 'item_name_mismatch' :
	
							$message = sprintf( __( 'This appears to be an invalid license key for %s.' ), WC_ASM_ITEM_NAME );
							break;
	
						case 'no_activations_left':
	
							$message = __( 'Your license key has reached its activation limit.' );
							break;
	
						default :
	
							$message = __( 'An error occurred, please try again.' );
							break;
					}
	
				}
	
			}
	
			// Check if anything passed on a message constituting a failure
			if ( ! empty( $message ) ) {
				$base_url = admin_url( 'options-general.php?page=' . WC_ASM_LICENSE_PAGE );
				$redirect = add_query_arg( array( 'wc_asm_activation' => 'false', 'message' => urlencode( $message ) ), $base_url );
	
				wp_redirect( $redirect );
				exit();
			}
	
			// $license_data->license will be either "valid" or "invalid"
	
			update_option( WC_ASM_LICENSE_STATUS, $license_data->license );
			wp_redirect( admin_url( 'options-general.php?page=' . WC_ASM_LICENSE_PAGE ) );
			exit();
		}
	}

	/**
	 * Deactivates the plugin license
	 * 
	 * @since 1.2.0
	 */
	public function maybe_deactivate_wc_asm_license() {
		if( isset($_POST['wc_asm_deactivate']) ) {
			
			if( ! check_admin_referer( 'trs_deactivate_wc_asm', 'wc_asm_nonce' ) )
				return;
			
			$license = $this->get_value( WC_ASM_LICENSE_KEY );
			
			$api_params = array(
				'edd_action' => 'deactivate_license',
				'license'    => $license,
				'item_name'  => urlencode( WC_ASM_ITEM_NAME ), // the name of our product in EDD
				'url'        => home_url()
			);
			
			
			// Call the custom API.
			$response = wp_remote_post( WC_ASM_UPDATER_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );
	
			// make sure the response came back okay
			if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
	
				if ( is_wp_error( $response ) ) {
					$message = $response->get_error_message();
				} else {
					$message = __( 'An error occurred, please try again.' );
				}
				
				$base_url = admin_url( 'options-general.php?page=' . WC_ASM_LICENSE_PAGE );
				$redirect = add_query_arg( array( 'wc_asm_activation' => 'false', 'message' => urlencode( $message ) ), $base_url );
	
				wp_redirect( $redirect );
				exit();
			}
			
			// decode the license data
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );
	
			if( $license_data->license == 'failed' ) {
				$message = __( 'An error occurred, that license does not seem valid, please try again.' );
				
				$base_url = admin_url( 'options-general.php?page=' . WC_ASM_LICENSE_PAGE );
				$redirect = add_query_arg( array( 'wc_asm_activation' => 'false', 'message' => urlencode( $message ) ), $base_url );
	
				wp_redirect( $redirect );
				exit();
			}

			// $license_data->license will be either "deactivated" or "failed"
			if( $license_data->license == 'deactivated' ) {
				delete_option( WC_ASM_LICENSE_STATUS );
			}
	
			wp_redirect( admin_url( 'options-general.php?page=' . WC_ASM_LICENSE_PAGE ) );
			exit();
		}
	}

	/**
	 * Catch and display admin error messages specifically for plugin licensing
	 * 
	 * @since 1.0.0
	 */
	public function handle_admin_notices() {
		if ( isset( $_GET['wc_asm_activation'] ) && ! empty( $_GET['message'] ) ) {

			switch( $_GET['wc_asm_activation'] ) {
	
				case 'false':
					$message = urldecode( $_GET['message'] );
					?>
					<div class="error">
						<p><?php echo $message; ?></p>
					</div>
					<?php
					break;
	
				case 'true':
					
				default:
					// Developers can put a custom success message here for when activation is successful if they way.
					break;
	
			}
			remove_query_arg( 'wc_asm_activation' );
		}
	}

	/**
	 * Initiates tab driven settings page
	 * 
	 * @since 1.3.3
	 */
	public function wc_asm_options_display_with_tabs( $cmb_options ) {
		$tabs = $this->wc_asm_options_page_tabs( $cmb_options );
		?>
		<div class="wrap cmb2-options-page option-<?php echo $cmb_options->option_key; ?>">
			<?php if ( get_admin_page_title() ) : ?>
				<h2><?php echo wp_kses_post( get_admin_page_title() ); ?></h2>
			<?php endif; ?>
			<h2 class="nav-tab-wrapper">
				<?php foreach ( $tabs as $option_key => $tab_title ) : ?>
					<a class="nav-tab<?php if ( isset( $_GET['page'] ) && $option_key === $_GET['page'] ) : ?> nav-tab-active<?php endif; ?>" href="<?php menu_page_url( $option_key ); ?>"><?php echo wp_kses_post( $tab_title ); ?></a>
				<?php endforeach; ?>
			</h2>
			<form class="cmb-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="POST" id="<?php echo $cmb_options->cmb->cmb_id; ?>" enctype="multipart/form-data" encoding="multipart/form-data">
				<input type="hidden" name="action" value="<?php echo esc_attr( $cmb_options->option_key ); ?>">
				<?php $cmb_options->options_page_metabox(); ?>
				<?php submit_button( esc_attr( $cmb_options->cmb->prop( 'save_button' ) ), 'primary', 'submit-cmb' ); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Helper function to get tabs for settings tabular view
	 * 
	 * @since 1.2.0
	 */
	public function wc_asm_options_page_tabs( $cmb_options ) {
		$tab_group = $cmb_options->cmb->prop( 'tab_group' );
		$tabs      = array();
		foreach ( CMB2_Boxes::get_all() as $cmb_id => $cmb ) {
			if ( $tab_group === $cmb->prop( 'tab_group' ) ) {
				$tabs[ $cmb->options_page_keys()[0] ] = $cmb->prop( 'tab_title' )
					? $cmb->prop( 'tab_title' )
					: $cmb->prop( 'title' );
			}
		}
		return $tabs;
	}

	/**
	 * Wrapper function around cmb2_get_option.
	 *
	 * @since  1.2.0
	 *
	 * @param  string $key     Options array key
	 * @param  mixed  $default Optional default value
	 * @return mixed           Option value
	 */
	public static function get_value( $key = '', $default = false ) {
		if ( function_exists( 'cmb2_get_option' ) ) {

			// Use cmb2_get_option as it passes through some key filters.
			return cmb2_get_option( 'the_rite_plugins_settings', $key, $default );
		}

		// Fallback to get_option if CMB2 is not loaded yet.
		$opts = get_option( 'the_rite_plugins_settings', $default );

		$val = $default;

		if ( 'all' == $key ) {
			$val = $opts;
		} elseif ( is_array( $opts ) && array_key_exists( $key, $opts ) && false !== $opts[ $key ] ) {
			$val = $opts[ $key ];
		}

		return $val;
	}
}
