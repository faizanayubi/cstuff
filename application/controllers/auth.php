<?php
/**
 * Description of auth
 *
 * @author Faizan Ayubi
 */
use Shared\Controller as Controller;
use Framework\RequestMethods as RequestMethods;
use Framework\Registry as Registry;

class Auth extends Controller {
    /**
     * @protected
     */
    public function _admin() {
        if (!$this->user->admin) {
            $this->setUser(false);
            throw new Router\Exception\Controller("Not a valid admin user account");
        }
    }

    /**
     * @protected
     */
    public function _session() {
        $user = $this->getUser();
        if ($user) {
            header("Location: /profile.html");
            exit();
        }
    }
    
    protected function _register() {
        $user = new User(array(
            "name" => RequestMethods::post("name"),
            "email" => RequestMethods::post("email"),
            "gender" => RequestMethods::post("gender", ""),
            "fbid" => RequestMethods::post("fbid", ""),
            "live" => 1,
            "admin" => 0
        ));
        if ($user->validate()) {
            $user->save();
        } else {
            throw new \Exception("Error Processing Request");
        }

        return $user;
    }

    public function logout() {
        session_destroy();
        $this->redirect("/index.html");
    }
}
