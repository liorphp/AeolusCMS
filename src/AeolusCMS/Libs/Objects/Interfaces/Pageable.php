<?php

namespace AeolusCMS\Libs\Objects\Interfaces;


interface Pageable {
    public function getId();

    public function getTitle();

    public function getUrl();

    public function getImg();

    public function generateMeta();

    public function showAsPage();

    public function pageController();

    public function getObjectName();

    public function getShortContent();

    public function generateBreadcrumbs();
}