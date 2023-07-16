import {constructModalConfirm} from "./utils/confirm.js";

window.addEventListener("DOMContentLoaded", function() {
    const buttonsEditComment = document.querySelectorAll("button[data-role=comment-edit]");

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
});