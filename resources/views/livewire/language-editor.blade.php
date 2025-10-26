<div>
    <h1 class="font-bold text-2xl mb-4">{{ $form['name'] }}</h1>
    <x-tollerus::panel>
        <p>Lorem ipsum dolor sit amet.</p>
        <div>
            <label for="machine_name">Machine-friendly name:</label>
            <input type="text" id="machine_name" wire:model="form.machine_name">
        </div>
        <div>
            <button wire:click="save">Save</button>
        </div>
    </x-tollerus::panel>
</div>
