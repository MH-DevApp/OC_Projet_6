import { constructModalConfirm } from "./utils/confirm.js";

window.addEventListener("DOMContentLoaded", () => {
    const btnDeleteTrick = document.querySelector("div#containerActions  a[data-role=deleteTrick]");
    const btnSeeMedias = document.querySelector("button[data-role=seeMedias]");

    if (btnDeleteTrick) {
        btnDeleteTrick.addEventListener("click", function (event) {
            event.preventDefault();

            constructModalConfirm(
                "Êtes-vous sûr de vouloir supprimer cette figure ?",
                () => {
                    window.location = btnDeleteTrick.href;
                }
            )
                .show();
        });
    }

    btnSeeMedias.addEventListener("click", () => {
        const containerMedias = document.querySelector("div#containerMedias");
        containerMedias.classList.toggle("d-none");
    });

    // Media show selected

    const imgMediaShow = document.querySelector("img[data-media-show]");
    const mediasImage = document.querySelectorAll("img[data-media-image]");

    mediasImage.forEach((media) => {
        media.addEventListener("click", () => {
            const mediaCurrent = document.querySelector("img[data-media-image].bg-primary");
            const mediaSource = media.parentElement.querySelector("[data-media-source]");
            const mediaSourceShow = imgMediaShow.parentElement.querySelector("[data-media-source]");

            if (mediaSource.classList.contains("d-none")) {
                mediaSourceShow.classList.remove("d-flex");
                mediaSourceShow.classList.add("d-none");
            } else {
                mediaSourceShow.classList.remove("d-none");
                mediaSourceShow.classList.add("d-flex");
            }

            mediaCurrent.classList.remove("bg-primary");
            mediaCurrent.classList.add("bg-white");
            media.classList.remove("bg-white");
            media.classList.add("bg-primary");

            imgMediaShow.src = media.src;
            imgMediaShow.alt = media.alt;

        });
    });

});