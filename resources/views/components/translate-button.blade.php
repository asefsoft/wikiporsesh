@props([
    'getStatePath'
])

<div
    x-data="{
        state: $wire.entangle('{{ $getStatePath() }}'),

        doTranslate() {
            doGoogleTranslate(this.state)
                .then((response) => {
                    this.state = response;
                    sendNotification('ترجمه شد');
                })
                .catch((response) => {
                    sendNotification('در عملیات ترجمه خطا رخ داد', 'error');
                    console.log('err catched in alphine', response);
                });
        }
     }"
>
    <x-forms::button
            {{-- Translate on server--}}
            {{-- :wire:click="'dispatchFormEvent(\'advanced-text-input::doTranslate\', \'' . $getStatePath() . '\')'"--}}

            {{-- Translate on clinet server--}}
            x-on:click="doTranslate();"

            size="sm"
            type="button"
            style="position: relative; float: inline-end; margin-right: 12px; margin-bottom: 5px;"
    >
        ترجمه
    </x-forms::button>
</div>
