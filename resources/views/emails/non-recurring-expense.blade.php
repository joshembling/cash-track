@extends('layouts.default')

@section('content')

<div class='w-full h-screen flex justify-center items-center'>
    <div class='w-1/2 flex flex-col items-start'>
        <p>Hi {{$user->name}},</p>
        <p>You currently have outstanding expenses that have not yet been marked as paid.</p>
        <p>If you have already paid or received payment, please update them from the <a href="{{env('APP_URL')}}/admin/">dashboard</a>.</p>
        <p>Kind regards,</p>
        <p>Cash Track 💸</p>
    </div>
</div>
@endsection
