<?php

namespace AC\MarkdownContentBundle\Routing;

use Symfony\Component\Routing\RouteCollection;

class SerializableRouteCollection extends RouteCollection implements \Serializable {

    public function serialize() {
        $data = array();
        foreach($this as $key => $val) {
            $data[$key] = $val;
        }
        return $data;
    }
    
    public function unserialize($data) {
        foreach($data as $key => $val) {
            $this->$key = $val;
        }
    }
}