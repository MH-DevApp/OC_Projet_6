import { constructModalConfirm } from "./utils/confirm.js";

window.addEventListener("DOMContentLoaded", () => {
    const buttonsDeleteTrick = document
        .querySelectorAll(
            "div#containerTricks article a[data-role=deleteTrick]"
        );

    buttonsDeleteTrick.forEach((button) => {
        button.addEventListener("click", function (event) {
            event.preventDefault();

            constructModalConfirm(
                "Êtes-vous sûr de vouloir supprimer cette figure ?",
                () => {
                    window.location = button.href;
                }
            )
                .show();
        });
    });

});