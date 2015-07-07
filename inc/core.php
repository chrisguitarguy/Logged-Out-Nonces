<?php
/**
 * Logged Out Nonces
 *
 * @category    WordPress
 * @package     LONonces
 * @copyright   2013 Christopher Davis
 * @license     http://opensource.org/licenses/MIT MIT
 */

/**
 * Hooked into plugins loaded to kick things off.
 *
 * @since  0.1
 * @uses   add_action
 * @uses   do_action
 * @return void
 */
function lononces_load()
{
    $path = dirname(__FILE__);

    do_action('lononces_loading');

    require_once $path . '/ProviderInterface.php';
    require_once $path . '/AbstractProvider.php';
    require_once $path . '/CookieProvider.php';

    add_filter('nonce_user_logged_out', 'lononces_userid');
    add_action('init', array(lononces_provider(), 'init'));

    do_action('lononces_loaded');
}

/**
 * Get the nonce provider.
 *
 * @since   0.1
 * @return  LONonces_ProviderInterface
 */
function lononces_provider()
{
    static $provider = null;

    if (!is_null($provider)) {
        return $provider;
    }

    if(  php_sapi_name() === 'cli') {
        $default = $provider = new LONonces_MockProvider();
    } else {
        $default = $provider = new LONonces_CookieProvider(
                apply_filters('lononces_cookie_name', '_lononce_id'),
                apply_filters('lononces_cookie_expires', WEEK_IN_SECONDS * 4),
                apply_filters('lononces_cookie_secure', false),
                apply_filters('lononces_cookie_salt', defined('NONCE_SALT') ? NONCE_SALT : 'lononces_salt')
        );
    }

    $provider = apply_filters('lononces_provider', $provider);

    if (!$provider instanceof LONonces_ProviderInterface) {
        $provider = $default;
    }

    return $provider;
}

/**
 * Replace empty user ID with our own.
 *
 * @since   0.1
 * @param   string $uid
 * @return  string
 */
function lononces_userid($uid)
{
    if (!$uid) {
        $uid = lononces_provider()->getId();
    }

    return $uid;
}
