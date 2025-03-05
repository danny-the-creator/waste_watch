<?php

namespace App\Http\Controllers;

use App\Models\ClientKey;
use App\Models\Trashcan;
use App\Services\KeypairService;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PhpMqtt\Client\Facades\MQTT;
use PhpMqtt\Client\MqttClient;

class ApiClient extends Controller
{
	private static MqttClient $mqttClient;

	public static function send_registered(string $device_id) {
		$topic = 'wastewatch/%s/registered';
		$response_topic = 'wastewatch/response/registered';

		try {
			$request_data = ['response_on' => $response_topic];
			self::send_request(sprintf($topic, $device_id), $request_data, $response_topic);
		} catch (\Exception $e) {
			Log::error("Failed to register trashcan: " . $e->getMessage());
		}
	}

	public static function forget_device(string $device_id) {
		$topic = 'wastewatch/%s/forget';
		$response_topic = null;

		try {
			$request_data = ['response_on' => $response_topic];
			self::send_request(sprintf($topic, $device_id), $request_data, $response_topic);
		} catch (\Exception $e) {
			Log::error("Failed to forget trashcan: " . $e->getMessage());
		}
	}

	public static function send_action(string $device_id, array $actions) {
		$topic = 'wastewatch/%s/action';
		$response_topic = 'wastewatch/response/action';

		try {
			$request_data = [
				'response_on' => $response_topic,
				'body' => $actions,
			];

			self::send_request(sprintf($topic, $device_id), $request_data, $response_topic);
		} catch (\Exception $e) {
			Log::error("Error sending action: " . $e->getMessage());
		}
	}


	private static function send_request(string $topic, array $data, string $response_topic = null) {
		$mqttClient = MQTT::connection();
		$request_id = Str::random(128);
		$data['request_id'] = $request_id;
		$data['signature'] = app(KeypairService::class)->sign($request_id);

		Log::info('Sending request to ' . $topic . ': ' . json_encode($data));
		$mqttClient->publish($topic, json_encode($data), MqttClient::QOS_EXACTLY_ONCE);
		$mqttClient->loop(true, true);
		// TODO: $mqttClient->subscribe($response_topic, function($topic, $message) {});

		Log::info("Published to '" . $topic . "': " . json_encode($data));
	}

	/**
	 * Delete any stored client key that has not been registered to a trashcan and also hasn't been updated for at least 15 seconds.
	 * @return void
	 */
	public static function purge_client_keys(): void {
		// try {
		// 	ClientKey::where('updated_at', '<', Carbon::now()->subSeconds(15))
		// 		->whereNull('trashcan_id')->delete();
			
		// 	Log::info("Purged old unregistered client keys");
		// } catch (\Exception $e) {
		// 	Log::error("Error deleting inactive client key records: " . $e->getMessage());
		// }
	}
}