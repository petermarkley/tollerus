# Benchmarks for Implementation Options in `src/Livewire/PublicWordLookup.php`

Performed on a Spanish verb inflection set (58 forms).

```
array:3 [▼ // vendor/laravel/framework/src/Illuminate/Support/Benchmark.php:70
  "isset per-form scan" => "32.707ms"
  "formIdsByValueId imperative intersect" => "4.990ms"
  "formIdsByValueId collection intersect" => "5.647ms"
]
```

Code:
```
            $lexemeModels = $this->entry->lexemes;
            Benchmark::dd([
                'isset per-form scan' => function () use ($lexemeModels, $primaryNeography) {
                    $lexemes = $lexemeModels->sortBy('position')
                        ->map(function ($lexeme) use ($primaryNeography) {
                            $group = $lexeme->wordClass->group;

                            /**
                             * Precompute a data lookup for faster matching logic:
                             * [
                             *   <formId> => [
                             *     <filterId> => 0,
                             *     <filterId> => 1,
                             *     ...
                             *   ],
                             *   ...
                             * ]
                             * This lets us use `isset()` on a keyed array instead of
                             * `->contains()` on a collection, which offers _noticeably
                             * better performance_ when rendering large inflection
                             * tables (50+ word forms).
                             */
                            $formInflectionValueIds = $lexeme->forms
                                ->mapWithKeys(fn ($f) => [$f->id => $f->inflectionValues->pluck('id')->flip()])
                                ->toArray();

                            $tables = $group->inflectionTables
                                ->where('visible', true)
                                ->sortBy('position')
                                ->map(function ($table) use ($lexeme, $formInflectionValueIds, $primaryNeography) {
                                    $columns = $table->columns
                                        ->where('visible', true)
                                        ->sortBy('position')
                                        ->map(function ($column) use ($lexeme, $formInflectionValueIds, $primaryNeography) {
                                            $rows = $column->rows
                                                ->where('visible', true)
                                                ->sortBy('position')
                                                ->map(function ($row) use ($column, $lexeme, $formInflectionValueIds, $primaryNeography) {
                                                    $filters = $column->filterValues->concat($row->filterValues);
                                                    $form = $lexeme->forms->filter(
                                                        fn ($form) => $filters->reduce(
                                                            fn ($carry, $filter) => $carry && isset($formInflectionValueIds[$form->id][$filter->id]),
                                                            true
                                                        )
                                                    )->first();
                                                    if ($form !== null && $primaryNeography !== null) {
                                                        $formNative = $form->nativeSpellings->firstWhere('neography_id', $primaryNeography->id);
                                                    } else {
                                                        $formNative = null;
                                                    }
                                                    return [
                                                        'model' => $row,
                                                        'form' => $form,
                                                        'formNative' => $formNative,
                                                    ];
                                                })->values();
                                            return [
                                                'model' => $column,
                                                'rows' => $rows,
                                            ];
                                        })->values();
                                    return [
                                        'model' => $table,
                                        'columns' => $columns,
                                    ];
                                })->values();
                            return [
                                'model' => $lexeme,
                                'class' => $lexeme->wordClass,
                                'group' => $group,
                                'tables' => $tables,
                            ];
                        })->values();
                },

                'formIdsByValueId imperative intersect' => function () use ($lexemeModels, $primaryNeography) {
                    $lexemes = $lexemeModels->sortBy('position')
                        ->map(function ($lexeme) use ($primaryNeography) {
                            $group = $lexeme->wordClass->group;

                            /**
                             * These data lookups facilitate a higher-performance
                             * matching algorithm once we start processing each
                             * inflection row. The difference is very noticeable
                             * in the UI for large inflection tables (50+ forms).
                             *
                             * Eloquent model lookup
                             * [
                             *   <formId> => <Form::class>,
                             *   <formId> => <Form::class>,
                             *   ...
                             * ]
                             */
                            $formsById = $lexeme->forms->keyBy('id')->all();
                            /**
                             * Cross-lookup from filter values to forms
                             * [
                             *   <featureValueId> => [
                             *     <formId>,
                             *     <formId>,
                             *     ...
                             *   ]
                             *   ...
                             * ]
                             */
                            $formIdsByValueId = $lexeme->forms
                                ->flatMap(
                                    fn ($f) => $f->inflectionValues
                                        ->pluck('id')
                                        ->map(fn ($vid) => [$vid => $f->id])
                                )->mapToGroups(fn ($pair) => $pair)
                                ->toArray();

                            $tables = $group->inflectionTables
                                ->where('visible', true)
                                ->sortBy('position')
                                ->map(function ($table) use ($lexeme, $formsById, $formIdsByValueId, $primaryNeography) {
                                    $columns = $table->columns
                                        ->where('visible', true)
                                        ->sortBy('position')
                                        ->map(function ($column) use ($lexeme, $formsById, $formIdsByValueId, $primaryNeography) {
                                            $rows = $column->rows
                                                ->where('visible', true)
                                                ->sortBy('position')
                                                ->map(function ($row) use ($column, $lexeme, $formsById, $formIdsByValueId, $primaryNeography) {
                                                    $filters = $column->filterValues->concat($row->filterValues);
                                                    /**
                                                     * Conceptually we are asking "which forms on this
                                                     * lexeme have [such and such] inflection value?"
                                                     *
                                                     * For example if we're rendering English verbs and this
                                                     * is the row for "3rd pers. sing.", the list of
                                                     * column + row filter values is:
                                                     *
                                                     * [finite, present, simple, 3rd person, singular]
                                                     *
                                                     * So we ask:
                                                     *  1. Which verb forms are finite?
                                                     *  2. Which _of those_ are present tense?
                                                     *  3. Which _of those_ are simple aspect?
                                                     * ... and so on.
                                                     *
                                                     * Thus we whittle the list of forms down as we go. We
                                                     * then render the first form that still remains at the
                                                     * end (if there is one).
                                                     *
                                                     * However we will create an optimized filter list for
                                                     * faster comparison:
                                                     *  - It uses our precomputed cross-lookup
                                                     *  - Allows us to rule out non matches earlier
                                                     */
                                                    $filterIds = $filters->pluck('id')
                                                        ->sortBy(fn ($filterId) => count($formIdsByValueId[$filterId] ?? []))
                                                        ->values();

                                                    $candidates = null;
                                                    foreach ($filterIds as $vid) {
                                                        $ids = $formIdsByValueId[$vid] ?? [];
                                                        $candidates = $candidates === null ? $ids : array_values(array_intersect($candidates, $ids));
                                                        if (!$candidates) break;
                                                    }
                                                    $formId = $candidates[0] ?? null;

                                                    if ($formId !== null && $primaryNeography !== null) {
                                                        $form = $formsById[$formId];
                                                        $formNative = $form->nativeSpellings->firstWhere('neography_id', $primaryNeography->id);
                                                    } else {
                                                        $form = null;
                                                        $formNative = null;
                                                    }
                                                    return [
                                                        'model' => $row,
                                                        'form' => $form,
                                                        'formNative' => $formNative,
                                                    ];
                                                })->values();
                                            return [
                                                'model' => $column,
                                                'rows' => $rows,
                                            ];
                                        })->values();
                                    return [
                                        'model' => $table,
                                        'columns' => $columns,
                                    ];
                                })->values();
                            return [
                                'model' => $lexeme,
                                'class' => $lexeme->wordClass,
                                'group' => $group,
                                'tables' => $tables,
                            ];
                        })->values();
                },

                'formIdsByValueId collection intersect' => function () use ($lexemeModels, $primaryNeography) {
                    $lexemes = $lexemeModels->sortBy('position')
                        ->map(function ($lexeme) use ($primaryNeography) {
                            $group = $lexeme->wordClass->group;

                            /**
                             * These data lookups facilitate a higher-performance
                             * matching algorithm once we start processing each
                             * inflection row. The difference is very noticeable
                             * in the UI for large inflection tables (50+ forms).
                             *
                             * Eloquent model lookup
                             * [
                             *   <formId> => <Form::class>,
                             *   <formId> => <Form::class>,
                             *   ...
                             * ]
                             */
                            $formsById = $lexeme->forms->keyBy('id')->all();
                            /**
                             * Cross-lookup from filter values to forms
                             * [
                             *   <featureValueId> => [
                             *     <formId>,
                             *     <formId>,
                             *     ...
                             *   ]
                             *   ...
                             * ]
                             */
                            $formIdsByValueId = $lexeme->forms
                                ->flatMap(
                                    fn ($f) => $f->inflectionValues
                                        ->pluck('id')
                                        ->map(fn ($vid) => [$vid => $f->id])
                                )->mapToGroups(fn ($pair) => $pair)
                                ->toArray();

                            $tables = $group->inflectionTables
                                ->where('visible', true)
                                ->sortBy('position')
                                ->map(function ($table) use ($lexeme, $formsById, $formIdsByValueId, $primaryNeography) {
                                    $columns = $table->columns
                                        ->where('visible', true)
                                        ->sortBy('position')
                                        ->map(function ($column) use ($lexeme, $formsById, $formIdsByValueId, $primaryNeography) {
                                            $rows = $column->rows
                                                ->where('visible', true)
                                                ->sortBy('position')
                                                ->map(function ($row) use ($column, $lexeme, $formsById, $formIdsByValueId, $primaryNeography) {
                                                    $filters = $column->filterValues->concat($row->filterValues);
                                                    /**
                                                     * Conceptually we are asking "which forms on this
                                                     * lexeme have [such and such] inflection value?"
                                                     *
                                                     * For example if we're rendering English verbs and this
                                                     * is the row for "3rd pers. sing.", the list of
                                                     * column + row filter values is:
                                                     *
                                                     * [finite, present, simple, 3rd person, singular]
                                                     *
                                                     * So we ask:
                                                     *  1. Which verb forms are finite?
                                                     *  2. Which _of those_ are present tense?
                                                     *  3. Which _of those_ are simple aspect?
                                                     * ... and so on.
                                                     *
                                                     * Thus we whittle the list of forms down as we go. We
                                                     * then render the first form that still remains at the
                                                     * end (if there is one).
                                                     *
                                                     * However we will create an optimized filter list for
                                                     * faster comparison:
                                                     *  - It uses our precomputed cross-lookup
                                                     *  - Allows us to rule out non matches earlier
                                                     */
                                                    $filterIds = $filters->pluck('id')
                                                        ->sortBy(fn ($filterId) => count($formIdsByValueId[$filterId] ?? []))
                                                        ->values();

                                                    $formId = $filterIds->reduce(
                                                        function ($candidates, $filterId) use ($formIdsByValueId) {
                                                            if ($candidates->isEmpty()) {
                                                                // Set is empty, so don't call `->intersect()` anymore
                                                                return $candidates;
                                                            }
                                                            // Compare, and whittle down the list of forms ...
                                                            return $candidates->intersect($formIdsByValueId[$filterId] ?? []);
                                                        },
                                                        // Start with all forms on the lexeme
                                                        collect(array_keys($formsById))
                                                    )->first(); // Done, now pick the first remaining form

                                                    if ($formId !== null && $primaryNeography !== null) {
                                                        $form = $formsById[$formId];
                                                        $formNative = $form->nativeSpellings->firstWhere('neography_id', $primaryNeography->id);
                                                    } else {
                                                        $form = null;
                                                        $formNative = null;
                                                    }
                                                    return [
                                                        'model' => $row,
                                                        'form' => $form,
                                                        'formNative' => $formNative,
                                                    ];
                                                })->values();
                                            return [
                                                'model' => $column,
                                                'rows' => $rows,
                                            ];
                                        })->values();
                                    return [
                                        'model' => $table,
                                        'columns' => $columns,
                                    ];
                                })->values();
                            return [
                                'model' => $lexeme,
                                'class' => $lexeme->wordClass,
                                'group' => $group,
                                'tables' => $tables,
                            ];
                        })->values();
                },
            ], iterations: 200);
```
