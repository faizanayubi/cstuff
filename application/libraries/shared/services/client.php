<?php
namespace Shared\Services;
use Framework\ArrayMethods as ArrayMethods;

/**
 * Simple static class to do client related functions
 */
class Client {
    /**
     * Find the servers ordered by Client
     * @param object $user \Modes\User
     * @param string $orderBy (According to which field keys should be made)
     * @return array
     */
	public static function servers($user, $orderBy = "id", $fields = ["*"]) {
		$servers = \Models\Server::all(["user_id = ?" => $user->id], $fields);

		return self::_orderBy($servers, $orderBy);
	}

    /**
     * Find the services used by the client
     * @param object $user \Modes\User
     * @return array
     */
	public static function services($user) {
		$services = \Models\Service::all(["user_id = ?" => $user->id]);
        $servers = self::servers($user, "service_id", ["id", "service_id"]);

        $results = [];
        foreach ($services as $s) {
            if (strtolower($s->type) == "server") {
                $server_id = $servers[$s->id]->id;
            } else {
                $server_id = null;
            }
            $results[$s->id] = ArrayMethods::toObject([
                "id" => $s->id,
                "server_id" => $server_id,
                "type" => $s->type,
                "price" => $s->price,
                "period" => $s->period,
                "renewal" => $s->renewal,
                "created" => $s->created,
                "live" => $s->live
            ]);
        }
        return $results;
	}

    /**
     * Find the orders for the given user
     */
    public static function orders($user) {
        $orders = \Models\Order::all(["user_id = ?" => $user->id], ["id", "service_id", "modified", "live"]);
        $services = \Models\Service::all(["user_id = ?" => $user->id], ["id", "type", "price"]);

        $results = [];
        foreach ($orders as $o) {
            $s = $services[$o->service_id];
            $d = [
                "id" => $o->id,
                "live" => $o->live,
                "type" => $s->type,
                "service_id" => $o->service_id,
                "updated" => $o->modified,
                "price" => $s->price
            ];
            $results[$o->id] = (object) $d;
        }
        return $results;
    }

	protected static function _orderBy($objects, $field) {
		$results = [];

		if ($field == "id") {
			return $objects;
		}

		foreach ($objects as $o) {
			$results[$o->$field] = $o;
		}
		return $results;
	}
}
