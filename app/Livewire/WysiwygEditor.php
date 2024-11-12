<?php

namespace App\Livewire;

use Livewire\Component;

class WysiwygEditor extends Component
{
    public $value;
    public $editorId;

    public function mount($value = '', $editorId = 'editor')
    {
        $this->value = $value;
        $this->editorId = $editorId;
    }

    public function render()
    {
        return view('livewire.wysiwyg-editor');
    }
}
