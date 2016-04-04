<?php
/**
 * Description of auth
 *
 * @author Faizan Ayubi
 */
use Shared\Controller as Controller;
use Framework\RequestMethods as RequestMethods;
use Framework\Registry as Registry;
use \Curl\Curl;
use Models\User as User;

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
    public function _secure() {
        if (!$this->user) {
            $this->redirect("/404");
        }
    }

    /**
     * @protected
     */
    public function _session() {
        $user = $this->getUser();
        if ($user) {
            $this->redirect("/client");
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
        $this->redirect("/");
    }

    protected function _server($user, $item) {
        $service = new Models\Service(array(
            "user_id" => $user->id,
            "item_id" => $item->id,
            "period" => 30,
            "price" => $item->price,
            "type" => "SERVER",
            "renewal" => strftime("%Y-%m-%d", strtotime('+30 day'))
        ));
        $service->save();

        $server = new Models\Server(array(
            "user_id" => $user->id,
            "item_id" => $item->id,
            "os" => RequestMethods::post("os"),
            "user" => "",
            "pass" => ""
        ));
        $server->save();

        $invoice = new Models\Invoice(array(
            "user_id" => $user->id,
            "amount" => $item->price,
            "duedate" => strftime("%Y-%m-%d", strtotime('now')),
            "ref" => ""
        ));
        $invoice->save();

        $bill = new Models\Bill(array(
            "user_id" => $user->id,
            "item_id" => $item->id,
            "invoice_id" => $invoice->id
        ));
        $bill->save();

        $order = new Models\Order(array(
            "user_id" => $user->id,
            "service_id" => $service->id
        ));
        $order->save();
    }

    protected function _pay($user, $item) {
        $configuration = Registry::get("configuration");
        $imojo = $configuration->parse("configuration/payment");
        $curl = new Curl();
        $curl->setHeader('X-Api-Key', $imojo->payment->instamojo->key);
        $curl->setHeader('X-Auth-Token', $imojo->payment->instamojo->auth);
        $curl->post('https://www.instamojo.com/api/1.1/payment-requests/', array(
            "purpose" => "Advertisement",
            "amount" => $item->amount,
            "buyer_name" => $user->name,
            "email" => $user->email,
            "phone" => $user->phone,
            "redirect_url" => "http://cloudstuff.tech/success.html",
            "allow_repeated_payments" => false
        ));

        $payment = $curl->response;
        if ($payment->success == "true") {
            $instamojo = new Models\Instamojo(array(
                "user_id" => $this->user->id,
                "payment_request_id" => $payment->payment_request->id,
                "amount" => $payment->payment_request->amount,
                "status" => $payment->payment_request->status,
                "longurl" => $payment->payment_request->longurl,
                "live" => 0
            ));
            $instamojo->save();
            $view->set("success", true);
            $view->set("payurl", $instamojo->longurl);
        }
    }

    /**
     * @before _session
     */
    public function login() {
        $this->seo(array("title" => "Login"));
        $view = $this->getActionView(); $session = Registry::get("session");

        if (RequestMethods::post("action") == "login") {
            $email = RequestMethods::post("email");
            $pass = RequestMethods::post("password");

            $user = User::first(["email = ?" => $email, "live = ?" => true]);
            if (!$user) {
                $view->set("message", 'Invalid email/password');
                return;
            }
            if (sha1($pass) === (string) $user->password) {
                $this->setUser($user);
                $org = Models\Organization::first(["user_id = ?" => $this->user->id]);
                $session->set("organization", $org);
                $this->redirect("/client");
            } else {
                $view->set("message", 'Invalid email/password');
                return;
            }
        }
    }

    /**
     * @before _session
     */
    public function forgotpassword() {
        $this->seo(array("title" => "Forgot Password"));
        $view = $this->getActionView();

        if (RequestMethods::post("action") == "reset" && $this->reCaptcha()) {
            $message = $this->_resetPassword();
            $view->set("message", $message);
        }
    }

    /**
     * @before _session
     */
    public function resetpassword($token) {
        $this->seo(array("title" => "Forgot Password"));
        $view = $this->getActionView();

        $meta = Meta::first(array("value = ?" => $token, "property = ?" => "resetpass"));
        if (!isset($meta)) {
            $this->redirect("/index.html");
        }

        if (RequestMethods::post("action") == "change" && $this->reCaptcha()) {
            $user = User::first(array("id = ?" => $meta->user_id));
            if(RequestMethods::post("password") == RequestMethods::post("cpassword")) {
                $user->password = sha1(RequestMethods::post("password"));
                $user->save();
                $meta->delete();
                $view->set("message", 'Password changed successfully now <a href="/login.html">Login</a>');
            } else{
                $view->set("message", 'Password Does not match');
            }
        }
    }

    protected function authorize($user) {
        $session = Registry::get("session");
        //setting organization
        $organization = Models\Organization::first(array("user_id = ?" => $user->id));
        if ($organization) {
            $this->setUser($user);
            $session->set("organization", $organization);
            $this->redirect("/client/index.html");
        }
    }

    protected function _resetPassword() {
        $exist = User::first(array("email = ?" => RequestMethods::post("email")), array("id", "email", "name"));
        if ($exist) {
            $meta = new Meta(array(
                "user_id" => $exist->id,
                "property" => "resetpass",
                "value" => uniqid()
            ));
            $meta->save();
            $this->notify(array(
                "template" => "forgotPassword",
                "subject" => "New Password Requested",
                "user" => $exist,
                "meta" => $meta
            ));

            return "Password Reset Email Sent Check Your Email. Check in Spam too.";
        } else {
            return "User doesnot exist.";
        }
    }

    /**
     * @before _secure, _admin
     */
    public function loginas($user_id) {
        $session = Registry::get("session");
        $session->set("admin_user_id", $user_id);
        $this->setUser(false);
        $user = User::first(array("id = ?" => $user_id));
        $this->authorize($user);
    }
}
