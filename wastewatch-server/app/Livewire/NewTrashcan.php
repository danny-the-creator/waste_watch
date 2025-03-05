<?php

namespace App\Livewire;

use App\Http\Controllers\ApiClient;
use App\Models\ClientKey;
use App\Models\Trashcan;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

class NewTrashcan extends Component
{
    public bool $open = false;

    public array $available = [];

    #[Validate('required')]
    public ?string $tag = '';
    #[Validate('required')]
    public ?string $location = '';

    public ?string $description = '';

    #[Validate('required')]
    public ?string $device = '';

    #[On("new-trashcan")]
    public function openModal() {
        $this->open = true;
    }

    public function save() {
        $this->validate();
        $trashcan = Trashcan::create([
            'tag' => $this->tag,
            'location' => $this->location,
            'description' => $this->description,
        ]);
        $client_key = ClientKey::where(['id' => intval($this->device)]);
        $client_key->update([
            'trashcan_id' => $trashcan->id
        ]);

        ApiClient::send_registered($client_key->first()->device_id);         

        $this->open = false;
        $this->reset('tag', 'location', 'description', 'device');
        $this->dispatch('refresh-table');
    }

    public function close() {
        $this->open = false;
    }

    #[Computed()]
    public function availableTrashcans() {
        return ClientKey::whereNull('trashcan_id')
            ->orderBy('device_id')
            ->get(['id', 'device_id']);
    }



    public function render()
    {
        ApiClient::purge_client_keys();
        return view('livewire.new-trashcan');
    }
}
