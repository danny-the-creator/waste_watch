<div>
    @if ($open)
    <dialog id="edit_dialog" class="absolute top-0 left-0 w-screen h-screen z-30 bg-black/50 flex justify-center items-center">
        <div class="w-4/6 h-4/6 flex flex-col gap-5 max-w-lg">
            <button type="button" class="w-full flex justify-end items-center gap-3 text-white font-button text-xl font-semibold" wire:click="close">Close <i class="fas fa-close text-2xl pr-5 font-semibold"></i></button>
            <div class="bg-white rounded-3xl flex-1 flex p-4">
                <form class="w-full h-full flex flex-col" wire:submit="save">
                    @csrf
                    <div class="w-full pt-2 flex items-center">
                        <i class="fas fa-user text-primary-base text-4xl pl-5"></i>
                        <div class="flex flex-col">
                            <p class="font-bold text-3xl ml-4 text-primary-base">Add User</p>
                        </div>
                    </div>
                    <div class="w-full flex-1 flex flex-col">
                        <label for="name" class="fieldlabel">Full Name</label>
                        <input class="field" type="text" placeholder="John J. Doe" wire:model="name">
                        
                        <label for="email" class="fieldlabel">Email Address</label>
                        <input class="field" type="email" autocomplete="email" placeholder="example@company.com" wire:model="email">
                        
                        <label for="password" class="fieldlabel">Temporary Password</label>
                        <input class="field" type="text" autocomplete="new-password" wire:model="password" disabled>
                        
                        <label for="role" class="fieldlabel">Role</label>
                        <select id="role" wire:model='role' required class="select-none field">
                            <option value="" selected disabled hidden>Select a role</option>
                            <option value="employee">Maintenance Worker</option>
                            <option value="admin">Administrator</option>
                        </select>

                        @if($errors->any())
    {{ implode('', $errors->all('<div>:message</div>')) }}
@endif

                        <i class="text-danger-base text-center pt-2">Be careful assigning the Administrator role! Administrators have complete access to the entire system, including all user access</i>
                    </div>
                    <div class="flex gap-5">
                        <button type="submit" class="accept-button flex-1 text-base">
                            <i class="fas fa-save"></i>
                            Create User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </dialog>
    @endif
</div>

