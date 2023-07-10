{{--{{dd($getRecord()->toArray())}}--}}

@php
   $expenses = collect($getRecord()->toArray())
                ->forget([
                    'id',
                    'created_at',
                    'updated_at',
                    'original_amount',
                    'reminder_sent_user'
                ]);
@endphp

@foreach ($expenses as $k => $v)
    @if ($v)
    <p class="px-4 py-1 bg-gray-100">

        <span class="font-medium">
            {{ $k }}
        </span>

        @if (!is_array($v))
        <span>
            {{ $v }}
        </span>
        @endif

    </p>
    @endif
@endforeach
