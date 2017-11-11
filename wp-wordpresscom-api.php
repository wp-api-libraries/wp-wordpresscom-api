<?php
/**
 * Library for accessing the WordPress.com API on WordPress
 *
 * @link https://developer.wordpress.com/docs/ API Documentation
 * @link https://developer.wordpress.com/docs/api/console/ Console
 * @package WP-API-Libraries\WP-WordPressCom-API
 */

/*
 * Plugin Name: WordPress.com API
 * Plugin URI: https://wp-api-libraries.com/
 * Description: Perform API requests.
 * Author: WP API Libraries
 * Version: 1.0.0
 * Author URI: https://wp-api-libraries.com
 * GitHub Plugin URI: https://github.com/wp-api-libraries/wp-wordpresscom-api
 * GitHub Branch: master
 */

/* Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'WordPressComAPI' ) ) {

	class WordPressComAPI {

		/**
		 * API Key.
		 *
		 * @var string
		 */
		static protected $oauth_token;

		/**
		 * CloudFlare BaseAPI Endpoint
		 *
		 * @var string
		 * @access protected
		 */
		protected $base_uri = 'https://public-api.wordpress.com/rest/v1.1/';

		/**
		 * Route being called.
		 *
		 * @var string
		 */
		protected $route = '';


		/**
		 * Class constructor.
		 *
		 * @param string $oauth_token  Wordpress.com oauth Key.
		 */
		public function __construct( $oauth_token ) {
			static::$oauth_token = trim( $oauth_token );
		}

		/**
		 * Prepares API request.
		 *
		 * @param  string $route   API route to make the call to.
		 * @param  array  $args    Arguments to pass into the API call.
		 * @param  array  $method  HTTP Method to use for request.
		 * @return self            Returns an instance of itself so it can be chained to the fetch method.
		 */
		protected function build_request( $route, $args = array(), $method = 'GET' ) {
			// Start building query.
			$this->set_headers();
			$this->args['method'] = $method;
			$this->route = $route;

			// Generate query string for GET requests.
			if ( 'GET' === $method ) {
				$this->route = add_query_arg( array_filter( $args ), $route );
			} elseif ( 'application/json' === $this->args['headers']['Content-Type'] ) {
				$this->args['body'] = wp_json_encode( $args );
			} else {
				$this->args['body'] = $args;
			}

			return $this;
		}


		/**
		 * Fetch the request from the API.
		 *
		 * @access private
		 * @return array|WP_Error Request results or WP_Error on request failure.
		 */
		protected function fetch() {
			// Make the request.
			$response = wp_remote_request( $this->base_uri . $this->route, $this->args );

			// Retrieve Status code & body.
			$code = wp_remote_retrieve_response_code( $response );
			$body = json_decode( wp_remote_retrieve_body( $response ) );

			$this->clear();
			// Return WP_Error if request is not successful.
			if ( ! $this->is_status_ok( $code ) ) {
				return new WP_Error( 'response-error', sprintf( __( 'Status: %d', 'wp-postmark-api' ), $code ), $body );
			}

			return $body;
		}


		/**
		 * Set request headers.
		 */
		protected function set_headers() {
			// Set request headers.
			$this->args['timeout'] = 30;
			$this->args['headers'] = array(
				  'Authorization' => 'Bearer '. static::$oauth_token,
				  'Content-Type' => 'application/json',
			);
		}

		/**
		 * Clear query data.
		 */
		protected function clear() {
			$this->args = array();
		}

		/**
		 * Check if HTTP status code is a success.
		 *
		 * @param  int $code HTTP status code.
		 * @return boolean       True if status is within valid range.
		 */
		protected function is_status_ok( $code ) {
			return ( 200 <= $code && 300 > $code );
		}

		/**
		 * Get a list of the current user's sites.
		 *
		 * @api GET
		 * @see https://developer.wordpress.com/docs/api/1.1/get/me/sites/ Documentation.
		 * @access public
		 * @param  array $args  Array with optional parameters. See API docs for details.
		 * @return array        Array of user's sites.
		 */
		public function me_sites( $args = array() ){
			return $this->build_request( 'me/sites', $args )->fetch();
		}


		/* STATS. */


		/**
		 * Get Stats.
		 *
		 * @access public
		 * @param mixed $site
		 * @param array $args (default: array())
		 * @return void
		 */
		public function get_stats( $site, $args = array() ) {
			return $this->build_request( 'sites/'. $site . '/stats', $args )->fetch();
		}

		/**
		 * get_stats_summary function.
		 *
		 * @access public
		 * @param mixed $site
		 * @param array $args (default: array())
		 * @return void
		 */
		public function get_stats_summary( $site, $args = array() ) {
			return $this->build_request( 'sites/'. $site . '/stats/summary', $args )->fetch();
		}

		/**
		 * get_top_posts function.
		 *
		 * @access public
		 * @param mixed $site
		 * @param array $args (default: array())
		 * @return void
		 */
		public function get_top_posts( $site, $args = array() ) {
			return $this->build_request( 'sites/'. $site . '/stats/top-posts', $args )->fetch();
		}
	}

}
