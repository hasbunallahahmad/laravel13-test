<script>
    document.addEventListener('DOMContentLoaded', () => {
        const title = document.querySelector('[data-slug-source]');
        const slug = document.querySelector('[data-slug-target]');

        if (!title || !slug) {
            return;
        }

        const originalSlug = slug.dataset.originalSlug ?? '';

        let slugManuallyEdited = slug.value.trim() !== originalSlug;

        const generateSlug = (value) => {
            return value
                .toLowerCase()
                .trim()
                .normalize('NFKD')
                .replace(/[\u0300-\u036f]/g, '')
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/[\s-]+/g, '-')
                .replace(/^-+|-+$/g, '');
        };

        title.addEventListener('input', () => {
            if (slugManuallyEdited) {
                return;
            }

            slug.value = generateSlug(title.value);
        });

        slug.addEventListener('input', () => {
            slugManuallyEdited = true;
        });
    });
</script>
