@once
@push('tollerus-scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.store('reorderFunctions', {
        positionProp: 'position',
        isFirstItem(parentObj, itemId) {
            let lowest = null;
            for (id in parentObj) {
                if (lowest === null || parentObj[id][this.positionProp] < lowest) {
                    lowest = parentObj[id][this.positionProp];
                }
            }
            return (parentObj[itemId][this.positionProp] == lowest);
        },
        isLastItem(parentObj, itemId) {
            let highest = null;
            for (id in parentObj) {
                if (highest === null || parentObj[id][this.positionProp] > highest) {
                    highest = parentObj[id][this.positionProp];
                }
            }
            return (parentObj[itemId][this.positionProp] == highest);
        },
        getNeighborId(parentObj, itemId, dir) {
            // Normalize input
            if (dir == 0) {
                return null;
            }
            dir = Math.round(dir / Math.abs(dir));
            // Get sorted numeric arrays
            let itemsNumeric = [];
            for (id in parentObj) {
                newItem = {id: id};
                newItem[this.positionProp] = parentObj[id][this.positionProp];
                itemsNumeric.push(newItem);
            }
            itemsNumeric.sort((a, b) => a[this.positionProp] - b[this.positionProp]);
            idsNumeric = itemsNumeric.map(item => item.id);
            // Get numeric indices
            itemIndex = idsNumeric.indexOf(itemId);
            if (itemIndex < 0) {
                return null;
            }
            neighborIndex = itemIndex + dir;
            if (neighborIndex < 0 || neighborIndex >= itemsNumeric.length) {
                return null;
            }
            // Return result
            return idsNumeric[neighborIndex];
        },
        swapItems(itemElem, neighborElem) {
            // Measure
            itemRect = itemElem.getBoundingClientRect();
            neighborRect = neighborElem.getBoundingClientRect();
            // Calculate
            if (itemRect.y > neighborRect.y) {
                // Item is moving upward
                itemMove = neighborRect.y - itemRect.y;
                gap = Math.abs(itemMove) - neighborRect.height;
                neighborMove = itemRect.height + gap;
            } else {
                // Item is moving downward
                neighborMove = itemRect.y - neighborRect.y;
                gap = Math.abs(neighborMove) - itemRect.height;
                itemMove = neighborRect.height + gap;
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
