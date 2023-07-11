import { constructModalConfirm } from "./utils/confirm.js";

window.addEventListener("DOMContentLoaded", () => {
    const btnDeleteTrick = document.querySelector("div#containerActions a[data-role=deleteTrick]");
    const btnSeeMedias = document.querySelector("button[data-role=seeMedias]");
    const trickName = document.querySelector("#pictureFeaturedTrick").dataset.trickName;

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

    const mediaShow = document.querySelector("div[data-show]");
    const medias = document.querySelectorAll("[data-media]");

    medias.forEach((media) => {
        media.addEventListener("click", () => {
            const mediaCurrent = document.querySelector("[data-media].bg-primary");

            if (media.dataset.media === "image") {
                mediaShow.innerHTML = createImageShowDOM(media);
            } else {
                mediaShow.innerHTML = createVideoShowDOM(media);
            }

            mediaCurrent.classList.remove("bg-primary");
            mediaCurrent.classList.add("bg-white");
            media.classList.remove("bg-white");
            media.classList.add("bg-primary");

        });
    });

    const createImageShowDOM = (media) => {
        const result = document.createElement("div");
        const mediaSource = media.parentElement.querySelector("[data-media-source]").cloneNode(true);

        const imgMediaShow = document.createElement("img");
        imgMediaShow.src = media.src;
        imgMediaShow.alt = media.alt;
        imgMediaShow.className = "shadow w-100 p-1 bg-white";

        const containerTitle = document.createElement("div");
        const title = document.createElement("h1");
        containerTitle.className = "position-absolute bottom-0 w-100 h-100 d-flex align-items-end justify-content-center mb-5";
        title.className = "title-trick-name bg-dark bg-opacity-75 text-white p-2 px-4";
        title.textContent = trickName;
        containerTitle.appendChild(title);

        result.appendChild(imgMediaShow);
        result.appendChild(containerTitle);

        if (mediaSource.classList.contains("d-flex")) {
            result.appendChild(mediaSource);
        }

        return result.innerHTML;

    };

    const createVideoShowDOM = (media) => {
        const result = document.createElement("div");

        const iframe = document.createElement("iframe");
        iframe.className = "shadow w-100 bg-white p-1";
        iframe.src = media.dataset.videoLink;
        iframe.allowFullScreen = true;

        result.appendChild(iframe);

        return result.innerHTML;
    };
});