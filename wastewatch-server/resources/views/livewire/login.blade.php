    <div class="flex justify-center items-center w-full h-full">

        @error('authorization')
            <div class="text-white bg-danger-base absolute top-5 rounded-lg flex items-center">
                <i class="fas fa-circle-exclamation text-2xl px-5"></i>
                <p class="py-4 pr-2">You are not authorized to see that page!</p>
                <button type="button" class="h-full py-4 rounded-r-lg px-3 hover:bg-danger-dark active:bg-danger-darker" wire:click="close_error">
                    <i class="fas fa-close"></i>
                </button>
            </div>
        @enderror

        <div class="w-1/2 max-w-md min-w-[20rem]">
            <form wire:submit="login" class="flex flex-col items-center">
                @csrf                
                <h1 class="font-title font-bold text-4xl mb-5">Sign in</h1>
                <div class="w-full flex flex-col">
                    <label 
                        class="fieldlabel" 
                        for="email"
                    >Email</label>
                    <input 
                        class="field @error('email') border border-danger-base @enderror "
                        wire:model="email" 
                        type="email" 
                        id="email" 
                        placeholder="example@mail.com" 
                        autocomplete="email"
                    required autofocus>
                    @error('email') <span class="text-danger-base pl-2">{{ $message }}</span> @enderror
                </div>

                <div class="w-full flex flex-col">
                    <label 
                        class="fieldlabel" 
                        for="password"
                    >Password</label>
                    <input 
                        class="field @error('password') border border-danger-base @enderror "
                        wire:model="password" 
                        type="password" 
                        id="password" 
                        placeholder="Enter at least 8 characters" 
                        required 
                        autocomplete="current-password">
                    @error('password') <span class="text-danger-base pl-2">{{ $message }}</span> @enderror
                </div>
                <div class="w-full flex justify-between pt-4">
                    <label for="remember" class="checkbox">
                        <input wire:model="remember_password" type="checkbox" id="remember">
                        <span><i class="fas fa-check"></i></span>
                        <p>Remember me</p>
                    </label>
                    <a class="text-primary-base pr-2 cursor-pointer hover:underline transition-all ease-in-out duration-200" wire:click.prevent="forgot_password">Forgot password?</a>
                </div>

                <div class="w-full pt-6">
                    <button type="submit" class="accept-button">Sign in</button>
                </div>
            </form>
        </div>
    </div>