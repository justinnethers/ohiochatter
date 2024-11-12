<?php

use App\Livewire\EditPost;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(EditPost::class)
        ->assertStatus(200);
});
