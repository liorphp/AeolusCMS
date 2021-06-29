<?php
namespace AeolusCMS\Libs\Objects\Interfaces;


interface  Searchable {

    public function getId();

    public function getTitle();

    public function getShortContent();

    public function getUrl();

    public function getThumbUrl($public);

    public function getStatus();

    public function isAvailable();
}