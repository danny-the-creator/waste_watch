<div>
    @isset ($trashcan)
    <dialog id="edit_dialog" class="absolute top-0 left-0 w-screen h-screen z-30 bg-black/50 flex justify-center items-center">
        <div class="w-4/6 h-4/6 flex flex-col gap-5">
            <button type="button" class="w-full flex justify-end items-center gap-3 text-white font-button text-xl font-semibold" wire:click="discard">Close <i class="fas fa-close text-2xl pr-5 font-semibold"></i></button>
            <div class="bg-white rounded-3xl flex-1 flex">

                <div class="w-5/12 px-5 py-4">
                    <form class="w-full h-full flex flex-col" wire:submit="save">
                        @csrf
                        <div class="w-full h-1/6 flex items-center">
                            <i class="fas fa-trash text-primary-base text-4xl pl-5"></i>
                            <div class="flex flex-col">
                                <input class="font-button font-bold text-3xl outline-none bg-transparent ml-4 w-5/6 border-b border-primary-base" type="text" wire:model="tag" autofocus placeholder="tag">
                                
                                <p class="ml-4 italic">Identifier: #{{$trashcan?->id}}</p>
                            </div>
                        </div>
                        <div class="w-full flex-1 flex flex-col">
                            <label for="location" class="fieldlabel">Location</label>
                            <input class="field" type="text" placeholder="location" wire:model="location">

                            <div class="flex justify-between pt-6 px-4">
                                <label class="checkbox" for="lock_lid">
                                    <input type="checkbox" id="lock_lid" wire:model="lid_blocked">
                                    <span><i class="fas fa-check"></i></span>
                                    <p>Lock trash lid</p>
                                </label>
                                <label class="checkbox" for="lock_servicing_lid">
                                    <input type="checkbox" id="lock_servicing_lid" wire:model="service_lid_blocked">
                                    <span><i class="fas fa-check"></i></span>
                                    <p>Lock servicing lid</p>
                                </label>
                            </div>
                            <label for="description" class="fieldlabel">Description</label>
                            <textarea class="field" rows="2" wire:model="description" placeholder="some more information about this device"></textarea>
                        </div>
                        <div class="flex gap-5">
                            <button type="submit" class="accept-button flex-1 text-base">
                                <i class="fas fa-save"></i>
                                Save changes
                            </button>
                        </div>
                        <button type="button" class="font-button rounded pr-3 pl-1 py-1 cursor-pointer flex items-center justify-center text-danger-base hover:bg-danger-base/10 hover:active:bg-danger-base/30 transition ease-in-out duration-200"
                                wire:click="delete({{$trashcan->id}})">
                                <i class="fas fa-trash w-4 h-4 mx-2"></i>
                                delete
                        </button>
                    </form>
                </div>
                <div class="w-7/12 flex flex-col justify-center items-center text-3xl rounded-r-3xl overflow-hidden">
                    {{-- <i class="fas fa-map"></i> --}}
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1443.9423481322558!2d6.8582138046163115!3d52.237641675332!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x47b813dcdf3a65f5%3A0xb85f149cfb212505!2sHorsttoren%201100!5e0!3m2!1snl!2snl!4v1730817602158!5m2!1snl!2snl" width="600" height="500" style="border:0; width:100%; height:100%;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
            </div>
        </div>
    </dialog>
    @endisset
</div>

