window.addEventListener("DOMContentLoaded", () => {
    const elementHistoryIndex = document.querySelector("[data-page-histories-index]");

    if (elementHistoryIndex) {
        const modalHistories = document.querySelector("#modalHistories");
        new bootstrap.Modal(modalHistories).show();
    }
});
