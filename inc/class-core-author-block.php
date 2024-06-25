<?php
/**
 * Core Author Block class file
 *
 * This class is to filter the core post author block so that it displays info from the Byline Manager.
 *
 * @package Byline_Manager
 */

declare( strict_types = 1 );

namespace Byline_Manager;

/**
 * Sets up the Singleton trait use.
 *
 * @phpstan-use-trait-for-class Singleton
 */
use Byline_Manager\traits\Singleton;

/**
 * Conditions
 */
class Core_Author_Block {
	/**
	 * Use the singleton.
	 *
	 * @use Singleton<static>
	 */
	use Singleton;

	/**
	 * Array of core author blocks.
	 *
	 * @var list{array{name:string, slug:string, callable:callable}}|array{}
	 */
	private array $core_author_blocks = [];
}
