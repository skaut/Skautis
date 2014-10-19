<?php

namespace SkautIS\Nette;

class SkautisQuery {

    public $fname;
    public $args;
    public $trace;
    public $time;
    public $result;

    public function __construct($fname, $args = NULL, $trace = NULL) {
        $this->fname = $fname;
        $this->args = $args;
        $this->trace = $trace;
        $this->time = -microtime(TRUE);
    }

    public function done($result = NULL) {
        $this->result = $result;
        $this->time += microtime(TRUE);
        return $this;
    }

}
