<?php
/**
 * Form validator class.
 * 
 * Validate form input with some rules.
 * 
 * @package ThemifyFlow
 * @since 1.0.0
 */
class TF_Validator {
	
	/**
	 * Handle error messages data.
	 * 
	 * @since 1.0.0
	 * @access protected
	 * @var array $errors
	 */
	protected $errors = array();
	
	/**
	 * Constructor.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param array $inputs 
	 * @param array $rules 
	 */	
	public function __construct( $inputs, $rules ) {

		foreach( $rules as $field => $option ) {
			$input = isset( $inputs[ $field ] ) ? $inputs[ $field ] : '';

			if ( method_exists( $this, $option['rule'] ) && call_user_func_array( array( $this, $option['rule'] ), array( $input ) ) ) {
				$this->errors[ $field ] = $option['error_msg'];
			}
		}
	}

	/**
	 * Check if validator failed.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @return boolean
	 */
	public function fails() {
		if ( count( $this->errors ) > 0 ) 
			return true;
		return false;
	}

	/**
	 * Get error messages.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @return array
	 */
	public function get_error_messages() {
		return $this->errors;
	}

	/**
	 * Check input empty.
	 * 
	 * @since 1.0.0
	 * @access private
	 * @param string $input 
	 * @return boolean
	 */
	private function notEmpty( $input ) {
		return ! empty( $input ) ? false : true;
	}

	/**
	 * TODO LATER:
	 * - Add more validation function below
	 */
}