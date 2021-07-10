<?php

namespace DiscourseConnect\Request;

trait DiscourseRequest {
    public static function getRequest(array $reqs){
        return self::getRequestByClass($reqs, self::class);
    }

    public function getValue($name){
        return $this->$name;
    }
}