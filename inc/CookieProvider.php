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
 * The default provider. Uses a cookie to store the user ID.
 *
 * @since   0.1
 * @author  Christopher Davis <http://christopherdavis.me>
 */
class LONonces_CookieProvider implements LONonces_ProviderInterface
{
    /**
     * The name of our cookie.
     *
     * @since   0.1
     * @access  protected
     * @var     string
     */
    protected $cookie_name;

    /**
     * When the cookie expires.
     *
     * @since   0.1
     * @access  protected
     * @var     int
     */
    protected $expires;

    /**
     * Whether or not to set an SSL only cookie.
     *
     * @since   0.1
     * @access  protected
     * @var     boolean
     */
    protected $secure;

    /**
     * Nonce salt that we'll use to "sign" our IDs to ensure they are actually
     * from us.
     *
     * @since   0.1
     * @access  protected
     * @var     string
     */
    protected $salt;

    /**
     * Container for an instance of PasswordHash
     *
     * @since   0.1
     * @access  protected
     * @var     PasswordHash
     */
    protected $hasher = null;


    /**
     * Constructor.
     *
     * @since   0.1
     * @access  public
     * @param   string $cookie_name
     * @param   int $expires
     * @param   boolean $secure
     * @return  void
     */
    public function __construct($cookie_name, $expires, $secure, $salt)
    {
        $this->cookie_name = $cookie_name;
        $this->expires = $expires;
        $this->secure = $secure;
        $this->salt = $salt;
    }

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        list($id, $expires, $sig) = $this->cookieId();

        // if we already have an ID set up, don't bother
        if ($id && $this->validExpiration($expires) && $this->validSignature($id, $sig)) {
            return;
        }

        $uid = md5($this->getHasher()->get_random_bytes(64));

        $this->setCookie($uid);
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        list($uid, $expires, $sig) = $this->cookieId();
        return $uid;
    }

    /**
     * Get the user ID from the cookie.
     *
     * @since   0.1
     * @access  protected
     * @return  array [$uid, $expires] or [null, null] on failure
     */
    protected function cookieId()
    {
        if (!isset($_COOKIE[$this->cookie_name])) {
            return array(null, null, null);
        }

        $cookie = $_COOKIE[$this->cookie_name];

        if (2 !== substr_count($cookie, '|')) {
            return array(null, null, null);
        }

        return explode('|', $cookie);
    }

    /**
     * Set the nonce cookie.
     *
     * @since   0.1
     * @access  protected
     * @param   string $uid The user ID to set
     * @return  void
     */
    protected function setCookie($uid)
    {
        $expires = time() + $this->expires;
        $value = $uid . '|' . $expires . '|' . $this->sign($uid);

        // make sure we put the user ID into the $_COOKIE superglobal
        $_COOKIE[$this->cookie_name] = $value;

        return setcookie(
            $this->cookie_name,
            $value,
            $expires,
            COOKIEPATH,
            COOKIE_DOMAIN,
            $this->secure
        );
    }

    /**
     * Get the hasher, used so we can generate a nice set of random bytes
     *
     * @since   0.1
     * @access  protected
     * @return  PasswordHash
     */
    protected function getHasher()
    {
        if (!is_null($this->hasher)) {
            return $this->hasher;
        }

        require_once ABSPATH . 'wp-includes/class-phpass.php';

        $this->hasher = new PasswordHash(8, true);

        return $this->hasher;
    }

    /**
     * Check to see if an expires time falls without our allowed time limits.
     *
     * @since   0.1
     * @access  protected
     * @param   int $expires The unix timestamp to check
     * @return  boolean
     */
    protected function validExpiration($expires)
    {
        $diff = intval($expires) - time();

        // if we've passed our day threshold return false
        if ($diff <= DAY_IN_SECONDS) {
            return false;
        }

        return true;
    }

    /**
     * Check a signature and see if it's valid.
     *
     * @since   0.1
     * @access  protected
     * @param   string $uid
     * @param   string $sig
     * @return  boolean
     */
    protected function validSignature($uid, $sig)
    {
        return 0 === strcmp(
            $sig,
            $this->sign($uid)
        );
    }

    /**
     * Sign $uid with our salt.
     *
     * @since   0.1
     * @access  protected
     * @param   string $uid
     * @return  string
     */
    protected function sign($uid)
    {
        return hash_hmac('sha1', $uid, $this->salt);
    }
}
