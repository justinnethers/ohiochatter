<?php
// app/Livewire/QuoteButton.php
namespace App\Livewire;

use Livewire\Component;
use App\Models\Reply;
use App\Models\Thread;

class QuoteButton extends Component
{
    public Reply | Thread $post;

    public function quote()
    {
        $username = $this->post->owner->username;
        $content = trim($this->post->body);

        // Format the quote with a proper paragraph break after
        $quote = sprintf(
            '<blockquote class="border-l-4 border-gray-300 pl-4 my-4"><strong>%s wrote:</strong><br>%s</blockquote><div><br></div>',
            htmlspecialchars($username),
            $content
        );

        $this->dispatch('insertQuote', quote: $quote);
    }

    public function render()
    {
        return view('livewire.quote-button');
    }
}
