(() => {
    const workspace = document.querySelector("[data-resume-key]");
    const content = document.querySelector("[data-lesson-content]");
    const prompt = document.querySelector("[data-resume-prompt]");

    if (!(workspace instanceof HTMLElement) || !(content instanceof HTMLElement)
        || !(prompt instanceof HTMLElement)) return;

    const slides = [...content.querySelectorAll("[data-lesson-slide]")];
    const key = `tw-lesson-reading:${workspace.dataset.resumeKey}`;
    const description = prompt.querySelector("[data-resume-description]");
    const continueButton = prompt.querySelector("[data-resume-continue]");
    const dismissButton = prompt.querySelector("[data-resume-dismiss]");

    const storage = {
        read() {
            try {
                const value = window.localStorage.getItem(key);
                return value ? JSON.parse(value) : null;
            } catch {
                return null;
            }
        },
        write(value) {
            try {
                window.localStorage.setItem(key, JSON.stringify(value));
            } catch {
                if (description instanceof HTMLElement) description.textContent = "Reading position can’t be saved in this browser.";
            }
        },
        clear() {
            try {
                window.localStorage.removeItem(key);
            } catch {
                if (description instanceof HTMLElement) description.textContent = "Reading position can’t be cleared in this browser.";
            }
        },
    };

    const saved = storage.read();
    const savedSlide = Number(saved?.slideIndex);
    const canResume = Number.isInteger(savedSlide) && savedSlide > 0 && savedSlide < slides.length;

    if (canResume) {
        const sectionTitle = slides[savedSlide]?.querySelector("h2, h3")?.textContent?.trim();
        if (description instanceof HTMLElement) {
            description.textContent = sectionTitle
                ? `Continue from page ${savedSlide + 1}: ${sectionTitle}`
                : `Continue from page ${savedSlide + 1} of ${slides.length}.`;
        }
        prompt.hidden = false;
    }

    continueButton?.addEventListener("click", () => {
        if (canResume) {
            window.dispatchEvent(new CustomEvent("lesson:go-to-slide", {
                detail: { slideIndex: savedSlide },
            }));
        }
        prompt.hidden = true;
    });

    dismissButton?.addEventListener("click", () => {
        prompt.hidden = true;
        storage.clear();
    });

    window.addEventListener("lesson:slidechange", (event) => {
        if (!(event instanceof CustomEvent) || !Number.isInteger(event.detail?.slideIndex)) return;
        const slideIndex = event.detail.slideIndex;
        if (slideIndex === 0) {
            storage.clear();
            return;
        }
        storage.write({
            slideIndex,
            sectionId: event.detail.sectionId ?? "",
            savedAt: Date.now(),
        });
    });

    window.addEventListener("pagehide", () => {
        const slideIndex = Number(workspace.dataset.currentSlide ?? 0);
        if (slideIndex > 0) {
            storage.write({
                slideIndex,
                sectionId: slides[slideIndex]?.dataset.sectionId ?? "",
                savedAt: Date.now(),
            });
        }
    });
})();
