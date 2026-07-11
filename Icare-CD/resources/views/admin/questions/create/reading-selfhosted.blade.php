    @push('scripts')
    <!-- Self-Hosted TinyMCE Solution -->
    <!-- Download TinyMCE from: https://www.tiny.cloud/get-tiny/self-hosted/ -->
    <!-- Extract and place in public/js/tinymce/ directory -->
    
    <!-- Option 1: Use CDN without API key (Limited features) -->
    <script src="https://cdn.jsdelivr.net/npm/tinymce@5/tinymce.min.js"></script>
    
    <!-- Option 2: Self-hosted (After downloading) -->
    <!-- <script src="{{ asset('js/tinymce/tinymce.min.js') }}"></script> -->
    
    <!-- Option 3: Use alternative CDN -->
    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.2/tinymce.min.js"></script> -->
    
    <script>
    // Initialize without API key for basic features
    tinymce.init({
        selector: '.tinymce-editor',
        height: 400,
        menubar: false,
        plugins: [
            'lists', 'link', 'charmap', 'code', 'table', 'wordcount'
        ],
        toolbar: 'undo redo | formatselect | bold italic underline | alignleft aligncenter alignright | bullist numlist | link | removeformat code',
        // Remove premium features that require API key
        // No image upload, no advanced features
        content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }',
        
        // Alternative image handling
        paste_data_images: true,
        
        setup: function(editor) {
            editor.on('change', function() {
                editor.save();
            });
        }
    });
    </script>
    @endpush
