function formatFileSize(bytes) {
    if (bytes < 1024) {
        return `${bytes} B`;
    }

    const kilobytes = bytes / 1024;

    if (kilobytes < 1024) {
        return `${kilobytes.toFixed(1)} KB`;
    }

    const megabytes = kilobytes / 1024;

    return `${megabytes.toFixed(1)} MB`;
}

function getFileTypeLabel(file) {
    switch (file.type) {
        case "application/pdf":
            return "PDF";

        case "image/jpeg":
            return "JPG";

        case "image/png":
            return "PNG";

        case "image/webp":
            return "WebP";

        default:
            return file.type || "File";
    }
}

function initializeMediaUpload(form) {
    if (!form || form.dataset.mediaUploadInitialized === "true") {
        return;
    }

    const input = form.querySelector('input[type="file"][name="file"]');

    const emptyState = form.querySelector("[data-media-upload-empty]");

    const preview = form.querySelector("[data-media-upload-preview]");

    const previewImage = form.querySelector(
        "[data-media-upload-preview-image]",
    );

    const previewIcon = form.querySelector("[data-media-upload-preview-icon]");

    const previewName = form.querySelector("[data-media-upload-preview-name]");

    const previewMeta = form.querySelector("[data-media-upload-preview-meta]");

    if (
        !input ||
        !emptyState ||
        !preview ||
        !previewImage ||
        !previewIcon ||
        !previewName ||
        !previewMeta
    ) {
        return;
    }

    let objectUrl = null;

    function revokeObjectUrl() {
        if (!objectUrl) {
            return;
        }

        URL.revokeObjectURL(objectUrl);
        objectUrl = null;
    }

    function clearPreview() {
        revokeObjectUrl();

        previewImage.removeAttribute("src");
        previewImage.setAttribute("alt", "");
        previewImage.classList.add("hidden");

        previewIcon.classList.add("hidden");

        previewName.textContent = "";
        previewMeta.textContent = "";

        preview.classList.add("hidden");
        emptyState.classList.remove("hidden");
    }

    function showImagePreview(file) {
        revokeObjectUrl();

        objectUrl = URL.createObjectURL(file);

        previewImage.src = objectUrl;
        previewImage.alt = file.name;

        previewImage.classList.remove("hidden");
        previewIcon.classList.add("hidden");
    }

    function showFilePreview() {
        revokeObjectUrl();

        previewImage.removeAttribute("src");
        previewImage.setAttribute("alt", "");
        previewImage.classList.add("hidden");

        previewIcon.classList.remove("hidden");
    }

    function showPreview(file) {
        if (!file) {
            clearPreview();
            return;
        }

        previewName.textContent = file.name;

        previewMeta.textContent = `${getFileTypeLabel(file)} · ${formatFileSize(file.size)}`;

        emptyState.classList.add("hidden");
        preview.classList.remove("hidden");

        if (file.type.startsWith("image/")) {
            showImagePreview(file);
            return;
        }

        showFilePreview();
    }

    input.addEventListener("change", () => {
        showPreview(input.files?.[0] ?? null);
    });

    form.addEventListener("reset", () => {
        clearPreview();
    });

    form.dataset.mediaUploadInitialized = "true";
}

function initializeMediaUploads(root = document) {
    if (!root) {
        return;
    }

    const forms = root.matches?.("[data-media-upload-form]")
        ? [root]
        : root.querySelectorAll("[data-media-upload-form]");

    forms.forEach((form) => {
        initializeMediaUpload(form);
    });
}

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", () => {
        initializeMediaUploads();
    });
} else {
    initializeMediaUploads();
}

document.addEventListener("livewire:navigated", () => {
    initializeMediaUploads();
});

export { initializeMediaUpload, initializeMediaUploads };
