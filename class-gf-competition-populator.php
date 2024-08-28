<?php

GFForms::include_addon_framework();

/**
 * Gravity Forms Competition Populator.
 *
 * @since     1.0.0
 * @author    Travis Lopes Rev by WWE
 * @copyright Copyright (c) 2016, Travis Lopes Rev WWE
 */
class GF_Competition_Populator extends GFAddOn {

	/**
	 * Defines the version of Gravity Forms Competition Populator.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    string $_version Contains the version, defined from competitionPopulator.php
	 */
	protected $_version = 'GF_COMPETITION_POPULATOR_VERSION';

	/**
	 * Defines the minimum Gravity Forms version required.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    string $_min_gravityforms_version The minimum version required.
	 */
	protected $_min_gravityforms_version = '1.9.13';

	/**
	 * Defines the plugin slug.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    string $_slug The slug used for this plugin.
	 */
	protected $_slug = 'gravity-forms-competition-populator';

	/**
	 * Defines the main plugin file.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    string $_path The path to the main plugin file, relative to the plugins folder.
	 */
	protected $_path = 'gravity-forms-competition-populator/competition-populator.php';

	/**
	 * Defines the full path to this class file.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    string $_full_path The full path.
	 */
	protected $_full_path = __FILE__;

	/**
	 * Defines the URL where this Add-On can be found.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    string The URL of the Add-On.
	 */
	protected $_url = 'http://travislop.es/plugins/gravity-forms-competition-populator';

	/**
	 * Defines the title of this Add-On.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    string $_title The title of the Add-On.
	 */
	protected $_title = 'Gravity Forms Competition Populator';

	/**
	 * Defines the short title of the Add-On.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    string $_short_title The short title.
	 */
	protected $_short_title = 'Competition Populator';

	/**
	 * Contains an instance of this class, if available.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    object $_instance If available, contains an instance of this class.
	 */
	private static $_instance = null;

	/**
	 * Defines the capability needed to access the Add-On settings page.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    string $_capabilities_settings_page The capability needed to access the Add-On settings page.
	 */
	protected $_capabilities_settings_page = 'forgravity_competition_populator';

	/**
	 * Defines the capability needed to access the Add-On form settings page.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    string $_capabilities_form_settings The capability needed to access the Add-On form settings page.
	 */
	protected $_capabilities_form_settings = 'forgravity_competition_populator';

	/**
	 * Defines the capability needed to uninstall the Add-On.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    string $_capabilities_uninstall The capability needed to uninstall the Add-On.
	 */
	protected $_capabilities_uninstall = 'forgravity_competition_populator_uninstall';

	/**
	 * Defines the capabilities needed for Competition Populator.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    array $_capabilities The capabilities needed for the Add-On
	 */
	protected $_capabilities = array( 'forgravity_competition_populator', 'forgravity_competition_populator_uninstall' );

	/**
	 * Get instance of this class.
	 *
	 * @since  1.0
	 * @access public
	 * @static
	 *
	 * @return $_instance
	 */
	public static function get_instance() {

		if ( null === self::$_instance ) {
			self::$_instance = new self;
		}

		return self::$_instance;

	}

	/**
	 * Register needed pre-initialization hooks.
	 *
	 * @since  2.0
	 * @access public
	 */
	public function pre_init() {

		parent::pre_init();

		/**
		 * Define recurrence for Competition Populator cron event.
		 *
		 * @param string $recurrence How often Competition Populator cron event should run.
		 */
		$recurrence = apply_filters( 'gf_competition_populator_recurrence', 'twicedaily' );

		if ( ! wp_next_scheduled( 'gf_competition_populator_maybe_expire' ) ) {
			$scheduled = wp_schedule_event( strtotime( 'midnight + 5 minutes'), $recurrence, 'gf_competition_populator_maybe_expire' );
		}

		add_action( 'gf_competition_populator_maybe_expire', array( $this, 'maybe_run_Populator' ) );

	}

	/**
	 * Enqueue needed stylesheets.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @return array
	 */
	public function styles() {

		$styles = array(
			array(
				'handle'  => $this->_slug . '_form_settings',
				'src'     => $this->get_base_url() . '/css/form_settings.css',
				'version' => $this->_version,
				'enqueue' => array( array( 'admin_page' => array( 'form_settings' ) ) ),
			),
		);

		return array_merge( parent::styles(), $styles );

	}





	// # UNINSTALL -----------------------------------------------------------------------------------------------------

	/**
	 * Remove cron event.
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @param array $schedules An array of non-default cron schedules.
	 *
	 * @return array
	 */
	public function uninstall( $schedules = array() ) {

		wp_clear_scheduled_hook( 'gf_competition_populator_maybe_expire' );

	}





