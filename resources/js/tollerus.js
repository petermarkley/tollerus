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
     * Alpine already loaded. If this happens, it's because
     * we're on an atypical host-app-defined page layout,
     * which also means we don't need our Alpine components
     * because those are only for admin pages. So there's
     * nothing to do.
     */
    if (import.meta?.env?.DEV) {
        console.warn('[Tollerus] Alpine already loaded. Public page assumed, therefore skipping admin-only Alpine component registration.');
    }
}
