<?php

namespace App\Factory;

use App\Build\GitfBuild;
use App\Build\XmlBuild;

class BuildFactory{
    public static function make($type) {
        return match (strtolower($type)) {
            "xml" => new XmlBuild(),
            "txt" => new GitfBuild(),
        };
    }
}