	// # FORM SETTINGS -------------------------------------------------------------------------------------------------

	/**
	 * Setup fields for form settings.
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @param array $form The current form object.
	 *
	 * @return array
	 */
	public function form_settings_fields( $form ) {
		
		$uploads = wp_upload_dir();
		$uploads_dir = $uploads['basedir'] ;

		return array(
			array(
				'fields' => array(
					array(
						'name'       => 'PopulationEnable',
						'label'      => esc_html__( 'Enable Populator', 'gravity-forms-competition-populator' ),
						'type'       => 'checkbox',
						'onclick'    => "jQuery( this ).parents( 'form' ).submit()",
						'choices'    => array(
							array(
								'name'  => 'PopulationEnable',
								'label' => esc_html__( 'Automatically populate competition entries on a defined schedule', 'gravity-forms-competition-populator' ),
							),
						),
					),
											
										
					array(
						'name'       => 'PopulateStart',
						'label'      => esc_html__( 'Populate competitions starting x days in advance (default 7 days)', 'gravity-forms-competition-populator' ),
						'type'       => 'text_select',
						'required'   => true,
						'dependency' => array( 'field' => 'PopulationEnable', 'values' => array( '1' ) ),
						'text'       => array(
							'name'        => 'startoffset[number]',
							'class'       => 'small',
							'input_type'  => 'number',
							'default_value' => '7',
							'after_input' => ' ',
						),
						'select'     => array(
							'name'    => 'startoffset[unit]',
							'choices' => array(
								array( 'value' => 'days',  'label' => esc_html__( 'days', 'gravity-forms-competition-populator' ) ),
								array( 'value' => 'weeks',  'label' => esc_html__( 'weeks', 'gravity-forms-competition-populator' ) ),
								array( 'value' => 'months', 'label' => esc_html__( 'months', 'gravity-forms-competition-populator' ) ),
							),
						),
					),
					
					array(
						'name'       => 'PopulateEnd',
						'label'      => esc_html__( 'Populate competitions Ending x days in advance (default 60 days)', 'gravity-forms-competition-populator' ),
						'type'       => 'text_select',
						'required'   => true,
						'dependency' => array( 'field' => 'PopulationEnable', 'values' => array( '1' ) ),
						'text'       => array(
							'name'        => 'endoffset[number]',
							'class'       => 'small',
							'input_type'  => 'number',
							'default_value' => '60',
							'after_input' => ' ',
						),
						'select'     => array(
							'name'    => 'endoffset[unit]',
							'choices' => array(
								array( 'value' => 'days',  'label' => esc_html__( 'days', 'gravity-forms-competition-populator' ) ),
								array( 'value' => 'weeks',  'label' => esc_html__( 'weeks', 'gravity-forms-competition-populator' ) ),
								array( 'value' => 'months', 'label' => esc_html__( 'months', 'gravity-forms-competition-populator' ) ),
							),
						),
					),
					
					array(
						'name'       => 'Populate_csv_file',
						'label'      => esc_html__( 'Base directory => '. $uploads_dir, 'gravity-forms-competition-populator' ),
						'type'       => 'text',
						'required'   => true,
						'dependency' => array( 'field' => 'PopulationEnable', 'values' => array( '1' ) ),
						'text'       => array(
							'name'        => 'csv_input_file',
							'class'       => 'large',
							'input_type'  => 'text',
							'tooltip'     => esc_html__( 'The name of the file with competition information','gravity-forms-competition-populator' ),
							'default_value' => $uploads_dir,
							'after_input' => ' ',
						),
					),			
			
					
					array(
						'name'       => 'PopulationRunTime',
						'label'      => esc_html__( 'Run population every (default 1 day)', 'gravity-forms-competition-populator' ),
						'type'       => 'text_select',
						'required'   => true,
						'dependency' => array( 'field' => 'PopulationEnable', 'values' => array( '1' ) ),
						'text'       => array(
							'name'        => 'PopulationRunTime[number]',
							'class'       => 'small',
							'input_type'  => 'number',
							'default_value' => '1',
							'after_input' => ' ',
						),
						'select'     => array(
							'name'    => 'PopulationRunTime[unit]',
							'choices' => array(
								array( 'value' => 'days',  'label' => esc_html__( 'days', 'gravity-forms-competition-populator' ) ),
								array( 'value' => 'weeks',  'label' => esc_html__( 'weeks', 'gravity-forms-competition-populator' ) ),
								array( 'value' => 'months', 'label' => esc_html__( 'months', 'gravity-forms-competition-populator' ) ),
							),
						),
					),
				),
			),
			array(
				'fields' => array(
					array(
						'type'  => 'save',
						'messages' => array(
							'error'   => esc_html__( 'There was an error while saving the Competition Populator settings. Please review the errors below and try again.', 'gravity-forms-competition-populator' ),
							'success' => esc_html__( 'Competition Populator settings updated.', 'gravity-forms-competition-populator' ),
						),
					),
				),
			),
		);

	}

