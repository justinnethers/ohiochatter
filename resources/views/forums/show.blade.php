@extends('layouts.app')

@section('content')
    <div>
        <section class="container">
            @foreach ($threads as $thread)
                <article>
                    <a href="/forums/{{ $forum->slug }}/{{ $thread->slug }}">{{ $thread->title }}</a>
                </article>
            @endforeach
        </section>

        {{ $threads->links() }}
    </div>
@stop
