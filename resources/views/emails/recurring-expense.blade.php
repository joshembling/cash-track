@php
    use App\Models\User;
@endphp

@extends('layouts.default')

@section('content')

<div class='w-full h-screen flex justify-center items-center'>
    <div class='w-1/2 flex flex-col items-start'>
        <p>Hi {{$user->name}},</p>
        <p>Just a quick reminder to say that your {{$expense->name}} payment of £{{$expense->split_amount ?? $expense->amount}} is due tomorrow.</p>
        @if($expense->split && $expense->payee_id !== null)
            @if ($user->id === $expense->user_id)
            <p>{{$expense->payee->name}} will transfer you their share. 👀</p>
            @else
            <p>Don't forget to transfer the money to {{$expense->user->name}}.</p>
            @endif
        @endif
        <p>Kind regards,</p>
        <p>Cash Track</p>
    </div>
</div>
@endsection