	/**
	 * Render a select settings field.
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @param array $field Field settings.
	 * @param bool  $echo  Display field. Defaults to true.
	 *
	 * @uses GFAddOn::field_failed_validation()
	 * @uses GFAddOn::get_error_icon()
	 * @uses GFAddOn::get_field_attributes()
	 * @uses GFAddOn::get_select_options()
	 * @uses GFAddOn::get_setting()
	 *
	 * @return string
	 */
	public function settings_select( $field, $echo = true ) {

		// Get after select value.
		$after_select = rgar( $field, 'after_select' );

		// Remove after select property.
		unset( $field['after_select'] );

		// Get select field markup.
		$html = parent::settings_select( $field, false );

		// Add after select.
		if ( ! rgblank( $after_select ) ) {
			$html .= ' ' . $after_select;
		}

		if ( $echo ) {
			echo $html;
		}

		return $html;

	}

	/**
	 * Render a text and select settings field.
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @param array $field Field settings.
	 * @param bool  $echo  Display field. Defaults to true.
	 *
	 * @return string
	 */
	public function settings_text_select( $field, $echo = true ) {

		// Initialize return HTML.
		$html = '';

		// Duplicate fields.
		$select_field = $text_field = $field;

		// Merge properties.
		$text_field   = array_merge( $text_field, $text_field['text'] );
		$select_field = array_merge( $select_field, $select_field['select'] );

		unset( $text_field['text'], $select_field['text'], $text_field['select'], $select_field['select'] );

		$html .= $this->settings_text( $text_field, false );
		$html .= $this->settings_select( $select_field, false );

		if ( $this->field_failed_validation( $field ) ) {
			$html .= $this->get_error_icon( $field );
		}

		if ( $echo ) {
			echo $html;
		}

		return $html;

	}

	/**
	 * Validates a text and select settings field.
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @param array $field    Field settings.
	 * @param array $settings Submitted settings values.
	 */
	public function validate_text_select_settings( $field, $settings ) {

		// Convert text field name.
		$text_field_name = str_replace( array( '[', ']' ), array( '/', '' ), $field['text']['name'] );

		// Get text field value.
		$text_field_value = rgars( $settings, $text_field_name );

		// If text field is empty and field is required, set error.
		if ( rgblank( $text_field_value ) && rgar( $field, 'required' ) ) {
			$this->set_field_error( $field, esc_html__( 'This field is required.', 'gravity-forms-competition-populator' ) );
			return;
		}

		// If text field is not numeric, set error.
		if ( ! rgblank( $text_field_value ) && ! ctype_digit( $text_field_value ) ) {
			$this->set_field_error( $field, esc_html__( 'You must use a whole number.', 'gravity-forms-competition-populator' ) );
			return;
		}

	}

	/**
	 * Define the title for the form settings page.
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @return string
	 */
	public function form_settings_page_title() {

		return esc_html__( 'Competition Populator Settings', 'gravity-forms-competition-populator' );

	}





	// # COMPETITION POPULATOR ----------------------------------------------------------------------------------------------

	/**
	 * Run Competition Populator on forms that pass populator conditions.
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @uses GFAPI::get_forms()
	 * @uses GF_Competition_Populator::maybe_run_populator()
	 */
	public function maybe_run_populator() {

		// Get forms.
		$forms = GFAPI::get_forms();

		// Loop through forms.
		foreach ( $forms as $form ) {

			// Get Competition Populator settings.
			$settings = rgar( $form, $this->_slug );

			// If populator is enabled, run populator.
			if ( rgar( $settings, 'PopulationEnable' ) ) {
				$this->populate_forms( $form, $settings );
			}

		}

	}

	/**
	 * Populate if form pass conditions.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param array $form     The form object.
	 * @param array $settings Competition Populator settings.
	 *
	 * @uses GFAPI::count_entries()
	 * @uses GF_Competition_Populator::populate_forms())

	 */
	public function populate_forms( $form, $settings ) {

		// Get Competition Populator transient for population.
		$transient_exists = get_transient( $this->_slug . '_' . $form['id'] );

		// If transient exists, skip form. (comment out for testing)
//		if ( '1' === $transient_exists ) {
//			$this->log_debug( __METHOD__ . '(): Skipping population for form #' . $form['id'] . ' because it is not due to be run yet.' );
//		return;
//		}

		// Define next run time.
		$next_run_time = $this->prepare_next_run_time( $settings );


		// Populate form entries.
		$this->populate_comps ($form, $settings);

		// Set transient.
		set_transient( $this->_slug . '_' . $form['id'], '1', $next_run_time );

	}



