<?php

namespace AeolusCMS\Libs\Objects\Interfaces;


interface Menuable {
    public function getId();

    public function getTitle();

    public function getUrl();

    public function getImg();

    public function getObjectLangName();
}