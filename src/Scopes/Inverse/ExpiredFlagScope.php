<?php

/*
 * This file is part of Laravel Eloquent Flag.
 *
 * (c) Anton Komarev <a.komarev@cybercog.su>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cog\Flag\Scopes\Inverse;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Class ExpiredFlagScope.
 *
 * @package Cog\Flag\Scopes\Inverse
 */
class ExpiredFlagScope implements Scope
{
    /**
     * All of the extensions to be added to the builder.
     *
     * @var array
     */
    protected $extensions = ['Unexpire', 'Expire', 'WithExpired', 'WithoutExpired', 'OnlyExpired'];

    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply(Builder $builder, Model $model)
    {
        if (method_exists($model, 'shouldApplyExpiredFlagScope') && !$model->shouldApplyExpiredFlagScope()) {
            return $builder;
        }

        return $builder->where('is_expired', 0);
    }

    /**
     * Extend the query builder with the needed functions.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return void
     */
    public function extend(Builder $builder)
    {
        foreach ($this->extensions as $extension) {
            $this->{"add{$extension}"}($builder);
        }
    }

    /**
     * Add the `unexpire` extension to the builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return void
     */
    protected function addUnexpire(Builder $builder)
    {
        $builder->macro('unexpire', function (Builder $builder) {
            $builder->withExpired();

            return $builder->update(['is_expired' => 0]);
        });
    }

    /**
     * Add the `expire` extension to the builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return void
     */
    protected function addExpire(Builder $builder)
    {
        $builder->macro('expire', function (Builder $builder) {
            return $builder->update(['is_expired' => 1]);
        });
    }

    /**
     * Add the `withExpired` extension to the builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return void
     */
    protected function addWithExpired(Builder $builder)
    {
        $builder->macro('withExpired', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });
    }

    /**
     * Add the `withoutExpired` extension to the builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return void
     */
    protected function addWithoutExpired(Builder $builder)
    {
        $builder->macro('withoutExpired', function (Builder $builder) {
            return $builder->withoutGlobalScope($this)->where('is_expired', 0);
        });
    }

    /**
     * Add the `onlyExpired` extension to the builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return void
     */
    protected function addOnlyExpired(Builder $builder)
    {
        $builder->macro('onlyExpired', function (Builder $builder) {
            return $builder->withoutGlobalScope($this)->where('is_expired', 1);
        });
    }
}
