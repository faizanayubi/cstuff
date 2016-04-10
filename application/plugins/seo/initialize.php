<?php

// initialize seo
include("seo.php");

$seo = new SEO(array(
    "title" => "CloudStuff | Cheap Dedicated Servers India with Premium Support",
    "keywords" => "dedicated server, premium support" ,
    "description" => "We provide Cheap Dedicated Servers India with Premium Support only for sites with alexa rank less than 1 Lakh",
    "author" => "CloudStuff",
    "robots" => "INDEX,FOLLOW",
    "photo" => CDN . "images/logo.jpg"
));

Framework\Registry::set("seo", $seo);