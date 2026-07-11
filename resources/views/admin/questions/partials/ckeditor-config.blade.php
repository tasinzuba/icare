<!-- CKEditor 5 Alternative Implementation -->
@push('scripts')
<!-- CKEditor 5 CDN -->
<script src="https://cdn.ckeditor.com/ckeditor5/40.2.0/classic/ckeditor.js"></script>

<script>
// Initialize CKEditor 5
document.addEventListener('DOMContentLoaded', function() {
    // For main content
    ClassicEditor
        .create(document.querySelector('#content'), {
            toolbar: {
                items: [
                    'heading', '|',
                    'bold', 'italic', 'underline', '|',
                    'link', 'bulletedList', 'numberedList', '|',
                    'outdent', 'indent', '|',
                    'blockQuote', 'insertTable', '|',
                    'undo', 'redo'
                ]
            },
            language: 'en',
            table: {
                contentToolbar: [
                    'tableColumn',
                    'tableRow',
                    'mergeTableCells'
                ]
            }
        })
        .then(editor => {
            window.contentEditor = editor;
            
            // Save content on change
            editor.model.document.on('change:data', () => {
                document.querySelector('#content').value = editor.getData();
            });
        })
        .catch(error => {
            console.error('CKEditor error:', error);
        });

    // For instructions
    if (document.querySelector('#instructions')) {
        ClassicEditor
            .create(document.querySelector('#instructions'), {
                toolbar: ['bold', 'italic', '|', 'bulletedList', 'numberedList', '|', 'link']
            })
            .then(editor => {
                window.instructionEditor = editor;
            })
            .catch(error => {
                console.error('CKEditor error:', error);
            });
    }
});
</script>
@endpush
