<?php
/**
 * Block Name: Byline.
 *
 * @package byline-manager
 */

/**
 * Registers the byline-manager/byline block using the metadata loaded from the `block.json` file.
 */
function byline_manager_byline_block_init(): void {
	// Register the block by passing the location of block.json.
	register_block_type(
		__DIR__
	);
}
add_action( 'init', 'byline_manager_byline_block_init' );
