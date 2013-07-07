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
 * Implements LONonces_ProviderInterface::init
 *
 * @since   0.1
 * @author  Christopher Davis <http://christopherdavis.me>
 */
abstract class LONonces_AbstractProvider implements LONonces_ProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        // noop
    }
}
