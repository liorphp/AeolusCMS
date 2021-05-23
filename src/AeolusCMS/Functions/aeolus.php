<?php

function url() {
    echo APP_URL;
}

function _t($name) {
    return $name;
}

function roundUpToAny($n,$x=5) {
    return round($n/$x) * $x;
}