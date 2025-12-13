<x-tollerus::panel id="tabpanel-font" role="tabpanel" x-cloak x-show="tab=='font'" class="flex flex-col gap-6">
    @foreach(\PeterMarkley\Tollerus\Enums\FontFormat::cases() as $fontFormat)
        <div class="flex flex-col gap-4">
            <h3 class="font-bold text-lg">{{ $fontFormat->localizeFormat() }}</h3>
            <div>Lorem ipsum dolor sit amet</div>
        </div>
    @endforeach
</x-tollerus::panel>
