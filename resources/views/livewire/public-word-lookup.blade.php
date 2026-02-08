<div class="mx-auto mt-4 w-full max-w-[1200px] flex flex-col gap-4 items-start">
    @if($languages->count() == 0)
        <div class="w-full flex flex-col gap-4 items-center">
            <p class="text-center text-tollerus-text">{{ __('tollerus::ui.no_data_notice') }}</p>
            <a href="{{ route('tollerus.admin.index') }}" class="px-4 py-2 rounded-lg shadow font-bold bg-tollerus-primary hover:bg-tollerus-primary-hover text-tollerus-text-inverse">{{ __('tollerus::ui.admin_area') }}</a>
        </div>
    @else
        <x-tollerus::public.nav-main currentPage="word_lookup" :langCount="$languages->count()"/>
        <div class="w-full flex flex-col gap-4 bg-tollerus-surface rounded-lg shadow-lg p-6 text-tollerus-text">
            <div>Lorem ipsum doolor sit amet. <a href="#" class="text-tollerus-primary hover:text-tollerus-primary-hover font-bold">Click Here</a></div>
            <div class="flex flex-row gap-4 items-center justify-start">
                <button class="appearance-none cursor-pointer px-4 py-2 rounded-lg shadow font-bold bg-tollerus-secondary hover:bg-tollerus-secondary-hover text-tollerus-text-inverse">Cancel</button>
                <button class="appearance-none cursor-pointer px-4 py-2 rounded-lg shadow font-bold bg-tollerus-primary hover:bg-tollerus-primary-hover text-tollerus-text-inverse">Submit</button>
            </div>
        </div>
    @endif
</div>
