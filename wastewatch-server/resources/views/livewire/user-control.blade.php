<div class="flex flex-col pt-20 h-screen pl-10">

    <div class="flex justify-between mb-10 pr-10 pl-4">
        <h1 class="header">Manage Users</h1>
    </div>
    <div class="flex justify-between mb-5 pr-14 pl-4">
        <div class="search">
            <i class="fas fa-search text-primary-base"></i>
            <input type="text" name="search_users" id="search_users" placeholder="Search" wire:model.live="search_query">
        </div>
        <div class="action-button w-44" wire:click="new"><i class="fas fa-plus"></i>Add User</div>
    </div>
    
    <div class="pr-10 flex-1 overflow-hidden flex flex-col">
        <div class="flex-1 overflow-y-scroll">
            <table class="table">
                <tr class="sticky z-10 -top-1">
                    <th><i class="fas fa-hashtag"></i></th>
                    <th>name</th>
                    <th>email</th>
                    <th>role</th>
                    <th>{{--actions --}}</th>
                </tr>
                    @forelse ($this->users as $user)
                        <tr>
                            {{-- ID --}}
                            <td>{{$user->id}}</td>
                            {{-- NAME --}}
                            <td class="font-semibold">{{$user->name}}</td>
                            {{-- EMAIL --}}
                            <td>{{$user->email}}</td>
                            {{-- ROLE --}}
                            {{--<td>
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-angle-down"></i>
                                    @if ($user->role == 'employee')
                                        <div class="text-safe-dark bg-safe-base/20 border border-safe-dark w-fit px-3 rounded-3xl min-w-20 text-center text-sm">
                                            <i class="fas fa-spray-can-sparkles -translate-x-1"></i>
                                            Maintanance Worker
                                        </div>
                                    @elseif ($user->role == 'admin')
                                        <div class="text-primary-dark bg-primary-base/20 border border-primary-dark w-fit px-3 rounded-3xl min-w-20 text-center text-sm">
                                            <i class="fas fa-user-tie -translate-x-1"></i>
                                            Administrator
                                        </div>
                                    @else
                                    <div class="text-danger-dark bg-danger-base/20 border border-danger-dark w-fit px-3 rounded-3xl min-w-20 text-center text-sm">
                                        <i class="fas fa-exclamation-circle -translate-x-2"></i>
                                            Other
                                        </div>
                                    @endif    
                                </div>
                            </td>--}}

                            <td>
                                <select name="" id="" class="py-2 pr-10 border-2 rounded border-table-line" @if($user->id == $authuser->id) disabled @endif>
                                    <option value="employee" @if ($user->role == 'employee') selected @endif>
                                        Maintanance Worker
                                    </option>
                                    <option value="admin" @if ($user->role == 'admin') selected @endif>
                                        Administrator
                                    </option>
                                </select>
                            </td>

                            <td>
                                <div class="flex justify-around items-center">
                                    <button class="font-button rounded pr-3 pl-1 py-1 flex items-center justify-center transition ease-in-out duration-200
                                    @if ($user->id == $authuser->id)
                                        text-gray-400 hover:text-gray-400 active:text-gray-400 cursor-not-allowed
                                    @else
                                        text-warning-base hover:bg-warning-base/10 active:bg-warning-base/30 cursor-pointer
                                    @endif
                                    ">
                                        <i class="fas fa-ban w-4 h-4 mx-2"></i>
                                        Suspend
                                    </button>
                                    <button class="font-button rounded pr-3 pl-1 py-1 flex items-center justify-center transition ease-in-out duration-200
                                    @if ($user->id == $authuser->id)
                                        text-gray-400 hover:text-gray-400 active:text-gray-400 cursor-not-allowed
                                    @else
                                        text-danger-base hover:bg-danger-base/10 active:bg-danger-base/30 cursor-pointer
                                    @endif
                                    ">
                                        <i class="fas fa-trash w-4 h-4 mx-2"></i>
                                        Delete
                                    </button>
                                </div>
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

    <livewire:new-user />

</div>
