<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? 'no_title'}} - WasteWatch</title>
        @vite('resources/css/app.css')
    </head>
    <body class="w-full h-full">
        <div class="w-full h-full flex">
            <nav class="shadow-md min-h-screen max-h-screen w-24 flex flex-col justify-between items-center">
                <div class="flex flex-col gap-5 justify-between items-center h-1/2">
                    <div class="w-full p-5">
                        <img src="{{asset('images/WasteWatch.png')}}" alt="WasteWatch">
                    </div>
                    <div class="flex-1 w-full flex flex-col items-center gap-5">
                        <a wire:navigate href="{{route('map')}}" class="text-center w-full hover:text-primary-base hover:active:text-primary-darker rounded-lg transition ease-in-out duration-200">
                            <i class="fas fa-map"></i>
                            <p class="font-button capitalize text-sm">Map</p>
                        </a>
                        @unless($role == 'guest')
                        <a wire:navigate href="{{route('devices')}}" class="text-center w-full hover:text-primary-base hover:active:text-primary-darker rounded-lg transition ease-in-out duration-200">
                            <i class="fas fa-list"></i>
                            <p class="font-button capitalize text-sm">Devices</p>
                        </a>
                        @endunless

                        @if($role == 'admin')
                        <a wire:navigate href="{{route('users')}}" class="text-center w-full hover:text-primary-base hover:active:text-primary-darker rounded-lg transition ease-in-out duration-200">
                            <i class="fas fa-users"></i>
                            <p class="font-button capitalize text-sm">Users</p>
                        </a>
                        <a wire:navigate href="{{route('messages')}}" class="text-center w-full hover:text-primary-base hover:active:text-primary-darker rounded-lg transition ease-in-out duration-200">
                            <i class="fas fa-envelope"></i>
                            <p class="font-button capitalize text-sm">Messages</p>
                        </a>
                        @endif
                    </div>
                </div>
                <div class="aspect-square w-full flex justify-center items-end pb-3 cursor-pointer">
                    @if($role=='guest')
                    <a href="{{route('login')}}" class="flex flex-col font-title">
                        <i class="fas fa-circle-user text-5xl"></i>
                         sign in 
                    </a>
                    @else
                    <a href="{{route('account')}}">
                        <i class="fas fa-circle-user text-5xl"></i>
                    </a>
                    @endif
                </div>
            </nav>
            <div class="min-h-full flex-1">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
