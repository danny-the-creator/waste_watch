<div class="flex flex-col pt-20 h-screen pl-10">

    <div class="flex justify-between mb-10 pr-10 pl-4">
        <h1 class="header">Messages</h1>
    </div>
    <div class="flex justify-between mb-5 pr-14 pl-4">
        <div class="search">
            <i class="fas fa-search text-primary-base"></i>
            <input type="text" name="search_users" id="search_users" placeholder="Search">
        </div>
    </div>
    
    <div class="pr-10 flex-1 overflow-hidden flex flex-col">
        <div class="flex-1 overflow-y-scroll flex flex-col gap-5 flex-nowrap">
            <table class="table table-fixed w-11/12 border-spacing-y-2 border-spacing-x-0 border-separate">
            @foreach ($messages as $message)
                <tr class="!h-20">
                    {{-- RESULT --}}
                    @switch($message['result'])
                        @case('danger')
                            <td class="w-10 !p-0">
                                <div class="w-full h-full flex justify-center items-center bg-danger-base/50 rounded-l-lg">
                                    <i class="fas fa-circle-exclamation text-danger-darker text-2xl"></i>
                                </div>
                            </td>
                            @break
                        @case('safe')
                            <td class="w-10 !p-0">
                                <div class="w-full h-full flex justify-center items-center bg-safe-base/50 rounded-l-lg">
                                    <i class="fas fa-circle-check text-safe-darker text-2xl"></i>
                                </div>
                            </td>
                            @break
                        @default
                            <td class="w-10 !p-0">
                                <div class="w-full h-full flex justify-center items-center bg-primary-base/50 rounded-l-lg">
                                    <i class="fas fa-circle-info text-primary-darker text-2xl"></i>
                                </div>
                            </td>
                    @endswitch 
                    <td class="!p-0">
                        <div class="bg-message-bg flex-1 h-full pl-2">
                            <div class="flex h-1/2 flex-col">
                                <p class="font-title text-xs text-message-secondary pt-1 tracking-wide">{{date('H:i - j M', $message['timestamp'])}}</p>
                                @if($message['username'])<p class="">{{--seperator--}}</p>@endif
                                <p class="font-title text-lg tracking-wider">{{$message['username']}}</p>
                            </div>
                            <div>
                                {{
                                    $message['trashcan']
                                        ? sprintf($message['message'], $message['trashcan']['tag'], $message['trashcan'][' id'])
                                        : $message['message']
                                }}
                            </div>
                        </div>
                    </td>
                    <td class="!p-0 w-40">
                        <div class="flex justify-around items-center w-full h-full bg-message-bg rounded-r-lg">
                            <button class="font-button rounded pl-1 p3-1 py-1 cursor-pointer flex items-center justify-center text-primary-base hover:bg-primary-base/10 hover:active:bg-primary-base/30 transition ease-in-out duration-200">
                                View device
                                <i class="fas fa-external-link w-4 h-4 mx-2"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            @endforeach
            </table>

            <details>
                <summary>
                    Raw
                </summary>
                <pre>{{json_encode($messages, JSON_PRETTY_PRINT)}}</pre>
            </details>
        </div>
    </div>    
</div>
