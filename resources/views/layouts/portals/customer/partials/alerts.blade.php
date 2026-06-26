@if (session('success'))
    <div class="mb-5 rounded-xl border border-success/30 bg-success/10 px-4 py-3 text-sm font-medium text-success">
        {{ session('success') }}
    </div>
@endif

@if ($errors->any())
    <div class="mb-5 rounded-xl border border-error/30 bg-error/10 px-4 py-3 text-sm text-error">
        <p class="font-semibold">Periksa kembali input berikut:</p>
        <ul class="mt-2 list-disc pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
