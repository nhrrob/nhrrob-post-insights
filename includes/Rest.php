<?php
/**
 * The REST routes: two the tracking script calls for every visitor, two the dashboard app reads.
 *
 * @package NHRRob\PostInsights
 */

namespace NHRRob\PostInsights;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Rest Class
 */
class Rest {

	const REST_NAMESPACE = 'nhrrob-post-insights/v1';

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
	}

	/**
	 * Register the REST routes.
	 *
	 * @return void
	 */
	public function register_routes() {
		$post = [
			'required'          => true,
			'sanitize_callback' => 'absint',
		];

		// Open to every visitor: all they can do is add to a counter of a public post.
		register_rest_route(
			self::REST_NAMESPACE,
			'/view',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'view' ],
				'permission_callback' => '__return_true',
				'args'                => [
					'post'     => $post,
					'referrer' => [
						'default'           => '',
						'sanitize_callback' => 'sanitize_text_field',
					],
				],
			]
		);

		register_rest_route(
			self::REST_NAMESPACE,
			'/time',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'time' ],
				'permission_callback' => '__return_true',
				'args'                => [
					'post'    => $post,
					'seconds' => [
						'required'          => true,
						'sanitize_callback' => [ Visit::class, 'seconds' ],
					],
				],
			]
		);

		register_rest_route(
			self::REST_NAMESPACE,
			'/posts',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'posts' ],
				'permission_callback' => [ self::class, 'can_view' ],
				'args'                => self::posts_args(),
			]
		);

		register_rest_route(
			self::REST_NAMESPACE,
			'/stats',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'stats' ],
				'permission_callback' => [ self::class, 'can_view' ],
				'args'                => [
					'days' => [
						'default'           => 30,
						'sanitize_callback' => 'absint',
					],
				],
			]
		);
	}

	/**
	 * The arguments of GET /posts. `days` 0 is all time.
	 *
	 * @return array
	 */
	public static function posts_args() {
		return [
			'days'    => [
				'type'              => 'integer',
				'default'           => 30,
				'sanitize_callback' => 'absint',
			],
			'orderby' => [
				'type'    => 'string',
				'default' => 'views',
				'enum'    => array_keys( Store::ORDERBY ),
			],
			'order'   => [
				'type'    => 'string',
				'default' => 'desc',
				'enum'    => [ 'asc', 'desc' ],
			],
			'page'    => [
				'type'              => 'integer',
				'default'           => 1,
				'sanitize_callback' => 'absint',
				'minimum'           => 1,
			],
		];
	}

	/**
	 * Whether the current user may see the numbers.
	 *
	 * @return bool
	 */
	public static function can_view() {
		/**
		 * The capability needed to see the numbers.
		 *
		 * @param string $capability Capability.
		 */
		return current_user_can( (string) apply_filters( 'nhrrob_post_insights_capability', 'manage_options' ) );
	}

	/**
	 * Whether this request's view or reading time is counted.
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return bool
	 */
	private function counts( $request ) {
		return ! Visit::is_bot( $request->get_header( 'user_agent' ) ) && Visit::counts( $request['post'] );
	}

	/**
	 * Count a view.
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response
	 */
	public function view( $request ) {
		if ( $this->counts( $request ) ) {
			Store::add_view( $request['post'], Visit::host( $request['referrer'], (string) wp_parse_url( home_url(), PHP_URL_HOST ) ) );
		}

		// The same answer either way, so a script learns nothing from it.
		return new \WP_REST_Response( null, 204 );
	}

	/**
	 * Add reading time.
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response
	 */
	public function time( $request ) {
		if ( $request['seconds'] > 0 && $this->counts( $request ) ) {
			Store::add_seconds( $request['post'], $request['seconds'] );
		}

		return new \WP_REST_Response( null, 204 );
	}

	/**
	 * The numbers of the last days.
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response
	 */
	public function stats( $request ) {
		return rest_ensure_response( Store::stats( $request['days'] ) );
	}

	/**
	 * One page of the table of every post.
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response
	 */
	public function posts( $request ) {
		return rest_ensure_response( Store::posts( $request['days'], $request['orderby'], $request['order'], $request['page'] ) );
	}
}
