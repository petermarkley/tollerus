<?php

namespace PeterMarkley\Tollerus\Traits;

trait HasOrderedObjects
{
    public string $positionProp = 'position';

    /**
     * Takes an associative array where the keys are object IDs, with
     * an implicit order based on a certain property. Returns the ID
     * of the given item's neighbor in the specified direction.
     *
     * Position property can be set per Livewire class *and* per
     * function call (in case multiple ordered lists exist on a page
     * which do not share the same position property name--for example
     * 'position' vs. 'order' vs. 'num').
     */
    public function getNeighborId(array $objList, string $itemId, int $dir, string $positionProp = ''): ?string
    {
        // Normalize input
        if (empty($positionProp)) {
            $positionProp = $this->positionProp;
        }
        if ($dir == 0) {
            return null;
        }
        $dir = (int)round($dir/abs($dir));
        // Get sorted numeric array of IDs
        $sorted = collect($objList)
            ->sortBy($positionProp)
            ->map(fn ($i, $k) => $k)
            ->values()
            ->toArray();
        // Get numeric indices of the 2 objects
        $itemIndex = array_search($itemId, $sorted);
        $neighborIndex = $itemIndex + $dir;
        if ($neighborIndex < 0 || $neighborIndex >= count($objList)) {
            return null;
        }
        // We now have the neighbor ID
        return $sorted[$neighborIndex];
    }

    /**
     * Is this the first item according to the implicit ordering?
     */
    public function isFirstItem(array $objList, string $itemId, string $positionProp = ''): bool
    {
        if (empty($positionProp)) {
            $positionProp = $this->positionProp;
        }
        if (!isset($objList[$itemId])) {
            return true;
        }
        $lowest = collect($objList)->min($positionProp);
        return ($objList[$itemId][$positionProp] == $lowest);
    }

    /**
     * Is this the last item according to the implicit ordering?
     */
    public function isLastItem(array $objList, string $itemId, string $positionProp = ''): bool
    {
        if (empty($positionProp)) {
            $positionProp = $this->positionProp;
        }
        if (!isset($objList[$itemId])) {
            return true;
        }
        $highest = collect($objList)->max($positionProp);
        return ($objList[$itemId][$positionProp] == $highest);
    }
}
