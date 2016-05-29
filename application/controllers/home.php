<?php

/**
 * @author Faizan Ayubi, Hemant Mann
 */
use Framework\RequestMethods as RequestMethods;
use Framework\Registry as Registry;
use \Curl\Curl;

class Home extends Auth {

    public function index() {
    	$this->getLayoutView()->set("seo", Framework\Registry::get("seo"));
    	$view = $this->getActionView();

        if (RequestMethods::post("action") == "lead") {
            $view->set("message", "Your Query has been submitted");
        }

    	$items = Models\Item::all(array("live = ?" => true), array("*"), "price", "asc", 5, 1);
    	$view->set("items", $items);
    }

    public function plans() {
        $this->seo(array("title" => "Cheap and Best Dedicated Server Plans"));
        $view = $this->getActionView();

        $items = Models\Item::all(array("live = ?" => true));
        $view->set("items", $items);
    }

    public function features() {
        $this->seo(array("title" => "CloudStuff Features"));
        $view = $this->getActionView();
    }

    public function contact() {
        $this->seo(array("title" => "Contact"));
        $view = $this->getActionView();
    }

    public function cart($item_id) {
		$this->seo(array("title" => "Cart"));$user = $this->user;
		$view = $this->getActionView();$session = Registry::get("session");

        $item = Models\Item::first(array("live = ?" => true, "id = ?" => $item_id));
        if (!$item) {
            $this->redirect("/index.html");
        }

        if (RequestMethods::post("action") == "cart") {
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
                    $this->setUser($user);
                    $session->set("organization", $organization);

                    $this->notify(array(
                        "template" => "newAccount",
                        "subject" => "Your Account Information",
                        "user" => $user,
                        "pass" => $pass,
                        "organization" => $organization
                    ));
                }
            }
            $order = $this->_server($user, $item);
            if ($item->price) {
                $url = $this->_pay($user, $item, $order);
            } else {
                $url = "/success.html";
            }
            $this->redirect($url);
        }
        $view->set("item", $item);
        $view->set("user", $user);
    }

    public function success() {
        $this->seo(array("title" => "Thank You", "view" => $this->getLayoutView()));
        $view = $this->getActionView();
        $configuration = Registry::get("configuration");
        $payment_request_id = RequestMethods::get("payment_request_id");

        if ($payment_request_id) {
            $instamojo = Models\Instamojo::first(array("payment_request_id = ?" => $payment_request_id));

            if (!$instamojo) return;

            $imojo = $configuration->parse("configuration/payment");
            $curl = new Curl();
            $curl->setHeader('X-Api-Key', $imojo->payment->instamojo->key);
            $curl->setHeader('X-Auth-Token', $imojo->payment->instamojo->auth);
            $curl->get('https://www.instamojo.com/api/1.1/payment-requests/'.$payment_request_id.'/');
            $payment = $curl->response;

            $instamojo->status = $payment->payment_request->status;
            if ($instamojo->status == "Completed") {
                $instamojo->live = 1;

                $order = Models\Order::first(array("id = ?" => $instamojo->order_id));
                $invoice = Models\Invoice::first(array("order_id = ?" => $order->id));
                $service = Models\Service::first(array("id = ?" => $order->service_id));

                $order->live = 1; $invoice->live = 1; $service->live = 1;
                $order->save(); $order->save(); $service->save();
            }
            $instamojo->save(); // since amount is paid update order and invoice

            $user = Models\User::first(array("id = ?" => $instamojo->user_id));

            $this->notify(array(
                "template" => "paidInvoice",
                "subject" => "Invoice Paid",
                "user" => $user,
                "instamojo" => $instamojo
            ));
        }
    }

    public function products() {
        $this->noview();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=products.csv');

        $output = fopen('php://output', 'w');
        fputcsv($output, array("id", "availability", "condition", "description", "image_link", "link", "title", "price", "product_type", "brand",  "google_product_category"));
        $items = Models\Item::all(array("live = ?" => true));
        foreach ($items as $item) {
            fputcsv($output, array(
                $item->id,
                "in stock",
                "new",
                $item->disk ." with ". $item->processor .", including ". $item->bandwidth .", ". $item->ips,
                "http://cloudstuff.tech/public/assets/images/logo.jpg",
                "http://cloudstuff.tech/cart/".$item->id.".html",
                $item->ram .", ". $item->plan,
                $item->price. " INR",
                "Dedicated Server",
                $item->plan,
                "Electronics > Computers > Computer Servers"
            ));
        }
    }

    public function termsofservice() {
        $this->seo(array("title" => "Terms of Service", "view" => $this->getLayoutView()));
        $view = $this->getActionView();
    }

    public function notfound() {
        $this->seo(array("title" => "Not Found", "view" => $this->getLayoutView()));
        $view = $this->getActionView();
    }
}
