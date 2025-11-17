<?php

namespace PeterMarkley\Tollerus\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

trait HasModelCache
{
    private function findInCache(string $failEvent, array $steps, ?string $domId = ''): Model|null
    {
        $collection = collect($this->{$this->cacheRoot});
        foreach ($steps as $step) {
            $model = $collection->firstWhere('id', (int)$step['id']);
            if (!($model instanceof $step['objectType'])) {
                $this->dispatch($failEvent, id: $domId);
                throw \Illuminate\Validation\ValidationException::withMessages($step['failMessage']);
                return null;
            }
            if (isset($step['relation'])) {
                $collection = $model->{$step['relation']};
            } else {
                return $model;
            }
        }
    }
}
