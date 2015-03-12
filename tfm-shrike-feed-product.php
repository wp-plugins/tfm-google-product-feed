<?php

/**
* Made with ❤ by themesfor.me
*
* XML Item generation
*/

class Tfm_Shrike_Product
{
	// Constraints
	const MAX_ID_LEN = 50;
	const MAX_TITLE_LEN = 150;
	const MAX_DESCRIPTION_LEN = 5000;
	const MAX_LINK_LEN = 2000;
	const MAX_PROD_TYPE_LEN = 750;

	private $product;
	private $currency;
	private $settings;

	/**
	* Setup hooks
	*
	* @param $product Product as WooCommerce object
	*/
	public function __construct(WP_Post $product)
	{
		$this->product = new WC_Product($product);
	}

	/**
	* Represent product as Google XML
	*
	* @return string XML representation of product ready for Google Product Feed
	*/
	public function get_xml()
	{
		$p = $this->product;

		if($this->validateProductData($p)) {

			$params['id'] = substr($p->id, 0, 50);
			$params['title'] = substr($p->get_title(),0,150);
			$params['description'] = substr($p->get_post_data()->post_excerpt,0,5000);
			$params['link'] = substr($p->get_permalink(),0,2000);
			$params['image_link'] = substr(wp_get_attachment_url($p->get_image_id()),0,2000);
			$params['condition'] = get_option('tfm_shrike_setting_condition','new');
			$params['availability'] = $p->is_in_stock() ? 'in stock' : 'out of stock';
			$params['price'] = sprintf('%s %s', $p->get_price(), get_woocommerce_currency());
			$params['category'] = htmlspecialchars(htmlspecialchars_decode(get_option('tfm_shrike_setting_category','')));
			$params['product_type'] = get_option('tfm_shrike_setting_type','none') == 'none' ? '' : substr($this->get_product_type($p),0,750);
			$params['gtin'] = $this->get_setting('gtin');
			$params['mpn'] = $this->get_setting('mpn');
			$params['brand'] = $this->get_setting('brand');

			$xml = file_get_contents(__DIR__ . '/assets/google-item.xml');
		}

		return isset($xml) ? TFM_XML_TOOLS::render_xml($xml, $params) : '';
	}

	private function validateProductData()
	{
		$p = $this->product;
		if(
			strlen($p->get_title()) == 0 ||
			strlen($p->get_post_data()->post_excerpt) == 0 ||
			strlen($p->get_permalink()) == 0 || strlen($p->get_permalink())>Tfm_Shrike_Product::MAX_LINK_LEN ||
			strlen(wp_get_attachment_url($p->get_image_id()))==0 || strlen(wp_get_attachment_url($p->get_image_id())) > Tfm_Shrike_Product::MAX_LINK_LEN
		) {
			return false;
		}

		return true;
	}

	private function get_shipping_xml()
	{
		/*
		<!-- 	<g:shipping>
		<g:country>US</g:country>
		<g:service>Standard</g:service>
		<g:price>14.95 USD</g:price>
	</g:shipping>-->
	<?php
	*/
	}

	private function get_product_type($post)
	{
		$args = array( 'taxonomy' => 'product_cat',);
		$terms = wp_get_post_terms($post->id,'product_cat', $args);

		$result = array();

		if(count($terms) == 0) {
			return '';
		}

		$digest = function ($id) use (&$result, &$digest)
		{
			$term = get_term_by( 'id', $id, 'product_cat', 'ARRAY_A' );

			if($term['parent']) {
				$digest($term['parent']);
			}

			$result[] = $term['name'];
		};

		$digest($terms[0]->term_id);

		return implode(' &gt; ', $result);
	}

	private function get_settings()
	{
		if(empty($this->settings)) {
			$this->settings = get_post_meta($this->product->id, 'tfm_shrike_settings', true);
		}

		return $this->settings;
	}

	private function get_setting($name)
	{
		$settings = $this->get_settings();

		if(!isset($settings[$name])) {
			return '';
		}

		return $settings[$name];
	}

}
