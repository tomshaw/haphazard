<?php
/**
 * CRYPT_BLOWFISH – Blowfish hashing with a salt as follows: “$2a$”, a two digit cost parameter,
 * “$”, and 22 base64 digits from the alphabet “./0-9A-Za-z”. Using characters outside of this
 * range in the salt will cause crypt() to return a zero-length string. The two digit cost parameter
 * is the base-2 logarithm of the iteration count for the underlying Blowfish-based hashing algorithmeter
 * and must be in range 04-31, values outside this range will cause crypt() to fail.
 */
class Plugin_PasswordHash
{
    private $_base64Digits = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz./';
    
    private $_maxDigits = 22;
    
    public function generate($password, $cost = 10)
    {
        return crypt($password, $this->salt($cost));
    }
    
    public function validate($password, $hash)
    {
        return (crypt($password, $hash) == $hash);
    }
    
    protected function random()
    {
        $out   = '';
        $max = strlen($this->_base64Digits) - 1;
        for ($i = 0; $i < $this->_maxDigits; $i++) {
            $out .= $this->_base64Digits[rand(0, $max)];
        }
        return $out;
    }
    
    protected function salt($cost)
    {
        if (!Zend_Validate::is($cost, 'Between', array('min' => 4,'max' => 31))) {
            throw new Zend_Exception('The cost hashing algorithmeter must be in range 04-31');
        }
        return '$2a$' . $cost . '$' . $this->random();
    }
}