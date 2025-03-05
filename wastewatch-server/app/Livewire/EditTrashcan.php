<?php

namespace App\Livewire;

use App\Http\Controllers\ApiClient;
use App\Models\Log;
use App\Models\Trashcan;
use Auth;
use DateTime;
use Livewire\Attributes\On;
use Livewire\Component;

class EditTrashcan extends Component
{

    public ?Trashcan $trashcan;

    public ?string $tag;
    public ?string $location;
    public ?bool $lid_blocked;
    public ?bool $service_lid_blocked;
    public ?string $description;

    #[On("edit-trashcan")]
    public function openModal($trashcan_id) {
        $this->trashcan = Trashcan::find($trashcan_id);
        $this->tag = $this->trashcan->tag;
        $this->location = $this->trashcan->location;
        $this->lid_blocked = $this->trashcan->lid_blocked;
        $this->service_lid_blocked = $this->trashcan->service_lid_blocked;
        $this->description = $this->trashcan->description;
    }
    
    public function save() {
        $this->trashcan->update([
            'tag' => $this->tag,
            'location' => $this->location,
            'lid_blocked' => $this->lid_blocked,
            'service_lid_blocked' => $this->service_lid_blocked,
            'description' => $this->description,
        ]);

        ApiClient::send_action($this->trashcan->client_key->device_id, [
            'block_lid' => $this->lid_blocked,
            'lock_service_lid' => $this->service_lid_blocked,
        ]);

        Log::create([
            'user_id' => Auth::user()->id,
            'trashcan_id' => $this->trashcan->id,
            'action' => "Updated trashcan",
            'timestamp' => now(),
        ]);

        unset($this->trashcan, $this->tag, $this->location, $this->lid_blocked, $this->description);
        $this->dispatch('refresh-table');
    }

    public function delete(int $id) {
        $trashcan = Trashcan::find($id);
        $device_id = $trashcan->client_key->device_id;
        ApiClient::forget_device($device_id);
        Trashcan::where(['id'=>$id])->delete();
        $this->discard();
        $this->dispatch('refresh-table');
    }

    public function discard() {
        unset($this->trashcan, $this->tag, $this->location, $this->lid_blocked, $this->description);
    }


    public function render()
    {
        return view('livewire.edit-trashcan');
    }
}
