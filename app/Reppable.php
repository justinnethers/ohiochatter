<?php

namespace App;


use App\Models\Neg;
use App\Models\Rep;
use App\Models\User;

trait Reppable
{

    protected static function bootReppable()
    {
        static::deleting(function ($model) {
            $model->reps->each->delete();
            $model->negs->each->delete();
        });
    }

    public function reps()
    {
        return $this->morphMany(Rep::class, 'repped');
    }

    public function negs()
    {
        return $this->morphMany(Neg::class, 'negged');
    }

    public function rep()
    {
        $attributes = ['user_id' => auth()->id()];

        if ($this->isReppedBy(auth()->user())) {
            $this->reps()->where($attributes)->get()->each->delete();
            return;
        }

        if ($this->isNeggedBy(auth()->user())) {
            $this->negs()->where($attributes)->get()->each->delete();
        }

        return $this->reps()->create($attributes);
    }

    public function neg()
    {
        $attributes = ['user_id' => auth()->id()];

        if ($this->isNeggedBy(auth()->user())) {
            $this->negs()->where($attributes)->get()->each->delete();
            return;
        }

        if ($this->isReppedBy(auth()->user())) {
            $this->reps()->where($attributes)->get()->each->delete();
        }

        return $this->negs()->create($attributes);
    }

    public function isReppedBy(User $user)
    {
        return $this->reps->where('user_id', $user->id)->count();
    }

    public function isNeggedBy(User $user)
    {
        return $this->negs->where('user_id', $user->id)->count();
    }
}
