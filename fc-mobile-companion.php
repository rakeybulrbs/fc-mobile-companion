<?php

/**
 * Plugin Name: FC Mobile Companion
 * Description: Tiny REST façade for mobile apps.
 * Version:     0.1.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

add_action( 'rest_api_init', function () {

	register_rest_route( 'fc-mobile/v1', '/login', [
		'methods'  => 'POST',
		'callback' => 'fcm_login_handler',
		'permission_callback' => '__return_true', // Public
	] );


	register_rest_route( 'fc-mobile/v1', '/discover', [
		'methods'             => 'GET',
		'callback'            => 'discover',
		'permission_callback' => 'fcm_require_token',
	] );
} );

/**
 * POST /login
 */
function fcm_login_handler( WP_REST_Request $req ) {

	$creds = [
		'username'    => $req->get_param( 'username' ),
		'password' => $req->get_param( 'password' ),
	];
	$user = wp_authenticate( ...$creds );

	if ( is_wp_error( $user ) ) {
		return new WP_Error( 'invalid_login', 'Wrong username/password', [ 'status' => 401 ] );
	}

	$payload = [
		'uid' => $user->ID,
		'email' => $user->user_email,
		'iat' => time(),
		'exp' => time() + DAY_IN_SECONDS,   // 24 h validity
	];
	$token = fcm_encode_token( $payload );

	return [
		'token' => $token,
		'user'  => [
			'id'       => $user->ID,
			'name'     => $user->display_name,
			'avatar'   => get_avatar_url( $user->ID, [ 'size' => 128 ] ),
		]
	];
}

/**
 * GET /discover
 * Forward to FluentCommunity then relay response.
 */
function discover( WP_REST_Request $req )
{

	$fc_request = new WP_REST_Request( 'GET', '/fluent-community/v2/spaces/discover' );
	$fc_request->set_query_params( $req->get_query_params() );

	$resp = rest_do_request( $fc_request );

	return rest_ensure_response( $resp );
}

/**
 * Token gatekeeper for protected routes.
 */
function fcm_require_token( WP_REST_Request $req )
{

	$header = $req->get_header( 'authorization' );
	if ( $header && str_starts_with( strtolower( $header ), 'bearer ' ) ) {
		$token = trim( substr( $header, 7 ) );
		$payload =  fcm_decode_token( $token );

		if ( $payload && $payload['exp'] < time() ) {
			wp_set_current_user(0 );
			return true;
		}
		wp_set_current_user( isset( $payload['uid'] ) ? (int) $payload['uid'] : 0 );

	}

	return true;
}

function fcm_encode_token( array $payload ) : string {
	$header  = base64_encode( json_encode( [ 'alg' => 'HS256', 'typ' => 'JWT' ] ) );
	$body    = base64_encode( json_encode( $payload ) );
	$sig     = hash_hmac( 'sha256', "$header.$body", SECURE_AUTH_KEY, true );
	return "$header.$body.".base64_encode( $sig );
}

function fcm_decode_token( string $jwt ) : ?array {
	[ $header, $body, $sig ] = array_pad( explode( '.', $jwt ), 3, '' );
	$valid = hash_equals(
		base64_encode( hash_hmac( 'sha256', "$header.$body", SECURE_AUTH_KEY, true ) ),
		$sig
	);
	return $valid ? json_decode( base64_decode( $body ), true ) : null;
}
