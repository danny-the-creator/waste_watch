<div class="flex flex-col pt-20 h-screen pl-10">

    <div class="flex justify-between mb-10 pr-10 pl-4">
        @if($role == 'guest')
        <h1 class="header">WasteWatch Map</h1>
        @else
        <h1 class="header">Welcome, <span class="text-black">{{$name}}</span></h1>
        <p class="text-title font-title tracking-wider text-sm pr-6 translate-y-10">Showing <span class="font-semibold">0</span> trashcans</p>
        @endif
    </div>
    <div class="flex justify-between mb-3 pr-14 pl-4 items-end">
        <div class="search">
            <i class="fas fa-search text-primary-base"></i>
            <input type="text" name="search_location" id="search_location" placeholder="Search">
        </div>
        <div class="w-72 flex flex-col justify-between items-end relative">
            <form class="segmented-tabs w-full h-8">
                <input type="radio" id="all" name="filter" checked>
                <label for="all">
                    All
                </label>
                <input type="radio" id="full" name="filter">
                <label for="full">
                    Full
                </label>
                <input type="radio" id="not_full" name="filter">
                <label for="not_full">
                    Not Full
                </label>
            </form>
        </div>
    </div>

    <div class="flex-1 mr-10 mb-8 bg-blue-200 flex flex-col justify-center items-center text-3xl rounded-lg">
        {{-- <i class="fas fa-map"></i> --}}
        {{-- No map --}}
        <iframe src="https://www.google.com/maps/d/u/0/embed?mid=1UvNQtvsH8CHXzOz67H7jYJ37EMh3bks&ehbc=2E312F&noprof=1" width="640" height="480" class="w-full h-full rounded-lg"></iframe>
    </div>
    
</div>
