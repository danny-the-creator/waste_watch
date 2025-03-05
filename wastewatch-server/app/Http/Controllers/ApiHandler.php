<?php

namespace App\Http\Controllers;

use App\Models\ClientKey;
use App\Models\Trashcan;
use App\Services\KeypairService;
use Illuminate\Routing\Controller;
use Log;
use PhpMqtt\Client\Facades\MQTT;
use PhpMqtt\Client\MqttClient;
use Str;

class ApiHandler extends Controller
{
    private KeypairService $keypairService;
    private MqttClient $mqttClient;
    
    public function __construct(KeypairService $keypairService = null, MqttClient $mqttClient = null){
        $this->keypairService = $keypairService ?? new KeypairService();
        $this->mqttClient = $mqttClient ?? MQTT::connection();
    }
 
    /**
     * Handle an incoming Hello-message. This message is used by trashcans to make themselves known to the server.
     * @param string $json
     * @param \PhpMqtt\Client\MqttClient $mqtt
     * @return void
     */
    public function hello(string $json) {
        $data = json_decode($json);
        
        // Check for Bad Request
        if (!isset($data->public_key) || !isset($data->device_id) || !isset($data->response_on)) {
            return $this->send_response($data->response_on, json_encode(['code' => 400]));
        }

        ApiClient::purge_client_keys();

        // Create or update a client_key entry. Public key cannot be updated once the trashcan is registered.
        $client_key = ClientKey::where(['device_id'=>$data->device_id])->latest()->first();

        if ($client_key == null) {
            $client_key = ClientKey::create([
                'device_id' => $data->device_id,
                'public_key' => $data->public_key,
            ]);
        } elseif ($client_key->trashcan_id == null) {
            $client_key->update([
                'public_key' => $data->public_key,
            ]);
        }

        // send your own public key
        $public_key = base64_encode($this->keypairService->generateOrGet());

        $response_data = [
            'code' => 200, 
            'public_key' => $public_key
        ];
        $this->send_response($data->response_on, json_encode($response_data));
        
        if($client_key?->trashcan_id != null) {
            ApiClient::send_registered($client_key->device_id);
        }
    }

    public function update(string $json) {
        $data = json_decode($json);

        $response_id = Str::random(128);
        $response_data['response_id'] = $response_id;
        $response_data['signature'] = $this->keypairService->sign($response_id);

        # Check 1: is it a good request? (no missing information)
        if (!isset($data->signature) || !isset($data->request_id) || !isset($data->device_id) || !isset($data->response_on)) {
            $response_data['code'] = 400;
            return $this->send_response($data->response_on, json_encode($response_data));
        }

        # Check 2: is the device registered?
        $client_key = ClientKey::where(['device_id' => $data->device_id])->whereNotNull('trashcan_id')->first();
        if ($client_key == null) {
            $response_data['code'] = 401;
            return $this->send_response($data->response_on, json_encode($response_data));
        }

        # Check 3: is the signature valid?
        if (!$this->keypairService->verify($data->request_id, $data->signature, $client_key->public_key)) {
            $response_data['code'] = 403;
            return $this->send_response($data->response_on, json_encode($response_data));
        }

        # The message is valid.
        $trashcan = $client_key->trashcan;
        foreach ($data->body as $key => $value) {
            switch ($key) {
                case 'trash_level':
                    if (in_array($value, [0, 1, 2])) {
                        $trashcan->fill_level = $value;
                        break;
                    }
                    Log::error("found 'trash_level' but incorrect value");
                    // fallthrough
                case 'lid_jammed':
                    if (in_array($value, [false, true])) {
                        $trashcan->lid_jammed = $value;
                        break;
                    }
                    Log::error("found 'lid_jammed' but incorrect value");
                    // fallthrough
                default:
                    Log::error("Unknown key " . $key . " or incorrect value " . $value);
                    $response_data['code'] = 400;
                    return $this->send_response($data->response_on, message: json_encode($response_data));
            }
        }
        Log::info("Trashcan before save: " . json_encode($trashcan->toArray()));
        if (!$trashcan->save()) {
            Log::error("Failed to save trashcan: " . json_encode($trashcan->getErrors()));
        }

        $response_data['code'] = 200;
        $this->send_response($data->response_on, json_encode($response_data));
    }

    private function send_response($topic, $message) {
        Log::info('Sending response to ' . $topic . ': ' . $message);
        if ($topic == null) return;
        try {
            $this->mqttClient->publish($topic, $message, MqttClient::QOS_EXACTLY_ONCE);
        } catch (\Exception $e) {
            Log::error('Error publishing response: ' . $e->getMessage());
        }
    }
}
