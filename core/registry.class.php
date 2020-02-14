<?php

class Registry
{
    /**
     * Holds an instance of the Registry Class.
     *
     * @var Registry
     */
    private static $instance = null;

    /**
     * The datastore for all variables in the registry.
     *
     * @var Array
     */
    protected $registry = array('controller' => 'home', 'action' => 'view');
    protected static $config = [];

    private function __construct()
    {
        Self::$config = parse_ini_file('./../config/'.ENVIRONMENT.'.ini',true);
        foreach(Self::$config['defines'] as $key=>$value){
            define($key,$value);
        }
    }

    /**
     * Singleton implementation of the config.
     *
     * @return Registry
     */
    public static function getConfig()
    {

        return self::$config;
    }

    /**
     * Singleton implementation of the registry.
     *
     * @return Registry
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Return a variable in the registry. If the variable does not exist return null.
     *
     * @param String $var
     * @return Mixed
     */
    public function get($var, $idx = false)
    {

        if (!isset($this->registry[$var])) {
            return false;
        }
        if ($idx != false) {

            if (is_object($this->registry[$var])) {
                if (!isset($this->registry[$var]->$idx)) {
                    return false;
                }
                return $this->registry[$var]->$idx;
            } else {
                if (!isset($this->registry[$var][$idx])) {
                    return false;
                }
                return $this->registry[$var][$idx];
            }

        }
        return $this->registry[$var];
    }

    /**
     * Return a variable in the registry. If the variable does not exist return null.
     *
     * @param String $var
     * @return Mixed
     */
    public function getElse($alt, $var, $idx = false)
    {

        if (!isset($this->registry[$var])) {
            return $alt;
        }
        if ($idx != false) {

            if (is_object($this->registry[$var])) {
                if (!isset($this->registry[$var]->$idx)) {
                    return $alt;
                }
                return $this->registry[$var]->$idx;
            } else {
                if (!isset($this->registry[$var][$idx])) {
                    return $alt;
                }
                return $this->registry[$var][$idx];
            }

        }
        return $this->registry[$var];
    }

    public function dump()
    {
        return ($this->registry);
    }

    /**
     * Set a variable in the registry.
     *
     * @param String $var
     * @param Mixed $val
     * @return Mixed
     */

    public function set($var, $val)
    {
        $this->registry[$var] = $val;

        return $this;
    }

    public function setChild($var,$subvar, $val)
    {
        $this->registry[$var][$subvar] = $val;

        return $this;
    }
    public function addValue($var, $val)
    {
        $this->registry[$var][] = $val;

        return $this;
    }

    // Implement ArrayAccess
    public function offsetExists($var)
    {
        return isset($this->registry[$var]);
    }

    public function offsetGet($var)
    {
        return $this->get($var);
    }

    public function offsetSet($var, $value)
    {
        $this->set($var, $value);
    }

    public function offsetUnset($var)
    {
        unset($this->registry[$var]);
    }
}
