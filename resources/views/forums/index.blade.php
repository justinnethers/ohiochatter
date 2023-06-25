@extends('layouts.app')

@section('content')
    <div>
        <ul>
            @foreach($forums as $forum)
                <li>
                    <a href="/forums/{{ $forum->slug }}">{{ $forum->name }} - {{ $forum->is_active }}</a>
                </li>
            @endforeach
        </ul>
    </div>
@stop
