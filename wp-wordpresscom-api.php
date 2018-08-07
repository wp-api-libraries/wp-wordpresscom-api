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

	/**
	 * WordPressComAPI class.
	 */
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

		/* USERS. */

		/**
		 * get_users function.
		 *
		 * @access public
		 * @param mixed $site
		 * @param array $args (default: array())
		 * @return void
		 */
		public function get_users( $site, $args = array() ) {
			return $this->build_request( 'sites/'. $site . '/users', $args )->fetch();
		}

		public function update_user() {

		}

		public function get_user() {

		}



		/* SITES. */

		public function get_rendered_shortcode( $site, $args = array() ) {

		}

		/**
		 * get_available_shortcodes function.
		 *
		 * @access public
		 * @param mixed $site
		 * @param array $args (default: array())
		 * @return void
		 */
		public function get_available_shortcodes( $site, $args = array() ) {
			return $this->build_request( 'sites/'. $site . '/shortcodes', $args )->fetch();
		}

		/**
		 * get_available_embeds function.
		 *
		 * @access public
		 * @param mixed $site
		 * @param array $args (default: array())
		 * @return void
		 */
		public function get_available_embeds( $site, $args = array() ) {
			return $this->build_request( 'sites/'. $site . '/embeds', $args )->fetch();
		}

		/**
		 * get_widgets function.
		 *
		 * @access public
		 * @param mixed $site
		 * @param array $args (default: array())
		 * @return void
		 */
		public function get_widgets( $site, $args = array() ) {
			return $this->build_request( 'sites/'. $site . '/widgets', $args )->fetch();
		}

		/**
		 * get_post_types function.
		 *
		 * @access public
		 * @param mixed $site
		 * @param array $args (default: array())
		 * @return void
		 */
		public function get_post_types( $site, $args = array() ) {
			return $this->build_request( 'sites/'. $site . '/post-types', $args )->fetch();
		}

		/**
		 * get_post_types_count function.
		 *
		 * @access public
		 * @param mixed $site
		 * @param mixed $post_type
		 * @param array $args (default: array())
		 * @return void
		 */
		public function get_post_types_count( $site, $post_type, $args = array() ) {
			return $this->build_request( 'sites/'. $site . '/post-counts/' . $post_type, $args )->fetch();
		}

		/**
		 * get_page_templates function.
		 *
		 * @access public
		 * @param mixed $site
		 * @param array $args (default: array())
		 * @return void
		 */
		public function get_page_templates( $site, $args = array() ) {
			return $this->build_request( 'sites/'. $site . '/page-templates', $args )->fetch();
		}

		/* POSTS. */

		/**
		 * get_posts function.
		 *
		 * @access public
		 * @param mixed $site
		 * @param array $args (default: array())
		 * @return void
		 */
		public function get_posts( $site, $args = array() ) {
			return $this->build_request( 'sites/'. $site . '/posts', $args )->fetch();
		}



		/* COMMENTS. */

		/* TAXONOMY. */

		/**
		 * get_categories function.
		 *
		 * @access public
		 * @param mixed $site
		 * @param array $args (default: array())
		 * @return void
		 */
		public function get_categories( $site, $args = array() ) {
			return $this->build_request( 'sites/'. $site . '/categories', $args )->fetch();
		}

		/**
		 * get_tags function.
		 *
		 * @access public
		 * @param mixed $site
		 * @param array $args (default: array())
		 * @return void
		 */
		public function get_tags( $site, $args = array() ) {
			return $this->build_request( 'sites/'. $site . '/tags', $args )->fetch();
		}

		/* FOLLOW. */

		/* SHARING. */

		/* FRESHLY PRESSED. */

		/* NOTIFICATIONS. */

		/* INSIGHTS. */

		/* READER. */

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

		/**
		 * get_video_stats function.
		 *
		 * @access public
		 * @param mixed $site
		 * @param mixed $post_id
		 * @param array $args (default: array())
		 * @return void
		 */
		public function get_video_stats( $site, $post_id, $args = array() ) {
			return $this->build_request( 'sites/'. $site . '/stats/video/' . $post_id, $args )->fetch();
		}

		/**
		 * get_site_referrers function.
		 *
		 * @access public
		 * @param mixed $site
		 * @param array $args (default: array())
		 * @return void
		 */
		public function get_site_referrers( $site, $args = array() ) {
			return $this->build_request( 'sites/'. $site . '/stats/referrers', $args )->fetch();
		}

		/**
		 * get_site_country_views function.
		 *
		 * @access public
		 * @param mixed $site
		 * @param array $args (default: array())
		 * @return void
		 */
		public function get_site_country_views( $site, $args = array() ) {
			return $this->build_request( 'sites/'. $site . '/stats/country-views', $args )->fetch();
		}

		/**
		 * get_site_outbound_clicks function.
		 *
		 * @access public
		 * @param mixed $site
		 * @param array $args (default: array())
		 * @return void
		 */
		public function get_site_outbound_clicks( $site, $args = array() ) {
			return $this->build_request( 'sites/'. $site . '/stats/clicks', $args )->fetch();
		}

		/**
		 * get_site_stats_by_tags function.
		 *
		 * @access public
		 * @return void
		 */
		public function get_site_stats_by_tags( $site, $args = array() ) {
			return $this->build_request( 'sites/'. $site . '/stats/tags', $args )->fetch();
		}

		/**
		 * get_site_top_authors function.
		 *
		 * @access public
		 * @param mixed $site
		 * @param array $args (default: array())
		 * @return void
		 */
		public function get_site_top_authors( $site, $args = array() ) {
			return $this->build_request( 'sites/'. $site . '/stats/top-authors', $args )->fetch();
		}

		/**
		 * get_site_stats_comments function.
		 *
		 * @access public
		 * @param mixed $site
		 * @param array $args (default: array())
		 * @return void
		 */
		public function get_site_stats_comments( $site, $args = array() ) {
			return $this->build_request( 'sites/'. $site . '/stats/comments', $args )->fetch();
		}

		/**
		 * get_site_stats_video_plays function.
		 *
		 * @access public
		 * @param mixed $site
		 * @param array $args (default: array())
		 * @return void
		 */
		public function get_site_stats_video_plays( $site, $args = array() ) {
			return $this->build_request( 'sites/'. $site . '/stats/video-plays', $args )->fetch();
		}


		/**
		 * get_site_followers function.
		 *
		 * @access public
		 * @param mixed $site
		 * @param array $args (default: array())
		 * @return void
		 */
		public function get_site_followers( $site, $args = array() ) {
			return $this->build_request( 'sites/'. $site . '/stats/followers', $args )->fetch();
		}


		/* MEDIA. */

		public function delete_media() {

		}

		public function post_media_item() {

		}

		/**
		 * get_all_media function.
		 *
		 * @access public
		 * @param mixed $site
		 * @param array $args (default: array())
		 * @return void
		 */
		public function get_all_media( $site, $args = array() ) {
			return $this->build_request( 'sites/'. $site . '/media/', $args )->fetch();

		}

		public function upload_media() {

		}

		public function update_media_item() {

		}

		/* MENUS. */

		public function create_menu() {

		}

		public function update_menu() {

		}

		/**
		 * get_menu function.
		 *
		 * @access public
		 * @param mixed $site
		 * @param mixed $menu_id
		 * @param mixed $args
		 * @return void
		 */
		public function get_menu( $site, $menu_id, $args ) {
			return $this->build_request( 'sites/'. $site . '/menus/' . $menu_id, $args )->fetch();
		}

		/**
		 * get_all_menus function.
		 *
		 * @access public
		 * @param mixed $site
		 * @param mixed $args
		 * @return void
		 */
		public function get_menus( $site, $args = array() ) {
			return $this->build_request( 'sites/'. $site . '/menus', $args )->fetch();
		}

		public function delete_menu() {

		}

		/* BATCH. */

		/* VIDEOS. */

	}

}
