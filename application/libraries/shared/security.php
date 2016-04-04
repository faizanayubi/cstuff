<?php

/**
 * @author Faizan Ayubi
 */

namespace Shared;

use Framework\Events as Events;
use Framework\Router as Router;
use Framework\Registry as Registry;
use \Curl\Curl;

class Security {

	protected function randomPassword() { 
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 8; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    }

    protected function reCaptcha() {
        $g_recaptcha_response = RequestMethods::post("g-recaptcha-response");
        $curl = new Curl();
        $curl->post('https://www.google.com/recaptcha/api/siteverify', array(
            'secret' => '6LfRZRQTAAAAABxnjW_9e6x_BgzVc_b2ghnxmE8D',
            'response' => $g_recaptcha_response
        ));
        return $curl->response->success;
    }

}