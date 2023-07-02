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

    public function rep($comment = null)
    {
        $attributes = ['user_id' => auth()->id(), 'comment' => $comment];
        if (!$this->reps()->where($attributes)->exists() && !$this->isNegged()) {
            $this->owner->updateReputation(auth()->user());
            return $this->reps()->create($attributes);
        }
    }

    public function neg($comment)
    {
        $attributes = ['user_id' => auth()->id(), 'comment' => $comment];
        if (!$this->negs()->where($attributes)->exists() && !$this->isRepped()) {
            $this->owner->updateReputation(auth()->user(), true);
            return $this->negs()->create($attributes);
        }
    }

    public function unrep()
    {
        $attributes = ['user_id' => auth()->id()];

        $this->reps()->where($attributes)->get()->each->delete();
        $this->owner->updateReputation(auth()->user(), true);
    }

    public function unneg()
    {
        $attributes = ['user_id' => auth()->id()];

        $this->negs()->where($attributes)->get()->each->delete();
        $this->owner->updateReputation(auth()->user());
    }

    public function isRepped()
    {
        return $this->reps->where('user_id', auth()->id())->count();
    }

    public function isNegged()
    {
        return $this->negs->where('user_id', auth()->id())->count();
    }

    public function getIsReppedAttribute()
    {
        return $this->isRepped();
    }

    public function getIsNeggedAttribute()
    {
        return $this->isNegged();
    }

    public function getRepsCountAttribute()
    {
        return $this->reps->count();
    }

    public function getNegsCountAttribute()
    {
        return $this->negs->count();
    }
}
