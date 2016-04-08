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
        $items = \Models\Item::all([], ["plan", "id"]);
        $servers = self::servers($user, "service_id", ["ips", "id", "service_id"]);

        $results = [];
        foreach ($services as $s) {
            $item = $items[$s->item_id];
            $server = $servers[$s->id];
            $results[$s->id] = ArrayMethods::toObject([
                "id" => $s->id,
                "name" => $item->plan,
                "ips" => $server->ips,
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
     * Find the 
     */
    public static function orders($user) {
        $orders = \Models\Order::all(["user_id = ?" => $user->id], ["id", "service_id", "modified", "live"]);
        $services = self::services($user);

        $results = [];
        foreach ($orders as $o) {
            $s = $services[$o->service_id];
            unset($s->id); unset($s->live);
            $d = [
                "id" => $o->id,
                "live" => $o->live,
                "service_id" => $o->service_id,
                "updated" => $o->modified
            ];
            $results[$o->id] = (object) array_merge($d, (array) $s);
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
