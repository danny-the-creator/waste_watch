<?php

namespace App\Console\Commands;

use App\Http\Controllers\ApiHandler;
use Illuminate\Console\Command;
use PhpMqtt\Client\Facades\MQTT;
use PhpMqtt\Client\MqttClient;

class MqttListener extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mqtt:subscribe';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Subscribe to the MQTT `wastewatch/+` topic';
        
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $mqtt = MQTT::connection();

        $apiHandler = new ApiHandler(mqttClient: $mqtt);

        $mqtt->subscribe('wastewatch/#', function($topic, $message) use ($apiHandler) {
            $this->info("[ $topic ] $message\n");

            match ($topic) {
                'wastewatch/hello' => $apiHandler->hello($message),
                'wastewatch/update' => $apiHandler->update($message),
                default => null,
            };
        }, MqttClient::QOS_EXACTLY_ONCE);
        $mqtt->loop(true);
    }
}
