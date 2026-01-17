<!-- CSRF Token -->
<meta name="csrf-token" content="{{ csrf_token() }}">

<!-- Include Bootstrap and Bootstrap Icons if not already present -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">

<link rel="stylesheet" href="{{ asset('/css/file-manager.css') }}">

<!-- The Main Hub -->
<div style="padding: 20px;" id="fm"></div>

<!-- Include the file manager script -->
<script src="{{ asset('vendor/file-manager/js/file-manager.js') }}"></script>

<!-- File manager -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const FileBrowserDialogue = {
            init: function() {
                // Here goes your code for setting your custom things onLoad.
            },
            mySubmit: function(URL) {
                // pass selected file path to TinyMCE
                if (window.parent && window.parent.tinymce) {
                    window.parent.postMessage({
                        mceAction: 'customAction',
                        content: URL,
                        text: URL
                    }, '*');
                    setTimeout(function() {
                        window.parent.tinymce.activeEditor.windowManager.close();
                    }, 100);
                }
            }
        }

        // Add callback to file manager
        fm.$store.commit('fm/setFileCallBack', function(fileUrl) {
            FileBrowserDialogue.mySubmit(fileUrl);
        });
    });
</script>
