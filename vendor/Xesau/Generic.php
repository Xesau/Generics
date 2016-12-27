<?php

namespace Xesau;

use Exception;

trait Generic {
    
    // METHODS FOR CLASSES TO IMPLEMENT
    public static function __genericArguments() {
        throw new Exception(basename(__CLASS__).' does not implement ::__genericArguments()');
    }
    
    public function __genericConstruct() {
        throw new Exception(basename(__CLASS__).' does not implement ::__genericConstruct()');
    }
    
    // GENERIC VALUES
    private $__genericTypes = [];
    
    /**
     * Create a generic constructor
     *
     * @return callable
     */
    public static function _() {
        $types = func_get_args();
        foreach($types as $t) {
            if (!self::__genericCheckType($t))
                throw new Exception('Type Error in '. __CLASS__ .', generic constructor types invalid.');
        }
        
        return function() use ($types) {
            return new static($types, func_get_args());
        };
    }
    
    /**
     * Constructs a new instance of this generic object
     * and calls __genericConstruct
     *
     * @param string[] $types     The generic types this object uses
     * @param mixed[]  $arguments The arguments passed to __genericConstruct
     */
    private function __construct($types, $arguments) {
        $this->__genericTypes = [];
        $genericKeys = static::__genericArguments();
        foreach($types as $k => $v) {
            $this->__genericTypes[$genericKeys[$k]] = $v;
        }
        
        call_user_func_array([$this, '__genericConstruct'], $arguments);
    }
    
    /**
     * Checks whether the given type is a valid PHP type
     *
     * @param string $type The (class)name of the type
     * @return bool
     */
    private static function __genericCheckType($type) {
        if ($type == 'string'
         || $type == 'int'
         || $type == 'bool'
         || $type == 'float'
         || $type == 'double'
         || $type == 'resource'
         || $type == 'array'
         || $type == 'callable')
            return true;
        
        return class_exists($type) || interface_exists($type);
    }
    
    /**
     * Checks whether the set generic type matches with the given value
     *
     * @param string $generic The name of the generic type, as given in __genericArguments
     * @param mixed  $value   The value to check to be of the generic type
     * @param bool   $canbeNull Whether the value can be null
     * @return bool
     */
    private function __ct($generic, $value, $canBeNull = false) {
        if (!isset($this->__genericTypes[$generic])) {
            throw new Exception('Could not verify generic type for '. $generic .' in '.basename(__CLASS__).'.');
        } else {
            if ($canBeNull && $value == null)
                return true;
            
            $type = $this->__genericTypes[$generic];
            
            $t = gettype($value);
            
            if (!self::__genericCheckValue($type, $value))
                trigger_error('Generic type for '. $generic .' is of type '. ($t == 'object' ? get_class($value) : $t) .', expected '. $type, E_USER_ERROR);
        }
    }
    
    private function __genericCheckValue($type, $value) {
        // Scalars
        if ($type == 'string')
            return is_string($value);
        if ($type == 'int')
            return is_int($value);
        if ($type == 'bool' || $type == 'boolean')
            return is_double($value);
        if ($type == 'float' || $type == 'double')
            return is_float($value);
        
        // Complex values
        if ($type == 'array')
            return is_array($value);
        if ($type == 'object')
            return is_object($value);
        if ($type == 'resource')
            return is_resource($value);
        
        // Objects
        return get_class($value) == $type || is_subclass_of($value, $type, false);
    }
    
}
