<div class="flex flex-col pt-20 h-screen pl-10">
    <div class="flex justify-between mb-10 pr-10 pl-4">
        <h1 class="header">Manage Trashcans</h1>
    </div>
    <div class="flex justify-between mb-5 pr-14 pl-4">
        <div class="search">
            <i class="fas fa-search text-primary-base"></i>
            <input type="text" name="search_trashcans" id="search_trashcans" placeholder="Search" wire:model.live="search_query">
        </div>
        <div class="action-button w-44" wire:click="new"><i class="fas fa-plus"></i>Add Trashcan</div>
    </div>
    
    <div class="pr-10 flex-1 overflow-hidden flex flex-col">
        <div class="flex-1 overflow-y-scroll">
            
            <table class="table" wire:poll.200ms>
                <tr class="sticky z-10 -top-1">
                    <th><i class="fas fa-hashtag"></i></th>
                    <th>tag</th>
                    <th>location</th>
                    <th>fill state</th>
                    <th>lid state</th>
                    <th>{{--actions--}}</th>
                </tr>
                @forelse ($this->trashcans as $trashcan)
                    <tr wire:key="{{$trashcan->id}}">
                        {{-- ID --}}
                        <td>{{$trashcan->id}}</td>
                        {{-- TAG --}}
                        <td class="font-bold">{{$trashcan->tag}}</td>
                        {{-- LOCATION --}}
                        <td>
                            <a class="cursor-pointer"><i class="fas fa-location-dot text-xl mr-2"></i>{{$trashcan->location}}</a>
                        </td>
                        {{-- STATUS --}}
                        <td>
                        @if ($trashcan->fill_level == 0)
                            <div class="text-safe-base">
                                <i class="fas fa-battery-quarter -rotate-90 text-xl"></i>
                                Empty
                            </div>
                        @elseif ($trashcan->fill_level == 1)
                            <div class="text-warning-base">
                                <i class="fas fa-battery-half -rotate-90 text-xl"></i>
                                Halfway
                            </div>
                        @else
                            <div class="text-danger-base">
                                <i class="fas fa-battery-full -rotate-90 text-xl"></i>
                                Full
                            </div>
                        @endif    
                        </td>
                        {{-- LID BLOCK --}}
                        <td>
                        @if($trashcan->lid_blocked == 0)
                            <div class="text-safe-dark bg-safe-base/20 border border-safe-dark w-fit px-3 rounded-3xl min-w-20 text-center text-sm">
                                Active
                            </div>
                        @else
                            <div class="text-danger-dark bg-danger-base/20 border border-danger-dark w-fit px-3 rounded-3xl min-w-20 text-center text-sm">
                                Blocked
                            </div>
                        @endif
                        </td>
                        <td>
                            <button class="font-button rounded pr-3 pl-1 py-1 cursor-pointer flex items-center justify-center text-primary-base hover:bg-primary-base/10 hover:active:bg-primary-base/30 transition ease-in-out duration-200"
                                wire:click="edit({{$trashcan->id}})">
                                <i class="fas fa-pen w-4 h-4 mx-2"></i>
                                edit
                            </button>
                        </td>
                    </tr>

                @empty
                    <tr>
                        <td>
                            No data found
                        </td>
                    </tr>
                @endforelse
            </table>
            <div x-intersect.full="$wire.loadMoreEntries()" class="w-full flex justify-center">
                <div wire:loading wire:target="loadMoreEntries">
                    Loading more...
                </div>
            </div>
        </div>
    </div>
    <livewire:edit-trashcan />
    <livewire:new-trashcan />
</div>
