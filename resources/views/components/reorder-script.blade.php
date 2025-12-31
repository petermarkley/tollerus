@once
@push('tollerus-scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.store('reorderFunctions', {
        positionProp: 'position',
        sortItems(parentObj) {
            return Object.entries(parentObj).sort((a, b) => {
                return a[1][this.positionProp] - b[1][this.positionProp];
            });
        },
        isFirstItem(parentObj, itemId) {
            if (typeof parentObj[itemId] === "undefined") {
                return true;
            }
            let lowest = null;
            for (let id in parentObj) {
                if (lowest === null || parentObj[id][this.positionProp] < lowest) {
                    lowest = parentObj[id][this.positionProp];
                }
            }
            return (parentObj[itemId][this.positionProp] == lowest);
        },
        isLastItem(parentObj, itemId) {
            if (typeof parentObj[itemId] === "undefined") {
                return true;
            }
            let highest = null;
            for (let id in parentObj) {
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
            for (let id in parentObj) {
                let newItem = {id: id};
                newItem[this.positionProp] = parentObj[id][this.positionProp];
                itemsNumeric.push(newItem);
            }
            itemsNumeric.sort((a, b) => a[this.positionProp] - b[this.positionProp]);
            let idsNumeric = itemsNumeric.map(item => item.id);
            // Get numeric indices
            let itemIndex = idsNumeric.indexOf(itemId);
            if (itemIndex < 0) {
                return null;
            }
            let neighborIndex = itemIndex + dir;
            if (neighborIndex < 0 || neighborIndex >= itemsNumeric.length) {
                return null;
            }
            // Return result
            return idsNumeric[neighborIndex];
        },
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
