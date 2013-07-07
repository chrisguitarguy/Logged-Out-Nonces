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
 * All nonce ID providers MUST implement this interface.
 *
 * @since   0.1
 * @author  Christopher Davis <http://christopherdavis.me>
 */
interface LONonces_ProviderInterface
{
    /**
     * Hooked into `init` for the provider to do whatever they may need to do
     * to get things rolling. Eg. Set cookies, etc.
     *
     * @since   0.1
     * @access  public
     * @return  void
     */
    public function init();

    /**
     * Get the logged out user's unique identifier. Providers should take care
     * of storing this ID in such a way that it can be retrieved (and nonces
     * validated).
     *
     * @since   0.1
     * @access  public
     * @return  string
     */
    public function getId();
}
