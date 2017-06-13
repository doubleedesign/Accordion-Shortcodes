<?php
/**
 * Plugin Name: Accordion Shortcodes for Zurb Foundation
 * Description: Shortcodes for adding accordions to a Zurb Foundation-based theme. A fork of Phil Buchanan's Accordion Shortcodes plugin, v2.3.3. Does not include JavaScript as it will use Foundation's. 
 * Version: 0.1
 * Author: Leesa Ward
 * Author URI: http://www.doubleedesign.com.au
 */

require_once('tinymce/tinymce.php');

// Make sure to not redeclare the class
if (!class_exists('Accordion_Shortcodes')) :

class Accordion_Shortcodes {

	/**
	 * Current plugin version number
	 */
	private $plugin_version = '0.1';

	/**
	 * Should the accordion JavaScript file be loaded the on the current page
	 * False by default
	 */
	private $load_script = false;

	/**
	 * Holds all the accordion shortcodes group settings
	 */
	private $script_data = array();

	/**
	 * Count of each accordion group on a page
	 */
	private $group_count = 0;

	/**
	 * Count for each accordion item within an accordion group
	 */
	private $item_count = 0;

	/**
	 * Holds the accordion group container HTML tag
	 */
	private $wrapper_tag = 'div';

	/**
	 * Holds the accordion item title HTML tag
	 */
	private $title_tag = 'h3';

	/**
	 * Holds the accordion item content container HTML tag
	 */
	private $content_tag = 'div';

	/**
	 * Class constructor
	 * Sets up the plugin, including: textdomain, adding shortcodes, registering
	 * scripts and adding buttons.
	 */
	function __construct() {
		$basename = plugin_basename(__FILE__);

		// Load text domain
		load_plugin_textdomain('accordion_shortcodes', false, dirname($basename) . '/languages/');

		// Add shortcodes
		$prefix = $this->get_compatibility_prefix();

		add_shortcode($prefix . 'accordion', array($this, 'accordion_shortcode'));
		add_shortcode($prefix . 'accordion-item', array($this, 'accordion_item_shortcode'));

		// Print script in wp_footer
		add_action('wp_footer', array($this, 'print_script'));

		if (is_admin()) {
			// Add link to documentation on plugin page
			add_filter("plugin_action_links_$basename", array($this, 'add_documentation_link'));

			// Add buttons to MCE editor
			if (!defined('AS_TINYMCE') || AS_TINYMCE != false) {
				$Accordion_Shortcode_Tinymce_Extensions = new Accordion_Shortcode_Tinymce_Extensions;
			}
		}
	}

	/**
	 * Get the compatibility mode prefix
	 *
	 * return string The compatibility mode prefix
	 */
	private function get_compatibility_prefix() {
		return defined('AS_COMPATIBILITY') && AS_COMPATIBILITY ? 'as-' : '';
	}

	/**
	 * Prints the accordion JavaScript in the footer
	 * This inlcludes both the accordion jQuery plugin file registered by
	 * 'register_script()' and the accordion settings JavaScript variable.
	 */
	public function print_script() {
		// Check to see if shortcodes are used on page
		if (!$this->load_script) return;

		// Output accordions settings JavaScript variable
		wp_localize_script('accordion-shortcodes-script', 'accordionShortcodesSettings', $this->script_data);
	}

	/**
	 * Checks if a value is boolean
	 *
	 * @param string $value The value to test
	 * return bool
	 */
	private function is_boolean($value) {
		return filter_var($value, FILTER_VALIDATE_BOOLEAN);
	}

