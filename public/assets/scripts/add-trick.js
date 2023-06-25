import { constructModalConfirm } from "./utils/confirm.js";

window.addEventListener("DOMContentLoaded", function() {
    const linkCancelTrick = document.querySelector("a#linkCancelTrick");
    const btnCancelTrick = document.querySelector("button#btnCancelTrick");

    if (btnCancelTrick) {
        btnCancelTrick.addEventListener("click", () => {
            constructModalConfirm(
                "Êtes-vous sûr d'annuler la figure ?",
                () => {
                    linkCancelTrick.click();
                }
            ).show();
        })
    }
});