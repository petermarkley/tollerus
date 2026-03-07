<?php

namespace PeterMarkley\Tollerus\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * NOTE: There is technical debt here!
 *
 * This trait is deceptively named because I was still learning Livewire
 * when I wrote it. I did not understand that public Livewire properties
 * were serialized and then only partly rehydrated on each request cycle,
 * and I actually imagined this strategy might save some DB queries. I
 * also believed it would help consolidate dereference and error-checking
 * code, leading to less repetition.
 *
 * In reality, if the model needs a DB query on every Livewire request no
 * matter what we do, then basically we can do Model::findOrFail($id) and
 * move on, and most of this code becomes superfluous anyway. Even some
 * of the method parameters that are getting passed from the front end JS
 * listeners become unnecessary.
 *
 * So eventually, this trait may become obsolete and be removed. But I'm
 * not prepared for a refactor at that scale right now. So for now, it
 * stays.
 *
 * A related but different issue is that in the Livewire Blade view, I
 * was entangling JS variables and using Alpine directives to build out
 * the dynamic page content, rather than Blade directives on the PHP
 * variables themselves. Again, this is because I was still learning
 * Livewire and mistakenly believed that a Blade `@foreach()` would be
 * baked into the page and not re-run until the next full page reload.
 *
 * If I could do it over, I would build the Admin Livewire pages with a
 * less wonky architecture. By the time I built PublicWordLookup I had
 * learned better, so it uses Blade directives as appropriate. But it was
 * too late for the admin pages, and refactoring them is going to be an
 * even bigger task than getting rid of the `HasModelCache` trait because
 * it's going to touch `reorder-script.blade.php` and everything, and all
 * the reording UI behaviors will have to be re-tested.
 *
 * Someday, but not today.
 *
 * -- Peter Markley 2026/03/07
 */
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
