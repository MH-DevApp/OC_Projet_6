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

    // Scroll to element current page
    const elementPageIndex = document.querySelector("[data-page-index]");
    if (elementPageIndex) {
        window.scrollTo(elementPageIndex.offsetLeft, elementPageIndex.offsetTop + elementPageIndex.offsetHeight);
    }

    // Scroll to container tricks
    const elementToContainerTricks = document.querySelectorAll("[data-to-container-tricks]");
    if (elementToContainerTricks.length) {
        const containerTricks = document.querySelector("#containerTricks");
        elementToContainerTricks.forEach((element) => {
            element.addEventListener("click", (event) => {
                event.preventDefault();
                window.scrollTo(containerTricks.offsetLeft, containerTricks.offsetTop - 100);
            });
        });
    }

});
