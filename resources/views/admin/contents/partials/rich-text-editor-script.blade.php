<script>
    const initializeContentEditors = () => {
        document.querySelectorAll('[data-content-editor]').forEach((editor) => {
            if (editor.dataset.tiptapInitialized === 'true') {
                return;
            }

            if (typeof window.initializeContentEditor !== 'function') {
                console.error('initializeContentEditor tidak tersedia.');
                return;
            }

            const instance = window.initializeContentEditor(editor);

            if (instance) {
                editor.dataset.tiptapInitialized = 'true';
            }
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeContentEditors);
    } else {
        initializeContentEditors();
    }

    document.addEventListener('livewire:navigated', initializeContentEditors);
</script>
