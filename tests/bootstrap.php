<?php
/**
 * create-wordpress-plugin Test Bootstrap
 */

/**
 * Visit {@see https://mantle.alley.com/docs/testing} to learn more.
 */
\Mantle\Testing\manager()
	->maybe_rsync_plugin()
	->before(
		function() {
			// Load the testcases.
			require_once __DIR__ . '/testcases/class-test-controller.php';
		}
	)
	->loaded( fn () => require_once __DIR__ . '/../byline-manager.php' )
	->install();

// XML to Array from Core

// Convert valid XML to an array tree structure.
// Kinda lame, but it works with a default PHP 4 installation.
class TestXMLParser {
	public $xml;
	public $data = [];

	/**
	 * PHP5 constructor.
	 */
	public function __construct( $in ) {
		$this->xml = xml_parser_create();
		xml_set_object( $this->xml, $this );
		xml_parser_set_option( $this->xml, XML_OPTION_CASE_FOLDING, 0 );
		xml_set_element_handler( $this->xml, [ $this, 'start_handler' ], [ $this, 'end_handler' ] );
		xml_set_character_data_handler( $this->xml, [ $this, 'data_handler' ] );
		$this->parse( $in );
	}

	public function parse( $in ) {
		$parse = xml_parse( $this->xml, $in, true );
		if ( ! $parse ) {
			trigger_error(
				sprintf(
					'XML error: %s at line %d',
					xml_error_string( xml_get_error_code( $this->xml ) ),
					xml_get_current_line_number( $this->xml )
				),
				E_USER_ERROR
			);
			xml_parser_free( $this->xml );
		}
		return true;
	}

	public function start_handler( $parser, $name, $attributes ) {
		$data['name'] = $name;
		if ( $attributes ) {
			$data['attributes'] = $attributes; }
		$this->data[] = $data;
	}

	public function data_handler( $parser, $data ) {
		$index = count( $this->data ) - 1;

		if ( ! isset( $this->data[ $index ]['content'] ) ) {
			$this->data[ $index ]['content'] = '';
		}
		$this->data[ $index ]['content'] .= $data;
	}

	public function end_handler( $parser, $name ) {
		if ( count( $this->data ) > 1 ) {
			$data                            = array_pop( $this->data );
			$index                           = count( $this->data ) - 1;
			$this->data[ $index ]['child'][] = $data;
		}
	}
}

/**
 * Converts an XML string into an array tree structure.
 *
 * The output of this function can be passed to xml_find() to find nodes by their path.
 *
 * @param string $in The XML string.
 * @return array XML as an array.
 */
function xml_to_array( $in ) {
	$p = new TestXMLParser( $in );
	return $p->data;
}

/**
 * Finds XML nodes by a given "path".
 *
 * Example usage:
 *
 *     $tree = xml_to_array( $rss );
 *     $items = xml_find( $tree, 'rss', 'channel', 'item' );
 *
 * @param array  $tree     An array tree structure of XML, typically from xml_to_array().
 * @param string ...$elements Names of XML nodes to create a "path" to find within the XML.
 * @return array Array of matching XML node information.
 */
function xml_find( $tree, ...$elements ) {
	$n   = count( $elements );
	$out = [];

	if ( $n < 1 ) {
		return $out;
	}

	for ( $i = 0; $i < count( $tree ); $i++ ) {
		// echo "checking '{$tree[$i][name]}' == '{$elements[0]}'\n";
		// var_dump( $tree[$i]['name'], $elements[0] );
		if ( $tree[ $i ]['name'] === $elements[0] ) {
			// echo "n == {$n}\n";
			if ( 1 === $n ) {
				$out[] = $tree[ $i ];
			} else {
				$subtree =& $tree[ $i ]['child'];
				$out     = array_merge( $out, xml_find( $subtree, ...array_slice( $elements, 1 ) ) );
			}
		}
	}

	return $out;
}

function get_echo( $callback, $args = [] ) {
	ob_start();
	call_user_func_array( $callback, $args );
	return ob_get_clean();
}
