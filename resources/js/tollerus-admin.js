import Alpine from 'alpinejs';

function registerTollerusAlpineComponents(A) {
    //
}

if (!window.Alpine) {
    window.Alpine = Alpine;
    registerTollerusAlpineComponents(window.Alpine);
    window.Alpine.start();
} else {
    /**
     * This should never happen, because admin pages strictly use
     * a known predictable layout that does not include any Alpine
     * bootstrapping code before this point.
     */
    if (import.meta?.env?.DEV) console.warn('[Tollerus] Alpine is somehow already loaded on admin page (???)');
}
