<?php
/**
 * ZFDebug Zend Additions
 *
 * @category   ZFDebug
 * @package    ZFDebug_Controller
 * @subpackage Plugins
 * @copyright  Copyright (c) 2008-2009 ZF Debug Bar Team (http://code.google.com/p/zfdebug)
 * @license    http://code.google.com/p/zfdebug/wiki/License     New BSD License
 * @version    $Id: $
 */

/**
 * @category   ZFDebug
 * @package    ZFDebug_Controller
 * @subpackage Plugins
 * @copyright  Copyright (c) 2008-2009 ZF Debug Bar Team (http://code.google.com/p/zfdebug)
 * @license    http://code.google.com/p/zfdebug/wiki/License     New BSD License
 */
class ZFDebug_Controller_Plugin_Debug_Plugin_Auth implements ZFDebug_Controller_Plugin_Debug_Plugin_Interface
{
    /**
     * Contains plugin identifier name
     *
     * @var string
     */
    protected $_identifier = 'auth';

    /**
     * Contains Zend_Auth object
     *
     * @var Zend_Auth
     */
    protected $_auth;

    /**
     * Contains "column name" for the username
     *
     * @var string
     */
    protected $_user = 'user';

    /**
     * Contains "column name" for the role
     *
     * @var string
     */
    protected $_role = 'role';

    /**
     * Contains Acls for this application
     *
     * @var Zend_Acl
     */
    protected $_acl;


    /**
     * Create ZFDebug_Controller_Plugin_Debug_Plugin_Auth.
     *
     * @param array $options Array with user and role values.
     */
    public function __construct(array $options = array())
    {
        $this->_auth = Zend_Auth::getInstance();
        if (isset($options['user'])) {
            $this->_user = $options['user'];
        }
        if (isset($options['role'])) {
            $this->_role = $options['role'];
        }
    }


    /**
     * Gets identifier for this plugin
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->_identifier;
    }


    /**
     * Returns the base64 encoded icon
     *
     * @return string
     **/
    public function getIconData()
    {
        return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAJ3SURBVDjLpZNtSNNRFIcNKunF1rZWBMJqKaSiX9RP1dClsjldA42slW0q5oxZiuHrlqllLayoaJa2jbm1Lc3QUZpKFmmaTMsaRp+kMgjBheSmTL2//kqMBJlFHx44XM7vOfdyuH4A/P6HFQ9zo7cpa/mM6RvCrVDzaVDy6C5JJKv6rwSnIhlFd0R0Up/GwF2KWyl01CTSkM/dQoQRzAurCjRCGnRUUE2FaoSL0HExiYVzsQwcj6RNrSqo4W5Gh6Yc4+1qDDTkIy+GhYK4nTgdz0H2PrrHUJzs71NQn86enPn+CVN9GnzruoYR63mMPbkC59gQzDl7pt7rc9f7FNyUhPY6Bx9gwt4E9zszhWWpdg6ZcS8j3O7zCTuEpnXB+3MNZkUUZu0NmHE8XsL91oSWwiiEc3MeseLrN6woYCWa/Zl8ozyQ3w3Hl2lYy0SwlCUvsVi/Gv2JwITnYPDun2Hy6jYuEzAF1jUBCVYpO6kXo+NuGMeBAgcgfwNkvgBOPgUqXgKvP7rBFvRhE1crp8Vq1noFYSlacVyqGk0D86gbART9BDk9BFnPCNJbCY5aCFL1Cyhtp0RWAp74MsKSrkq9guHyvfMTtmLc1togpZoyqYmyNoITzVTYRJCiXYBIQ3CwFqi83o3JDhX6C0M8XsGIMoQ4OyuRlq1DdZcLkmbgGDX1iIEKNxAcbgTEOqC4ZRaJ6Ub86K7CYFEo8Qo+GBQlQyXBczLZpbloaQ9k1NUz/kD2myBBKxRZpa5hVcQslalatoUxizxAVVrN3CW21bFj9F858Q9dnIRmDyeuybM71uxmH9BNBB1q6zybV7H9s1Ue4PM3/gu/AEbfqfWy2twsAAAAAElFTkSuQmCC';
    }


    /**
     * Gets menu tab for the Debugbar
     *
     * @return string
     */
    public function getTab()
    {
        if(!$this->_auth->hasIdentity())
        {
            return 'Not authorized';
        }
        $identity = $this->_auth->getIdentity();

        if(is_object($identity))
        {
            $username = $this->_auth->getIdentity()->{$this->_user};
            $role = $this->_auth->getIdentity()->{$this->_role};
        }
        else
        {
            $username = $identity[$this->_user];
            $role = $identity[$this->_role];
        }
        if(is_array($role))
        {
            $role = implode(',', $role);
        }

        return $username . ' (' . $role . ')';
    }


    /**
     * Gets content panel for the Debugbar
     *
     * @return string
     */
    public function getPanel()
    {
        $result = '<div></div>';

        $identity = $this->_auth->getIdentity();

        if( $identity )
        {
            $result = '<div>';
            foreach( $identity as $key => $value )
            {
                $result .= "<strong>{$key}</strong>: ";
                if(is_array($value))
                {
                    $result .= '<br />';
                    foreach($value as $valueKey => $valueValue)
                    {
                        $result .= str_repeat('&nbsp;', strlen($key) + 2) . $valueKey . ':' . $valueValue . '<br />';
                    }
                }
                else
                {
                    $result .= "{$value}<br />";
                }
            }
            $result .= '</div>';
        }

        return $result;
    }
}