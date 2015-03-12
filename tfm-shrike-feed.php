<?php

/**
 * Made with ❤ by themesfor.me
 *
 * XML Feed generation
 */

require_once('tfm-shrike-feed-product.php');

class tfm_shrike_feed
{
	// Constraints
	const MAX_PRODUCTS = 5000;

	/**
     * Setup hooks
     */
	public function __construct()
	{
		add_action('init', array($this, 'intercept_feed'));
	}

	/**
     * Intecept all url's with feed param
     */
	public function intercept_feed()
	{
		if (isset($_GET['feed'])) {
			$feed_name = $_GET['feed'];
		} else {
			return;
		}

		switch($feed_name){
		    case 'google_feed' :
		        $this->create_google_feed();
		        exit();
		    break;
		}
	}

	/**
     * Generate XML ready for Google Merchants
     */
	public function create_google_feed()
	{

			$params['title'] = get_bloginfo('name');
			$params['link'] = get_bloginfo('url');
			$params['description'] = get_bloginfo('description');
			$params['items'] = $this->create_items_list();

			$xml = file_get_contents(__DIR__ . '/assets/google-feed.xml');

			$xml = TFM_XML_TOOLS::render_xml($xml, $params);
			$xml = TFM_XML_TOOLS::remove_empty_nodes($xml);

			header('Content-Type: application/xml');
			echo $xml;
	}

	/**
     * Create items specific XML
     *
     * @param array $tabs List of tabs
     * @return array List of tabs with our tab appended
     */
	public function create_items_list()
	{
		$args = array('post_type' => 'product','posts_per_page' => self::MAX_PRODUCTS);
	  	$query = new WP_Query($args);

		$itemsXml = '';
		
		if($query->have_posts()) {
			$posts = $query->get_posts();

			foreach($posts as $post) {
				$tfm_product = new Tfm_Shrike_Product($post);

				$itemsXml .= $tfm_product->get_xml();
			}
		}
		
		return $itemsXml;
	}

}

if (!defined('ABSPATH')) exit;

global $tfm_shrike_feed;
$tfm_shrike_feed = new tfm_shrike_feed();
