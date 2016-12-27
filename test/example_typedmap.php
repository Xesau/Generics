<?php

include_once 'Generic.php';

class TypedMap implements Iterator, Countable {
    use Xesau\Generic;

    private $map;
    private $keyMap;
    
    public static function __genericArguments() {
        return ['Key', 'Value'];
    }
    
    public function __genericConstruct() {
        $this->map = [];
        $this->keyMap = [];
    }
    
    public function add($key, $value, $replace = false) {
        $this->__ct('Key', $key);
        $this->__ct('Value', $value);
        
        if ($this->containsKey($key)) {
            if ($replace !== true) {
                throw new InvalidArgumentException('An entry with key '. self::getName($key) .' already exists.');
            }
        }
        
        $keyName = self::getName($key);
        $this->map[$keyName] = $value;
        $this->keyMap[$keyName] = $key;
    }
    
    public function containsKey($key) {
        $this->__ct('Key', $key);
        
        return array_key_exists(self::getName($key), $this->map);
    }
    
    public function containsValue($value) {
        $this->__ct('Value', $value);
        
        return in_array($value, $this->map);
    }
    
    public function get($key) {
        $this->__ct('Key', $key);
        
        if ($this->containsKey($key)) {
            return $this->map[self::getName($key)];
        } else {
            throw DomainException('No entry with key '. $key);
        }
    }
    
    public function findKeys($value) {
        $this->__ct($value);
        
        $keys = array_keys($this->map, $value);
        $return = [];
        foreach($keys as $k)
            $return[] = $this->keyMap[$k];
        return $return;
    }
    
    private function getName($key) {
        return print_r($key, true);
    }
    
    // ITERATOR FUNCTIONS
    public function current() {
        return current($this->map);
    }
    
    public function next() {
        next($this->map);
    }
    
    public function key() {
        return $this->keyMap[key($this->map)];
    }
    
    public function rewind() {
        reset($this->map);
    }
    
    public function valid() {
        return key($this->map) !== null;
    }
    
    public function count() {
        return count($this->map);
    }    
}

$map = call_user_func(TypedMap::_('A', 'B'));

class A {}
class B {}
class B2 extends B {}

$map->add(new A(), new B());
$map->add(new A(), new B2(), true);

print_r($map->get(new A()));