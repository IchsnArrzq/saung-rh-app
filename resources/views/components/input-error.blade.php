@props(['messages'])

@if ($messages)
    <ul {{ $attributes->merge(['class' => 'label-text-alt text-error text-xs']) }}>
        @foreach ((array) $messages as $message)
            <li>{{ $message }}</li>
        @endforeach
    </ul>
@endif
