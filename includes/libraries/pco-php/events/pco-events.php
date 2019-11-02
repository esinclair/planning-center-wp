<?php 

/**
* Load the base class for the PCO PHP API
* // https://api.planningcenteronline.com/groups/v2/events

*/
class PCO_PHP_Events {

	protected $method;
	protected $parameters;

	function __construct( $args )	{
		
		$options = get_option('planning_center_wp');

		$this->method = $args['method'];
		$this->parameters = $args['parameters'];

	}

	public function lists() 
	{
		if ( $this->parameters ) {
			$this->parameters = $this->format_parameters( $this->parameters );
		}

		$base_url = 'https://api.planningcenteronline.com/groups/v2/events';

		return $base_url . '?' . $this->parameters;
	}

	public function events()
	{	

		if ( $this->parameters ) {
			$this->parameters = $this->format_parameters( $this->parameters );
		}
		
		$base_url = 'https://api.planningcenteronline.com/groups/v2/events';

		return $base_url; // . '?' . $this->parameters;

	}

	public function format_parameters( $parameters )
	{
		
		$params = array();
		$string = '';

		switch ($this->method) {
//			case 'lists':
//				$keys = array( 'name', 'batch_completed_at', 'created_at', 'updated_at' );
//				break;
			case 'events':
				$keys = array(
				);
			default:
				$keys = array();
				break;
		}

		

		$items = explode( ',', $parameters );

		foreach( $items as $item ) {
			$params[] = explode(':', $item );
		}

		foreach( $params as $param ) {
			
			$parameter = $param[0];
			$value = $param[1];
			
			if ( in_array( $parameter, $keys ) ) {
				$string .= 'where[' . $parameter . ']=' . $value . '&';
			}
			
		}

		return $string;
	}
}