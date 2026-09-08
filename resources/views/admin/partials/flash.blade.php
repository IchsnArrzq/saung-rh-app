@if (session('success'))
    <x-alert type="success" class="mb-4">{{ session('success') }}</x-alert>
@endif

@if ($errors->any())
    <x-alert type="error" class="mb-4" title="Periksa kembali input berikut:">
        <ul class="mt-2 list-disc pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </x-alert>
@endif
