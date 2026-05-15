@extends('layouts.app')

@section('content')
    <form id="translationForm">
        @csrf
        <div class="language-container">
            <div class="row">
                @foreach(['fr' => 'French', 'en' => 'English', 'es' => 'Spanish'] as $langCode => $langName)
                    <div class="col-lg-4">
                        <div class="navbar navbar-light customPanel language-section">
                            <h2>{{ $langName }} ({{ strtoupper($langCode) }})</h2>
                            <hr>
                            <div id="phrases-{{ $langCode }}">
                                @forelse($phrases[$langCode] as $key => $value)
                                    <div class="phrase-pair form-group d-flex gap-2 my-2">
                                        <input type="text" class="form-control" name="{{ $langCode }}[{{ $loop->index }}][key]" value="{{ old($langCode.'.'.$loop->index.'.key', $key) }}" placeholder="Original phrase" />
                                        <input type="text" class="form-control" name="{{ $langCode }}[{{ $loop->index }}][value]" value="{{ old($langCode.'.'.$loop->index.'.value', $value) }}" placeholder="Translated phrase" />
                                        <button type="button" class="btn btn-danger" onclick="removePhrase(this)"><i class="fa-solid fa-trash"></i></button>
                                    </div>
                                @empty
                                    <div class="phrase-pair form-group d-flex gap-3 my-2">
                                        <input type="text" class="form-control" name="{{ $langCode }}[0][key]" placeholder="Original phrase" />
                                        <input type="text" class="form-control" name="{{ $langCode }}[0][value]" placeholder="Translated phrase" />
                                        <button type="button" class="btn btn-danger" onclick="removePhrase(this)"><i class="fa-solid fa-trash"></i></button>
                                    </div>
                                @endforelse
                            </div>
                            <button type="button" class="btn btn-success" onclick="addPhrase('{{ $langCode }}')"><i class="fa-solid fa-plus"></i></button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        <div  class="navbar navbar-light customPanel language-section">
            <button type="submit" class="btn btn-primary mt-3">Save All Translations</button>
        </div>
    </form>

    <script>
        function addPhrase(lang) {
            const container = document.getElementById('phrases-' + lang);
            const index = container.children.length;
            const div = document.createElement('div');
            div.className = 'phrase-pair form-group d-flex gap-2 my-2';
            div.innerHTML = `
                <input type="text" class="form-control first" name="${lang}[${index}][key]" placeholder="Original phrase" />
                <input type="text" class="form-control" name="${lang}[${index}][value]" placeholder="Translated phrase" />
                <button type="button"class="btn btn-danger" onclick="removePhrase(this)"><i class="fa-solid fa-trash"></i></button>
            `;
            container.appendChild(div);
            div.querySelector("input.first").focus()
        }

        function removePhrase(button) {
            button.closest('.phrase-pair').remove();
        }

        document.getElementById('translationForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = new FormData(e.target);
            const data = {};

            // Group by language
            for (let [key, value] of formData.entries()) {
                const match = key.match(/^(\w+)\[(\d+)\]\[(key|value)\]$/);
                if (match) {
                    const lang = match[1];
                    const idx = match[2];
                    const type = match[3];

                    if (!data[lang]) data[lang] = {};
                    if (!data[lang][idx]) data[lang][idx] = {};

                    data[lang][idx][type] = value;
                }
            }

            // Convert to flat object: { "fr": { "hello": "bonjour" }, ... }
            const payload = {};
            for (const lang in data) {
                payload[lang] = {};
                for (const idx in data[lang]) {
                    const pair = data[lang][idx];
                    if (pair.key && pair.value) {
                        payload[lang][pair.key] = pair.value;
                    }
                }
            }

            try {
                const response = await fetch('{{ route("translations.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                    },
                    body: JSON.stringify(payload)
                });

                if (response.ok) {
                    alert('Translations saved successfully!');
                } else {
                    alert('Error saving translations.');
                }
            } catch (err) {
                console.error(err);
                alert('Network error.');
            }
        });
    </script>

@endsection