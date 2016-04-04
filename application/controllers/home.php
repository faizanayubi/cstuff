<?php

/**
 * @author Faizan Ayubi, Hemant Mann
 */
use Shared\Controller as Controller;
use Framework\RequestMethods as RequestMethods;
use Framework\Registry as Registry;
use \Curl\Curl;

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

    protected function _pay($value) {
        $configuration = Registry::get("configuration");
        $imojo = $configuration->parse("configuration/payment");
        $curl = new Curl();
        $curl->setHeader('X-Api-Key', $imojo->payment->instamojo->key);
        $curl->setHeader('X-Auth-Token', $imojo->payment->instamojo->auth);
        $curl->post('https://www.instamojo.com/api/1.1/payment-requests/', array(
            "purpose" => "Advertisement",
            "amount" => $amount,
            "buyer_name" => $this->user->name,
            "email" => $this->user->email,
            "phone" => $this->user->phone,
            "redirect_url" => "http://clicks99.com/finance/success",
            "allow_repeated_payments" => false
        ));

        $payment = $curl->response;
        if ($payment->success == "true") {
            $instamojo = new Instamojo(array(
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

    public function success() {
        $this->seo(array("title" => "Thank You", "view" => $this->getLayoutView()));
        $view = $this->getActionView();
        $configuration = Registry::get("configuration");
        $payment_request_id = RequestMethods::get("payment_request_id");

        if ($payment_request_id) {
            $instamojo = Instamojo::first(array("payment_request_id = ?" => $payment_request_id));

            if ($instamojo) {
                $imojo = $configuration->parse("configuration/payment");
                $curl = new Curl();
                $curl->setHeader('X-Api-Key', $imojo->payment->instamojo->key);
                $curl->setHeader('X-Auth-Token', $imojo->payment->instamojo->auth);
                $curl->get('https://www.instamojo.com/api/1.1/payment-requests/'.$payment_request_id.'/');
                $payment = $curl->response;

                $instamojo->status = $payment->payment_request->status;
                if ($instamojo->status == "Completed") {
                    $instamojo->live = 1;
                }
                $instamojo->save();

                $user = User::first(array("id = ?" => $instamojo->user_id));

                $account->balance += $instamojo->amount;
                $account->save();

                $this->notify(array(
                    "template" => "accountCredited",
                    "subject" => "Payment Received",
                    "user" => $user,
                    "transaction" => $transaction
                ));
            }

        }
    }

}
