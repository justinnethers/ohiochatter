<?php

namespace App\Modules\Messages\Http\Controllers;

use App\Modules\Messages\Actions\AddMessage;
use App\Modules\Messages\Actions\CreateThread;
use App\Modules\Messages\Actions\FetchThreads;
use App\Http\Controllers\Controller;
use App\Modules\Messages\Http\Requests\AddMessageRequest;
use App\Modules\Messages\Http\Requests\CreateThreadRequest;
use App\Models\User;
use Cmgmyr\Messenger\Models\Thread;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request, FetchThreads $fetcher)
    {
        $threads = $fetcher->execute(
            auth()->id(),
            $request->boolean('unread')
        );

        return view('messages.index', compact('threads'));
    }

    public function show(Thread $thread)
    {
        abort_unless($thread->hasParticipant(auth()->id()), 403);

        $users = User::whereNotIn('id', $thread->participantsUserIds(auth()->id()))->get();

        $thread->markAsRead(auth()->id());

        return view('messages.show', compact('thread', 'users'));
    }

    public function create()
    {
        $users = User::where('id', '!=', auth()->id())->get();

        return view('messages.create', compact('users'));
    }

    public function store(CreateThreadRequest $request, CreateThread $action)
    {
        $thread = $action->execute(
            $request->validated(),
            auth()->id()
        );

        return redirect()->route('messages.show', $thread)
            ->with('message', 'Thread created successfully.');
    }

    public function addMessage(AddMessageRequest $request, Thread $thread, AddMessage $action)
    {
        abort_unless($thread->hasParticipant(auth()->id()), 403);

        $action->execute($thread, $request->validated(), auth()->id());

        return redirect()->route('messages.show', $thread)
            ->with('message', 'Message sent successfully.');
    }
}
