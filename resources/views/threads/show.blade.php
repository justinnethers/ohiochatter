@extends('layouts.app')

@section('content')
    <div>
        <article>
            <h1>{{ $thread->title }}</h1>
            <p>{!! $thread->body !!}</p>
        </article>
        <pre wrap>{{ $thread }}</pre>
        <pre wrap>{{ $op }}</pre>
    </div>
@stop
