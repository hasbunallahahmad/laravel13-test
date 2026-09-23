import { Editor } from "@tiptap/core";
import StarterKit from "@tiptap/starter-kit";
import Image from "@tiptap/extension-image";

export function initializeContentEditor(wrapper) {
    if (!wrapper) {
        return null;
    }

    if (wrapper.dataset.tiptapInitialized === "true") {
        return null;
    }

    const editorElement = wrapper.querySelector("[data-tiptap-editor]");
    const textarea = wrapper.querySelector('textarea[name="body"]');
    const buttons = wrapper.querySelectorAll("[data-editor-command]");

    const linkPopover = wrapper.querySelector("[data-link-popover]");
    const linkUrlInput = wrapper.querySelector("[data-link-url]");
    const linkTargetInput = wrapper.querySelector("[data-link-target]");
    const linkSaveButton = wrapper.querySelector("[data-link-save]");
    const linkCancelButton = wrapper.querySelector("[data-link-cancel]");

    const mediaPickerUrl = wrapper.dataset.mediaPickerUrl;
    const mediaPickerModal = wrapper.querySelector("[data-media-picker-modal]");
    const mediaPickerContent = wrapper.querySelector(
        "[data-media-picker-content]",
    );

    const mediaPickerCloseButton = wrapper.querySelector(
        "[data-media-picker-close]",
    );

    if (
        !editorElement ||
        !textarea ||
        !linkPopover ||
        !linkUrlInput ||
        !linkTargetInput ||
        !linkSaveButton ||
        !linkCancelButton ||
        !mediaPickerUrl ||
        !mediaPickerModal ||
        !mediaPickerContent ||
        !mediaPickerCloseButton
    ) {
        return null;
    }

    let savedSelection = null;

    const editor = new Editor({
        element: editorElement,

        extensions: [
            StarterKit,
            Image.configure({
                inline: false,
                allowBase64: false,
            }),
        ],

        content: textarea.value || "<p></p>",

        onUpdate: ({ editor }) => {
            textarea.value = editor.getHTML();
            updateToolbarState();
        },
    });

    buttons.forEach((button) => {
        button.addEventListener("mousedown", (event) => {
            event.preventDefault();
        });

        button.addEventListener("click", (event) => {
            event.preventDefault();

            const command = button.dataset.editorCommand;

            switch (command) {
                case "bold":
                    editor.chain().focus().toggleBold().run();
                    break;

                case "italic":
                    editor.chain().focus().toggleItalic().run();
                    break;

                case "bullet-list":
                    editor.chain().focus().toggleBulletList().run();
                    break;

                case "ordered-list":
                    editor.chain().focus().toggleOrderedList().run();
                    break;

                case "heading-2":
                    editor.chain().focus().toggleHeading({ level: 2 }).run();
                    break;

                case "heading-3":
                    editor.chain().focus().toggleHeading({ level: 3 }).run();
                    break;

                case "paragraph":
                    editor.chain().focus().setParagraph().run();
                    break;

                case "underline":
                    editor.chain().focus().toggleUnderline().run();
                    break;

                case "strike":
                    editor.chain().focus().toggleStrike().run();
                    break;

                case "clear":
                    editor.chain().focus().clearNodes().unsetAllMarks().run();
                    break;

                case "blockquote":
                    editor.chain().focus().toggleBlockquote().run();
                    break;

                case "image":
                    handleImage();
                    break;

                case "undo":
                    editor.chain().focus().undo().run();
                    break;

                case "redo":
                    editor.chain().focus().redo().run();
                    break;

                case "link":
                    handleLink();
                    break;

                case "unlink":
                    editor.chain().focus().unsetLink().run();
                    break;
            }

            textarea.value = editor.getHTML();
            updateToolbarState();
        });
    });

    linkSaveButton.addEventListener("click", (event) => {
        event.preventDefault();

        saveLink();
    });

    linkCancelButton.addEventListener("click", (event) => {
        event.preventDefault();

        closeLinkPopover();
    });

    linkUrlInput.addEventListener("keydown", (event) => {
        if (event.key === "Enter") {
            event.preventDefault();

            saveLink();
        }

        if (event.key === "Escape") {
            event.preventDefault();

            closeLinkPopover();
        }
    });

    linkTargetInput.addEventListener("keydown", (event) => {
        if (event.key === "Escape") {
            event.preventDefault();

            closeLinkPopover();
        }
    });

    mediaPickerCloseButton.addEventListener("click", (event) => {
        event.preventDefault();

        closeMediaPicker();
    });

    mediaPickerModal.addEventListener("mousedown", (event) => {
        if (event.target === mediaPickerModal) {
            closeMediaPicker();
        }
    });

    editor.on("selectionUpdate", updateToolbarState);
    editor.on("transaction", updateToolbarState);

    textarea.value = editor.getHTML();

    wrapper.dataset.tiptapInitialized = "true";

    updateToolbarState();

    return editor;

    function updateToolbarState() {
        const states = {
            bold: editor.isActive("bold"),
            italic: editor.isActive("italic"),

            "bullet-list": editor.isActive("bulletList"),
            "ordered-list": editor.isActive("orderedList"),

            "heading-2": editor.isActive("heading", {
                level: 2,
            }),

            "heading-3": editor.isActive("heading", {
                level: 3,
            }),

            paragraph: editor.isActive("paragraph"),

            underline: editor.isActive("underline"),
            strike: editor.isActive("strike"),

            blockquote: editor.isActive("blockquote"),

            image:
                editor.isActive("image") ||
                editor.state.selection.node?.type.name === "image",

            link: editor.isActive("link"),
        };

        buttons.forEach((button) => {
            const command = button.dataset.editorCommand;

            if (Object.hasOwn(states, command)) {
                const active = states[command];

                button.classList.toggle("is-active", active);

                button.setAttribute("aria-pressed", active ? "true" : "false");
            }

            if (command === "undo") {
                button.disabled = !editor.can().undo();
            }

            if (command === "redo") {
                button.disabled = !editor.can().redo();
            }
        });
    }

    function handleImage() {
        const { from, to } = editor.state.selection;

        savedSelection = {
            from,
            to,
        };

        openMediaPicker();
    }

    async function openMediaPicker() {
        mediaPickerModal.classList.remove("hidden");

        mediaPickerContent.innerHTML = `
        <div class="flex items-center justify-center py-12">
            <p class="text-sm text-app-text-muted">
                Memuat gambar...
            </p>
        </div>
    `;

        try {
            const response = await fetch(mediaPickerUrl, {
                headers: {
                    Accept: "text/html",
                    "X-Requested-With": "XMLHttpRequest",
                },
            });

            if (!response.ok) {
                throw new Error("Gagal memuat Media Picker.");
            }

            mediaPickerContent.innerHTML = await response.text();

            bindMediaPickerItems();
        } catch (error) {
            mediaPickerContent.innerHTML = `
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                Gagal memuat gambar. Silakan coba lagi.
            </div>
        `;

            console.error(error);
        }
    }

    function bindMediaPickerItems() {
        if (!mediaPickerContent) {
            return;
        }

        const items = mediaPickerContent.querySelectorAll(
            "[data-media-picker-item]",
        );

        items.forEach((item) => {
            item.addEventListener("click", (event) => {
                event.preventDefault();

                insertMediaImage(item);
            });
        });
    }

    function insertMediaImage(item) {
        const src = item.dataset.mediaSrc;
        const alt = item.dataset.mediaAlt || "";
        const width = item.dataset.mediaWidth;
        const height = item.dataset.mediaHeight;

        if (!src) {
            return;
        }

        restoreSelection();

        const attributes = {
            src,
            alt,
        };

        if (width) {
            attributes.width = Number(width);
        }

        if (height) {
            attributes.height = Number(height);
        }

        editor.chain().focus().setImage(attributes).run();

        textarea.value = editor.getHTML();

        closeMediaPicker();
    }

    function closeMediaPicker() {
        mediaPickerModal.classList.add("hidden");

        mediaPickerContent.innerHTML = `
            <div class="flex items-center justify-center py-12">
                <p class="text-sm text-app-text-muted">
                    Memuat gambar...
                </p>
            </div>
        `;

        savedSelection = null;
    }

    function handleLink() {
        const { from, to } = editor.state.selection;

        savedSelection = {
            from,
            to,
        };

        const attributes = editor.getAttributes("link");

        linkUrlInput.value = attributes.href ?? "";

        linkTargetInput.checked = attributes.target === "_blank";

        linkPopover.classList.remove("hidden");

        requestAnimationFrame(() => {
            linkUrlInput.focus();
            linkUrlInput.select();
        });
    }

    function saveLink() {
        const url = linkUrlInput.value.trim();

        if (url === "") {
            restoreSelection();

            editor.chain().focus().unsetLink().run();

            textarea.value = editor.getHTML();

            closeLinkPopover();

            return;
        }

        if (!isSafeLinkUrl(url)) {
            window.alert("URL tautan tidak valid.");

            linkUrlInput.focus();

            return;
        }

        restoreSelection();

        const attributes = {
            href: url,
            target: null,
            rel: null,
        };

        if (linkTargetInput.checked) {
            attributes.target = "_blank";
            attributes.rel = "noreferrer noopener";
        }

        editor
            .chain()
            .focus()
            .extendMarkRange("link")
            .setLink(attributes)
            .run();

        textarea.value = editor.getHTML();

        closeLinkPopover();
    }

    function restoreSelection() {
        if (!savedSelection) {
            return;
        }

        editor.commands.setTextSelection({
            from: savedSelection.from,
            to: savedSelection.to,
        });

        savedSelection = null;
    }

    function closeLinkPopover() {
        linkPopover.classList.add("hidden");

        linkUrlInput.value = "";
        linkTargetInput.checked = false;

        savedSelection = null;
    }

    function isSafeLinkUrl(url) {
        if (url.startsWith("/")) {
            return true;
        }

        if (url.startsWith("#")) {
            return true;
        }

        try {
            const parsed = new URL(url);

            return ["http:", "https:"].includes(parsed.protocol);
        } catch {
            return false;
        }
    }
}
