import {constructModalConfirm} from "./utils/confirm.js";

window.addEventListener("DOMContentLoaded", function() {
    const buttonsEditComment = document.querySelectorAll("button[data-role=comment-edit]");
    const linksDeleteComment = document.querySelectorAll("a[data-role=comment-delete]");

    buttonsEditComment.forEach((button) => {
        button.addEventListener("click", () => {
            const id = button.dataset.commentId;
            const elementValueComment = document.querySelector("div#containerComments span[data-comment-id='" + id + "']");

            const inputCommentContent = document.querySelector("div#editCommentForm input#comment_content");
            const inputCommentId = document.querySelector("div#editCommentForm input#comment_commentEdited");

            if (id && elementValueComment && inputCommentId) {
                inputCommentContent.value = elementValueComment.textContent;
                inputCommentId.value = id;
            }

        });
    });

    linksDeleteComment.forEach((link) => {
        link.addEventListener("click", (event) => {
            event.preventDefault();

            constructModalConfirm(
                "Êtes-vous sûr de vouloir supprimer ce commentaire ?",
                () => {
                    window.location.href = link.href;
                }
            ).show();
        });
    });

    // Scroll to element current page
    const elementPageIndex = document.querySelector("[data-page-index]");
    if (elementPageIndex) {
        window.scrollTo(elementPageIndex.offsetLeft, elementPageIndex.offsetTop + elementPageIndex.offsetHeight);
    }
});