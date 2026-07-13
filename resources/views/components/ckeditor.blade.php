@props(['name', 'value' => '', 'rows' => 10])

<textarea name="{{ $name }}" id="editor-{{ $name }}" rows="{{ $rows }}"
          class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old($name, $value) }}</textarea>

@once
    <script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
@endonce

<script>
    (function () {
        function initEditor() {
            var el = document.querySelector('#editor-{{ $name }}');
            if (!el || typeof ClassicEditor === 'undefined' || el.dataset.ckInitialized) return;
            el.dataset.ckInitialized = 'true';

            ClassicEditor.create(el, {
                simpleUpload: {
                    uploadUrl: '{{ route('admin.uploads.editor-image') }}',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                }
            }).then(function (editor) {
                var form = el.closest('form');
                form?.addEventListener('submit', function () {
                    editor.updateSourceElement();
                });
            }).catch(function (error) {
                console.error('CKEditor failed to load', error);
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initEditor);
        } else {
            initEditor();
        }
    })();
</script>
