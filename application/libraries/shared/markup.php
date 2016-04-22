<?php

/**
 * Description of markup
 *
 * @author Faizan Ayubi
 */

namespace Shared {

    class Markup {

        public function __construct() {
            // do nothing
        }

        public function __clone() {
            // do nothing
        }

        public static function errors($array, $key, $separator = "<br />", $before = "<br />", $after = "") {
            if (isset($array[$key])) {
                $html = '<span class="help-block" style="color: red;">';
                $html .= $before . join($separator, $array[$key]) . $after;
                $html .= '</span>';
                return $html;
            }
            return "";
        }

        public static function pagination($page) {
            if (strpos(URL, "?")) {
                $request = explode("?", URL);
                if (strpos($request[1], "&")) {
                    parse_str($request[1], $params);
                }

                $params["page"] = $page;
                return $request[0]."?".http_build_query($params);
            } else {
                $params["page"] = $page;
                return URL."?".http_build_query($params);
            }
            return "";
        }

        public static function models() {
            $model = array();
            $path = APP_PATH . "/application/models";
            $iterator = new \DirectoryIterator($path);

            foreach ($iterator as $item) {
                if (!$item->isDot()) {
                    array_push($model, substr($item->getFilename(), 0, -4));
                }
            }
            return $model;
        }

        public function nice_number($n, $opts = []) {
            // first strip any formatting;
            $n = (0+str_replace(",", "", $n));

            // is this a number?
            if (!is_numeric($n)) return false;
            $prefix = false;
            if (isset($opts['currency'])) {
                $currency = $opts["currency"];
                if (strtolower($currency) == "usd") {
                    $n = (float) ($n / 66);
                    $prefix = '<i class="fa fa-usd"></i> ';
                } else {
                    $prefix = '<i class="fa fa-inr"></i> ';
                }
            }

            // now filter it;
            $num = false;
            if ($n > 1000000000000) $num = round(($n/1000000000000), 2).'T';
            elseif ($n > 1000000000) $num = round(($n/1000000000), 2).'B';
            elseif ($n > 1000000) $num = round(($n/1000000), 2).'M';
            elseif ($n > 1000) $num = round(($n/1000), 2).'K';
            if ($num !== false) {
                if ($prefix) $num = $prefix . $num;
                return $num;
            }

            if (is_float($n)) $n = number_format($n, 2);
            else $n = number_format($n);

            if ($prefix !== false) {
                return $prefix . $n;
            }
            return $n;
        }

        public static function websiteRegex() {
            $regex = "((https?|ftp)\:\/\/)"; // SCHEME
            $regex .= "([a-z0-9+!*(),;?&=\$_.-]+(\:[a-z0-9+!*(),;?&=\$_.-]+)?@)?"; // User and Pass
            $regex .= "([a-z0-9-.]*)\.([a-z]{2,4})"; // Host or IP
            $regex .= "(\:[0-9]{2,5})?"; // Port
            $regex .= "(\/([a-z0-9+\$_-]\.?)+)*\/?"; // Path
            $regex .= "(\?[a-z+&\$_.-][a-z0-9;:@&%=+\/\$_.-]*)?"; // GET Query
            $regex .= "(#[a-z_.-][a-z0-9+\$_.-]*)?"; // Anchor

            return $regex;
        }

    }

}