(() => {
    const workspace = document.querySelector("[data-lesson-workspace]");
    const article = document.querySelector("[data-lesson-article]");
    const content = document.querySelector("[data-lesson-content]");

    if (!(workspace instanceof HTMLElement) || !(article instanceof HTMLElement)
        || !(content instanceof HTMLElement)) return;

    const slides = [...content.querySelectorAll("[data-lesson-slide]")];
    const previousButton = document.querySelector("[data-slide-previous]");
    const nextButton = document.querySelector("[data-slide-next]");
    const slideCount = document.querySelector("[data-slide-count]");
    const slideStatus = document.querySelector("[data-slide-status]");
    const progress = document.querySelector("[data-reading-progress]");
    const progressBar = document.querySelector("[data-reading-progress-bar]");
    const status = document.querySelector("[data-lesson-status]");
    const focusButton = document.querySelector("[data-focus-mode]");
    const focusExitButton = document.querySelector("[data-focus-mode-exit]");
    const sectionLinks = [...document.querySelectorAll("[data-slide-target]")];
    let currentSlide = 0;

    const updateSlide = (index, announce = true) => {
        if (slides.length === 0) return;
        currentSlide = Math.min(slides.length - 1, Math.max(0, index));
        const activeSlide = slides[currentSlide];
        if (!(activeSlide instanceof HTMLElement)) return;

        slides.forEach((slide, slideIndex) => {
            slide.hidden = slideIndex !== currentSlide;
        });
        workspace.dataset.currentSlide = String(currentSlide);
        if (previousButton instanceof HTMLButtonElement) previousButton.disabled = currentSlide === 0;
        if (nextButton instanceof HTMLButtonElement) nextButton.disabled = currentSlide === slides.length - 1;

        const label = `Page ${currentSlide + 1} of ${slides.length}`;
        if (slideCount instanceof HTMLElement) slideCount.textContent = label;
        if (slideStatus instanceof HTMLElement) slideStatus.textContent = label;

        const percentage = Math.round(((currentSlide + 1) / slides.length) * 100);
        if (progress instanceof HTMLElement) progress.setAttribute("aria-valuenow", String(percentage));
        if (progressBar instanceof HTMLElement) {
            const progressClass = `progress-${Math.round(percentage / 5) * 5}`;
            [...progressBar.classList]
                .filter((name) => name.startsWith("progress-"))
                .forEach((name) => progressBar.classList.remove(name));
            progressBar.classList.add(progressClass);
        }

        sectionLinks.forEach((link) => {
            const active = Number(link.dataset.slideTarget) === currentSlide
                || activeSlide.dataset.sectionId === link.getAttribute("href")?.slice(1);
            if (active) link.setAttribute("aria-current", "location");
            else link.removeAttribute("aria-current");
        });

        if (announce) {
            window.dispatchEvent(new CustomEvent("lesson:slidechange", {
                detail: {
                    slideIndex: currentSlide,
                    sectionId: activeSlide.dataset.sectionId ?? "",
                },
            }));
        }
    };

    previousButton?.addEventListener("click", () => updateSlide(currentSlide - 1));
    nextButton?.addEventListener("click", () => updateSlide(currentSlide + 1));
    sectionLinks.forEach((link) => {
        link.addEventListener("click", (event) => {
            event.preventDefault();
            updateSlide(Number(link.dataset.slideTarget));
        });
    });
    window.addEventListener("lesson:go-to-slide", (event) => {
        if (event instanceof CustomEvent && Number.isInteger(event.detail?.slideIndex)) {
            updateSlide(event.detail.slideIndex);
        }
    });

    document.addEventListener("keydown", (event) => {
        if (event.altKey || event.ctrlKey || event.metaKey || workspace.classList.contains("is-panel-open")) return;
        const target = event.target;
        if (target instanceof HTMLElement && (target.isContentEditable
            || ["INPUT", "TEXTAREA", "SELECT", "BUTTON", "A"].includes(target.tagName))) return;
        if (event.key === "ArrowLeft") updateSlide(currentSlide - 1);
        if (event.key === "ArrowRight") updateSlide(currentSlide + 1);
    });

    updateSlide(0, false);

    if (content instanceof HTMLElement) {
        let textScale = 1;
        try {
            const storedScale = Number(window.localStorage.getItem("tw-lesson-text-scale"));
            if (Number.isFinite(storedScale) && storedScale >= 0.9 && storedScale <= 1.3) textScale = storedScale;
        } catch {
            if (status instanceof HTMLElement) status.textContent = "Text size preference cannot be saved in this browser.";
        }

        const setTextScale = (scale) => {
            textScale = Math.min(1.3, Math.max(0.9, scale));
            const scaleClass = `text-scale-${Math.round(textScale * 100)}`;
            [...content.classList].filter((name) => name.startsWith("text-scale-")).forEach((name) => content.classList.remove(name));
            content.classList.add(scaleClass);
            try {
                window.localStorage.setItem("tw-lesson-text-scale", String(textScale));
            } catch {
                if (status instanceof HTMLElement) status.textContent = "Text size changed for this visit only.";
            }
        };

        setTextScale(textScale);
        document.querySelectorAll("[data-text-size]").forEach((button) => {
            button.addEventListener("click", () => {
                const direction = button.getAttribute("data-text-size") === "increase" ? 0.1 : -0.1;
                setTextScale(Math.round((textScale + direction) * 10) / 10);
                if (status instanceof HTMLElement) status.textContent = `Text size ${Math.round(textScale * 100)} percent.`;
            });
        });
    }

    const setFocusMode = (enabled) => {
        workspace.classList.toggle("is-focus-mode", enabled);
        if (focusButton instanceof HTMLButtonElement) {
            focusButton.setAttribute("aria-pressed", String(enabled));
            focusButton.innerHTML = enabled ? "Exit focus mode <span aria-hidden='true'>↙</span>" : "Enter focus mode <span aria-hidden='true'>↗</span>";
        }
        if (focusExitButton instanceof HTMLButtonElement) focusExitButton.hidden = !enabled;
    };

    if (focusButton instanceof HTMLButtonElement) {
        focusButton.addEventListener("click", () => {
            setFocusMode(focusButton.getAttribute("aria-pressed") !== "true");
        });
    }
    if (focusExitButton instanceof HTMLButtonElement) focusExitButton.addEventListener("click", () => setFocusMode(false));

    const closePanels = () => {
        workspace.classList.remove("is-panel-open", "is-panel-notes", "is-panel-outline");
        document.querySelectorAll("[data-player-panel]").forEach((button) => button.setAttribute("aria-expanded", "false"));
    };
    document.querySelectorAll("[data-player-panel]").forEach((button) => {
        button.addEventListener("click", () => {
            const panel = button.getAttribute("data-player-panel");
            const wasOpen = workspace.classList.contains(`is-panel-${panel}`);
            closePanels();
            if (!wasOpen && (panel === "notes" || panel === "outline")) {
                workspace.classList.add("is-panel-open", `is-panel-${panel}`);
                button.setAttribute("aria-expanded", "true");
                if (window.matchMedia("(max-width: 1050px)").matches) {
                    document.querySelector(panel === "notes" ? "[data-lesson-notes]" : ".lesson-tools")
                        ?.scrollIntoView({ behavior: "smooth", block: "start" });
                }
            }
        });
    });
    document.addEventListener("keydown", (event) => {
        if (event.key === "Escape") {
            closePanels();
            if (workspace.classList.contains("is-focus-mode")) setFocusMode(false);
        }
    });

    document.querySelector(".lesson-save-note-link")?.addEventListener("click", (event) => {
        event.preventDefault();
        const notesInput = document.querySelector("[data-lesson-notes-input]");
        if (window.matchMedia("(max-width: 1050px)").matches) {
            workspace.classList.add("is-panel-open", "is-panel-notes");
            document.querySelector("[data-player-panel='notes']")?.setAttribute("aria-expanded", "true");
        }
        if (notesInput instanceof HTMLElement) {
            notesInput.scrollIntoView({ behavior: "smooth", block: "center" });
            notesInput.focus({ preventScroll: true });
        }
    });

    document.querySelector("[data-copy-lesson]")?.addEventListener("click", async () => {
        if (!(status instanceof HTMLElement)) return;
        try {
            await navigator.clipboard.writeText(window.location.href);
            status.textContent = "Lesson link copied.";
        } catch {
            status.textContent = "Clipboard access is unavailable. Copy the lesson URL from your address bar.";
        }
    });
})();