	/**
	 * Check for valid HTML tag
	 * Checks the supplied HTML tag against a list of approved tags.
	 *
	 * @param string $tag The HTML tag to test
	 * return string A valid HTML tag
	 */
	private function check_html_tag($tag) {
		$tag = preg_replace('/\s/', '', $tag);
		$tags = array('h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'span');

		if (in_array($tag, $tags)) return $tag;
		else return $this->title_tag;
	}

	/**
	 * Check for valid scroll value
	 * Scroll value must be either an int or bool
	 *
	 * @param int/bool $scroll The scroll offset integer or true/false
	 * return int/bool The scroll offset integer else true/false
	 */
	private function check_scroll_value($scroll) {
		$int = intval($scroll);

		if (is_int($int) && $int != 0) {
			return $int;
		}
		else {
			return $this->is_boolean($scroll);
		}
	}

	/**
	 * Get's the ID for an accordion item
	 *
	 * @param string $id If the user set an ID
	 * return array The IDs for the accordion title and item
	 */
	private function get_accordion_id($id) {
		$title_id = $id ? $id : "accordion-$this->group_count-t$this->item_count";
		$content_id = $id ? "content-$id" : "accordion-$this->group_count-c$this->item_count";

		return array(
			'title'   => $title_id,
			'content' => $content_id
		);
	}

	/**
	 * Accordion group shortcode
	 */
	public function accordion_shortcode($atts, $content = null) {
		// The shortcode is used on the page, so load the JavaScript
		$this->load_script = true;

		// Set accordion counters
		$this->group_count++;
		$this->item_count = 0;

		extract(shortcode_atts(array(
			'tag'          => '',
			'autoclose'    => true,
			'openfirst'    => false,
			'openall'      => false,
			'clicktoclose' => false,
			'scroll'       => false,
			'class'        => ''
		), $atts, 'accordion'));

		// Set global HTML tag names
		// Set title HTML tag
		if ($tag) $this->title_tag = $this->check_html_tag($tag);
		else $this->title_tag = 'h3';

		// Set settings object (for use in JavaScript)
		$script_data = array(
			'id'           => "accordion-$this->group_count",
			'autoClose'    => $this->is_boolean($autoclose),
			'openFirst'    => $this->is_boolean($openfirst),
			'openAll'      => $this->is_boolean($openall),
			'clickToClose' => $this->is_boolean($clicktoclose),
			'scroll'       => $this->check_scroll_value($scroll)
		);

		// Add this shortcodes settings instance to the global script data array
		$this->script_data[] = $script_data;

		return sprintf('<ul id="%2$s" class="accordion no-js%3$s" data-accordion data-allow-all-closed="true" role="tablist">%1$s</ul>',
			do_shortcode($content),
			"accordion-$this->group_count",
			$class ? " $class" : ''
		);
	}



	/**
	 * Accordion item shortcode
	 */
	public function accordion_item_shortcode($atts, $content = null) {
		extract(shortcode_atts(array(
			'title' => '',
			'id'    => '',
			'tag'   => '',
			'class' => '',
			'state' => ''
		), $atts, 'accordion-item'));

		// Increment accordion item count
		$this->item_count++;

		$ids = $this->get_accordion_id($id);
		
		$open_wrapper = sprintf('<li class="accordion-item %1$s" data-accordion-item>',
			$state ? $state : ''				
		);

		$accordion_title = sprintf('<a id="%3$s" class="accordion-title" role="tab" tabindex="0" %5$s><span>%2$s</span></a>',
			$tag ? $this->check_html_tag($tag) : $this->title_tag,
			$title ? $title : '<span style="color:red;">' . __('Please enter a title attribute', 'accordion_shortcodes') . '</span>',
			$ids['title'],
			$ids['content'],
			$class ? " $class" : ''
		);

		$accordion_content = sprintf('<div id="%3$s" class="accordion-content" data-tab-content role="tabpanel" aria-labelledby="%4$s" aria-hidden="true">%2$s</div>',
			$this->content_tag,
			do_shortcode($content),
			$ids['content'],
			$ids['title']
		);
		
		$close_wrapper = sprintf('</li>'); 
		
		return $open_wrapper . $accordion_title . $accordion_content . $close_wrapper;
	}



	/**
	 * Add documentation link on plugin page
	 */
	public function add_documentation_link($links) {
		array_push($links, sprintf('<a href="%s">%s</a>',
			'http://wordpress.org/plugins/accordion-shortcodes/',
			_x('Documentation', 'link to documentation on wordpress.org site', 'accordion_shortcodes')
		));

		return $links;
	}

}

$Accordion_Shortcodes = new Accordion_Shortcodes;

endif;
