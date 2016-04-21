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

        public function nice_number($n) {
            // first strip any formatting;
            $n = (0+str_replace(",", "", $n));

            // is this a number?
            if (!is_numeric($n)) return false;

            // now filter it;
            if ($n > 1000000000000) return round(($n/1000000000000), 2).'T';
            elseif ($n > 1000000000) return round(($n/1000000000), 2).'B';
            elseif ($n > 1000000) return round(($n/1000000), 2).'M';
            elseif ($n > 1000) return round(($n/1000), 2).'K';

            return number_format($n);
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