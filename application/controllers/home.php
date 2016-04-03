<?php

/**
 * @author Faizan Ayubi, Hemant Mann
 */
use Shared\Controller as Controller;
use Framework\RequestMethods as RequestMethods;
use Framework\Registry as Registry;

class Home extends Controller {

    public function index() {
    	$this->getLayoutView()->set("seo", Framework\Registry::get("seo"));
    	$view = $this->getActionView();

    	$items = Models\Item::all(array("live = ?" => true));
    	$view->set("items", $items);
    }

    public function lead() {
        $this->JSONview();
        $view = $this->getActionView();
        if (RequestMethods::post("action") == "lead") {
            $user = new Models\User(array(
                "name" => RequestMethods::post("name"),
                "email" => RequestMethods::post("email"),
                "phone" => RequestMethods::post("phone"),
                "password" => sha1($pass),
                "admin" => false,
                "live" => 1
            ));
            $user->save();
            $view->set("success", true);
        }
    }


    public function cart($item_id) {
		$this->seo(array("title" => "Cart"));
		$view = $this->getActionView();

        $item = Models\Item::first(array("live = ?" => true));
        if (!$item) {
            $this->redirect("/index.html");
        }

        if (RequestMethods::post("action") == "cart") {
            $user = $this->user;
            if (!$user) {
                $exist = Models\User::first(array("email = ?" => RequestMethods::post("email")));
                if ($exist) {
                    $user = $exist;
                } else {
                    $pass = $this->randomPassword();
                    $user = new Models\User(array(
                        "name" => RequestMethods::post("name"),
                        "email" => RequestMethods::post("email"),
                        "phone" => RequestMethods::post("phone"),
                        "password" => sha1($pass),
                        "admin" => false,
                        "live" => 1
                    ));
                    $user->save();
                    $organization = new Models\Organization(array(
                        "user_id" => $user->id,
                        "name" =>  RequestMethods::post("company"),
                        "address" => RequestMethods::post("address"),
                        "city" => RequestMethods::post("city"),
                        "postalcode" => RequestMethods::post("postalcode"),
                        "country" => RequestMethods::post("country")
                    ));
                    $organization->save();

                    $this->newServer($user, $item);
                }
            } else {
                $organization = Models\Organization::first(array("user_id = ?" => $user->id));
            }

        }
        $view->set("item", $item);
    }

    protected function newServer($user, $item) {
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

        ));
        $invoice->save();

        $bill = new Models\Bill(array(
            
        ));
        $bill->save();

        $order = new Models\Order(array(
            
        ));
        $order->save();
    }

}
