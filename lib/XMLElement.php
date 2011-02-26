<?php

class XMLElement
{
    protected $attribs=array();
    protected $name;
    protected $value;
    protected $debugMode = false;
    protected $properties = array();
    
    public function setDebugMode($debugMode)
    {
        $this->debugMode = $debugMode ? true : false;
    }
    
    public function __construct($name, $attribs)
    {
        $this->setName($name);
        $this->setAttribs($attribs);
    }
    
    public function setAttribs($attribs)
    {
        if (is_array($attribs)) {
            $this->attribs = $attribs;
        }
    }

    public function getAttrib($attrib)
    {
        return isset($this->attribs[$attrib]) ? $this->attribs[$attrib] : null;
    }
    
    public function getAttribs()
    {
        return $this->attribs;
    }
    
    public function setValue($value, $strip_tags=false)
    {
        $this->value = $strip_tags ? strip_tags($value) : html_entity_decode($value);
    }

    public function appendValue($value)
    {
        $this->value .= $value;
    }

    public function setName($name)
    {
        $this->name = $name;
    }
    
    public function name()
    {
        return $this->name;
    }

    public function value()
    {
        return $this->value;
    }
    
    protected function elementMap()
    {
        return array();
    }
    
    protected function standardAttributes()
    {
        return array();
    }
    
    public function getProperty($var)
    {
        if (in_array($var, $this->standardAttributes())) {
            $method = "get" . $var;
            return $this->$method();
        } elseif (array_key_exists(strtoupper($var), $this->properties)) {
            return $this->properties[strtoupper($var)]->value();
        }
    }
    
    public function getChildElement($var)
    {
        if (array_key_exists(strtoupper($var), $this->properties)) {
            return $this->properties[strtoupper($var)];
        }
    }
 
    public function addElement(XMLElement $element)
    {
        $name = $element->name();
        $value = $element->value();
        $map = $this->elementMap();
        
        if (array_key_exists($name, $map)) {
            $this->$map[$name] = $value;
        } elseif (isset($this->properties[$name])) {
            if (!is_array($this->properties[$name])) {
                $this->properties[$name] = array($this->properties[$name]);
            }
            $this->properties[$name][] = $element;
        } else {
            $this->properties[$name] = $element;
        }
    }
    
}
