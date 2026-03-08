@once
@push('tollerus-scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.store('reorderFunctions', {
        swapItems(itemElem, neighborElem) {
            // Measure
            let itemRect = itemElem.getBoundingClientRect();
            let neighborRect = neighborElem.getBoundingClientRect();
            // Calculate
            if (itemRect.y > neighborRect.y) {
                // Item is moving upward
                var itemMove = neighborRect.y - itemRect.y;
                var gap = Math.abs(itemMove) - neighborRect.height;
                var neighborMove = itemRect.height + gap;
            } else {
                // Item is moving downward
                var neighborMove = itemRect.y - neighborRect.y;
                var gap = Math.abs(neighborMove) - itemRect.height;
                var itemMove = neighborRect.height + gap;
            }
            // Begin animation
            itemElem.style.transform = `translateY(${itemMove}px)`;
            neighborElem.style.transform = `translateY(${neighborMove}px)`;
            // After animation is over ...
            const onDone = (event) => {
                // Listener should be ephemeral
                event.target.removeEventListener('transitionend', onDone);
                // Disable CSS animations
                itemElem.classList.add('transition-none');
                neighborElem.classList.add('transition-none');
                // Remove transform
                itemElem.style.removeProperty('transform');
                neighborElem.style.removeProperty('transform');
                void itemElem.offsetWidth; // Force re-flow
                // Update position on same frame, ahead of Alpine
                let storedPosition = itemElem.style.order;
                itemElem.style.order = neighborElem.style.order;
                neighborElem.style.order = storedPosition;
                void itemElem.offsetWidth; // Force re-flow
                // Wait for repaint, then re-enable CSS animations
                requestAnimationFrame(() => {
                    itemElem.classList.remove('transition-none');
                    neighborElem.classList.remove('transition-none');
                });
            };
            itemElem.addEventListener('transitionend', onDone);
        },
    });
});
</script>
@endpush
@endonce
