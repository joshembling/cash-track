@php
    use App\Models\User;
@endphp

@extends('layouts.default')

@section('content')

<div class='w-full h-screen flex justify-center items-center'>
    <div class='w-1/2 flex flex-col items-start'>
        <p>Hi {{$user->name}},</p>
        <p>A new expense has been added by {{$expense->user->name}}.</p>
        <p>You owe them £{{$expense->split_amount ?? $expense->amount}} for {{$expense->name}}.</p>
        <a href="{{env('APP_URL')}}/admin/expenses/{{$expense->id}}/edit">View it here</a>
        <br>
        <p>Kind regards,</p>
        <p>Cash Track</p>
    </div>
</div>
@endsection
