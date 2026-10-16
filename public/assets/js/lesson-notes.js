(() => {
    const panel = document.querySelector("[data-lesson-notes]");
    const input = panel?.querySelector("[data-lesson-notes-input]");
    const status = panel?.querySelector("[data-notes-status]");
    const saveButton = panel?.querySelector("[data-notes-save]");

    if (!(panel instanceof HTMLElement) || !(input instanceof HTMLTextAreaElement)
        || !(status instanceof HTMLElement) || !(saveButton instanceof HTMLButtonElement)) return;

    const endpoint = panel.dataset.endpoint;
    const courseId = panel.dataset.course;
    const lessonId = panel.dataset.lesson;
    const csrfToken = panel.dataset.csrfToken;
    if (!endpoint || !courseId || !lessonId || !csrfToken) {
        status.textContent = "Notes are unavailable for this lesson.";
        return;
    }

    let savedValue = input.value;
    let pendingSave = null;
    let debounceTimer = 0;

    const setStatus = (message, state = "") => {
        status.textContent = message;
        status.dataset.state = state;
    };

    const save = async () => {
        if (input.value === savedValue) {
            setStatus("All changes saved.", "saved");
            return;
        }
        if (pendingSave) {
            await pendingSave;
            if (input.value === savedValue) return;
        }

        const valueBeingSaved = input.value;
        const form = new URLSearchParams({
            csrf_token: csrfToken,
            course: courseId,
            lesson: lessonId,
            note: valueBeingSaved,
        });

        saveButton.disabled = true;
        setStatus("Saving your note…", "saving");
        pendingSave = (async () => {
            try {
                const response = await fetch(endpoint, {
                    method: "POST",
                    credentials: "same-origin",
                    cache: "no-store",
                    headers: {
                        "Accept": "application/json",
                        "Content-Type": "application/x-www-form-urlencoded;charset=UTF-8",
                    },
                    body: form.toString(),
                });
                const result = await response.json();
                if (!response.ok || result.success !== true) {
                    throw new Error(typeof result.message === "string" ? result.message : "Your note could not be saved.");
                }

                savedValue = valueBeingSaved;
                setStatus(input.value === savedValue ? "All changes saved." : "Saving your latest changes…", "saved");
            } catch (error) {
                setStatus(error instanceof Error ? error.message : "Your note could not be saved. Try again.", "error");
            } finally {
                saveButton.disabled = false;
                pendingSave = null;
                if (input.value !== savedValue) scheduleSave();
            }

        })();

        await pendingSave;
    };

    const scheduleSave = () => {
        window.clearTimeout(debounceTimer);
        setStatus(input.value === savedValue ? "All changes saved." : "Unsaved changes…", input.value === savedValue ? "saved" : "dirty");
        if (input.value !== savedValue) debounceTimer = window.setTimeout(save, 1200);
    };

    input.addEventListener("input", scheduleSave);
    input.addEventListener("blur", () => {
        if (input.value !== savedValue) {
            window.clearTimeout(debounceTimer);
            void save();
        }
    });
    input.addEventListener("keydown", (event) => {
        if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === "s") {
            event.preventDefault();
            window.clearTimeout(debounceTimer);
            void save();
        }
    });
    saveButton.addEventListener("click", () => {
        window.clearTimeout(debounceTimer);
        void save();
    });
})();
