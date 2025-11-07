<?php

namespace PeterMarkley\Tollerus\Actions;

use Illuminate\Database\Eloquent\Model;

class CreateWithUniqueName
{
    /**
     * This is for DB tables that have a non-nullable field
     * with a unique constraint, when we are not prompting
     * the user for an input value first. In that situation
     * we need a placeholder value that's unique or else the
     * insert will fail.
     */
    public static function handle(
        int $startNum,
        \Closure $createFunc,
        string $base = '',
        int $maxAttempts = 20,
    ): Model|null
    {
        if (empty($base)) {
            $base = __('tollerus::ui.untitled');
        }
        $num = $startNum;
        for ($i=0; $i < $maxAttempts; $i++) {
            $tryNum = $num + $i;
            $tryName = $i==0 ? $base : $base . " ({$tryNum})";
            try {
                return $createFunc($tryName);
            } catch (\Illuminate\Database\QueryException $e) {
                /**
                 * If this isn't a `unique` constraint violation, then
                 * something else is wrong and we need to surface the error.
                 */
                $sqlState = $e->getCode();
                $driverCode = $e->errorInfo[1] ?? null;
                if (!($sqlState === '23000' && $driverCode === 1062)) {
                    throw $e;
                }
            }
        }
        throw new \RuntimeException(__('tollerus::error.max_attempts_adding_unique_name'));
    }
}
