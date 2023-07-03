@php
    use App\Models\User;
@endphp

@extends('layouts.default')

@section('content')

<div class='w-full h-screen flex justify-center items-center'>
    <div class='w-1/2 flex flex-col items-start'>
        <p>Hi {{$user->name}},</p>
        <p>Just a quick reminder to say that your {{$expense->name}} payment of £{{$expense->split_amount ?? $expense->amount}} is due tomorrow.</p>
        @if ($user->id === $expense->user_id && $expense->split && $expense->payee_id !== null)
        <p>{{User::find($expense->payee_id)->name}} will transfer you their share. 👀</p>
        @else
        <p>Don't forget to transfer the money to {{User::find($expense->user_id)->name}}.</p>
        @endif
        <p>Kind regards,</p>
        <p>Cash Track</p>
    </div>
</div>
@endsection
