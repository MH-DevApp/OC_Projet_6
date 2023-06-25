window.addEventListener("DOMContentLoaded", function() {
    const modalTitle = document.querySelector("h1.modal-title");

    const btnFeatured = document.querySelector("button[data-role=featured]");
    const btnMedia = document.querySelector("button[data-role=media]");

    const typeInput = document.querySelector("select#media_trick_type");
    const imageFileInput = document.querySelector("input#media_trick_imageFile");
    const linkMovieInput = document.querySelector("input#media_trick_linkMovie");
    const sourceNameInput = document.querySelector("input#media_trick_sourceName");
    const sourceLinkInput = document.querySelector("input#media_trick_sourceLink");
    const isFeaturedInput = document.querySelector("input#media_trick_isFeatured");

    if (btnFeatured) {
        btnFeatured.addEventListener("click", function() {
            showModalFeatured();
        });
    }

    if (btnMedia) {
        btnMedia.addEventListener("click", function() {
            showModalMedia();
        });
    }

    typeInput.addEventListener("change", function(event) {
        imageFileInput.value = "";
        linkMovieInput.value = "";
        sourceNameInput.value = "";
        sourceLinkInput.value = "";

        if (event.target.value === "image") {
            imageFileInput.parentElement.classList.remove("d-none");
            linkMovieInput.parentElement.classList.add("d-none");
            sourceLinkInput.parentElement.classList.remove("d-none");
            sourceNameInput.parentElement.classList.remove("d-none");
        } else {
            imageFileInput.parentElement.classList.add("d-none");
            linkMovieInput.parentElement.classList.remove("d-none");
            sourceLinkInput.parentElement.classList.add("d-none");
            sourceNameInput.parentElement.classList.add("d-none");
        }
    });

    document.getElementById("mediaForm")
        .addEventListener("hidden.bs.modal", function() {
       clearForm();
    });

    const showModalFeatured = () => {
        modalTitle.textContent = "Mise en avant";
        isFeaturedInput.checked = true;
        typeInput.value = "image";
        typeInput.parentElement.classList.add("d-none");
        linkMovieInput.parentElement.classList.add("d-none");
    };

    const showModalMedia = () => {
        modalTitle.textContent = "Média";
        typeInput.parentElement.classList.remove("d-none");
        isFeaturedInput.checked = false;
        if (typeInput.value === "image") {
            imageFileInput.parentElement.classList.remove("d-none");
            linkMovieInput.parentElement.classList.add("d-none");
            sourceLinkInput.parentElement.classList.remove("d-none");
            sourceNameInput.parentElement.classList.remove("d-none");
        } else {
            imageFileInput.parentElement.classList.add("d-none");
            linkMovieInput.parentElement.classList.remove("d-none");
            sourceLinkInput.parentElement.classList.add("d-none");
            sourceNameInput.parentElement.classList.add("d-none");
        }
    };

    const clearForm = () => {
        typeInput.value = "image";
        imageFileInput.value = "";
        linkMovieInput.value = "";
        sourceNameInput.value = "";
        sourceLinkInput.value = "";
        isFeaturedInput.checked = false;

        typeInput.parentElement.classList.remove("d-none");
        imageFileInput.parentElement.classList.remove("d-none");
        linkMovieInput.parentElement.classList.add("d-none");

    };

    // Show Movie link infos

    const iconInfoLinkMovie = document.querySelector("i#infoLinkMovie");
    const modalInfoLinkMovie = document.querySelector("div#modalInfoLinkMovie");
    const modalForm = document.querySelector("div#mediaForm");
    const btnBackToModalForm = modalInfoLinkMovie.querySelector("button#backToModalForm");

    iconInfoLinkMovie.addEventListener("click", function() {
        modalForm.classList.add("d-none");
        modalInfoLinkMovie.classList.remove("d-none");
        modalInfoLinkMovie.classList.add("d-flex");
    });

    btnBackToModalForm.addEventListener("click", function() {
        modalInfoLinkMovie.classList.add("d-none");
        modalInfoLinkMovie.classList.remove("d-flex");
        modalForm.classList.remove("d-none");
    });

    // Show Modal Media

    const modalShowMedia = document.querySelector("div#modalShowMedia");
    const medias = document.querySelectorAll("[data-role=showMedia]");
    const btnShowModalMedia = document.querySelector("button#btnShowModalMedia");

    medias.forEach((media) => {
        media.addEventListener("click", (event) => {
            const cloneParentMedia = media.parentElement.cloneNode(true);
            const cloneMedia = cloneParentMedia.querySelector("[data-role=showMedia]");
            const modalBody = modalShowMedia.querySelector("div.modal-body");

            cloneParentMedia.classList.add("w-100");
            cloneMedia.classList.add("h-100");
            cloneMedia.style.cursor = "default";
            modalBody.append(cloneParentMedia);

            btnShowModalMedia.click();
        });
    });

    modalShowMedia.addEventListener("hidden.bs.modal", () => {
        modalShowMedia.querySelector("div.modal-body").innerHTML = "";
    });

});