import {constructModalConfirm} from "./utils/confirm.js";

window.addEventListener("DOMContentLoaded", function() {
    const inputNameTrick = document.querySelector("input#trick_first_step_name");
    const inputDescriptionTrick = document.querySelector("textarea#trick_first_step_description");
    const inputGroupTrick = document.querySelector("select#trick_first_step_groupTrick");
    const btnSaveTrick = document.querySelector("button#btnSaveTrick");
    const btnCancelEditTrick = document.querySelector("button#btnCancelEditTrick");

    const tmpNameTrick = inputNameTrick.value;
    const tmpDescriptionTrick = inputDescriptionTrick.value;
    const tmpGroupTrick = inputGroupTrick.value;

    btnCancelEditTrick.addEventListener("click", () => {
        constructModalConfirm(
            "Êtes-vous sûr de vouloir annuler les modifications ?",
            () => {
                inputNameTrick.value = tmpNameTrick;
                inputDescriptionTrick.value = tmpDescriptionTrick;
                inputGroupTrick.value = tmpGroupTrick;
                onChangeFieldValue();
            }
        ).show();
    });

    const onChangeFieldValue = () => {
        if (
            tmpNameTrick === inputNameTrick.value &&
            tmpDescriptionTrick === inputDescriptionTrick.value &&
            tmpGroupTrick === inputGroupTrick.value
        ) {
            btnSaveTrick.classList.add("disabled");
            btnCancelEditTrick.classList.add("d-none");
        } else {
            btnSaveTrick.classList.remove("disabled");
            btnCancelEditTrick.classList.remove("d-none");
        }
    }

    inputNameTrick.addEventListener("input", onChangeFieldValue);
    inputDescriptionTrick.addEventListener("input", onChangeFieldValue);
    inputGroupTrick.addEventListener("change", onChangeFieldValue);

    // Delete media
    const linksDeleteMedia = document.querySelectorAll("[data-media-action=delete]");
    linksDeleteMedia.forEach((link) => {
        link.addEventListener("click", (event) => {
            event.preventDefault();

            constructModalConfirm(
                `Êtes-vous sûr de vouloir supprimer cette ${link.dataset.mediaType} ?`,
                () => {
                    window.location = link.href;
                }
            ).show();
        });
    })

});