	/**
	 * Prepare the next time Competition Populator should run.
	 *
	 * @since  2.0.3
	 * @access public
	 *
	 * @param array $settings Competition Populator settings.
	 *
	 * @return int
	 */
	public function prepare_next_run_time( $settings ) {

		// Get run time number.
		$number = $settings['PopulationRunTime']['number'];

		// Prepare run time based on unit.
		switch ( $settings['PopulationRunTime']['unit'] ) {

			case 'days':
				$next_run_time = $number * DAY_IN_SECONDS;
				break;

			case 'months':
				$next_run_time = $number * MONTH_IN_SECONDS;
				break;

			case 'weeks':
				$next_run_time = $number * WEEK_IN_SECONDS;
				break;

		}

		// Adjust run time by five seconds.
		$next_run_time -= 5;

		return $next_run_time;

	}
	
	public function get_start_date($settings) {

		// Get start date
		$number = $settings['startoffset']['number'];
		$unit = $settings['startoffset']['unit'];

		$start_date = "-" . $number . " " . $unit ;
				
		return strtotime($start_date);

	}
	
	public function get_end_date($settings) {

		// Get end date
		$number = $settings['endoffset']['number'];
		$unit = $settings['endoffset']['unit'];

		$end_date = "-" . $number . " " . $unit ;
				
		return strtotime($end_date);

	}
	
	public function populate_comps( $form, $settings ) {	

		
		$file = $settings['csv_import_file'];
		$array = parse_csv_file($file);
		$form_id = $form['gravity-forms-competition-populator']['form_id'];
		

			
		//Creating a choices array.    
		$choices = array();
	   
		//Adding competitions to the comps array
		foreach($array as $item) {
			$comp_date = $item['CompDate'];
			$competition = $item['Competition'];
			
			$start_date = strtotime( $form['gravity-forms-competition-populator']['start_date']);
			$end_date = strtotime( $form['gravity-forms-competition-populator']['end_date']);
									  
			if (date_check2($comp_date,$start_date,$end_date)){
				$choices[] = array( 'text' => $competition , 'value' => $competition  );
			}
		}
		
		$field_id = $form['gravity-forms-competition-populator']['populate_field'] ;
	//	if ($field_id == "") {
	//	   $field_id = 1;
	//   }
		
		//Adding competitions to field id 
		foreach ( $form['fields'] as &$field ) {
			if ( $field->id == $field_id ) {
				$field->choices = $choices;
			}
		} 
		
	return $form;		
	}


	public function date_check2($sub,$start_date,$end_date){
		
			
		if ($start_date == "") {
			$start_date = strtotime( '+ 7 days'); //Use default value if blank
		 }
		 
		 if ($end_date == "") {
			$end_date = strtotime( '+ 60 days'); //Use default value if blank
		 }
			 
		 $format = 'Y-m-d'; 
		 $eval_date = strtotime($sub);
		
		//echo "Comp Date: ". $sub . " " . $eval_date . " Start: ". $start_date. " End: " .  $end_date . "\n";
		
			if ($eval_date > $start_date && $eval_date  < $end_date) {
				
				$result = True;
				//echo "True";
			}
			else{
				//echo $eval_date . " is not less than " . $end_date . " " . "End Date is False";
				$result = False;
			}
							
			//echo "\n";
		Return $result;
	}

	public function parse_csv_file($csvfile) {
		$csv = Array();
		$rowcount = 0;
		if (($handle = fopen($csvfile, "r")) !== FALSE) {
			$max_line_length = defined('MAX_LINE_LENGTH') ? MAX_LINE_LENGTH : 10000;
			$header = fgetcsv($handle, $max_line_length);
			$header_colcount = count($header);
			while (($row = fgetcsv($handle, $max_line_length)) !== FALSE) {
				$row_colcount = count($row);
				if ($row_colcount == $header_colcount) {
					$entry = array_combine($header, $row);
					$csv[] = $entry;
				}
				else {
					error_log("csvreader: Invalid number of columns at line " . ($rowcount + 2) . " (row " . ($rowcount + 1) . "). Expected=$header_colcount Got=$row_colcount");
					return null;
				}
				$rowcount++;
			}
			//echo "Totally $rowcount rows found\n";
			fclose($handle);
		}
		else {
			error_log("csvreader: Could not read CSV \"$csvfile\"");
			return null;
		}
		return $csv;
	}


}

