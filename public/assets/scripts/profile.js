import { constructModalConfirm } from "./utils/confirm.js";

window.addEventListener("DOMContentLoaded", () => {

    // AVATAR

    const formAvatar = document.querySelector("form[name='profile_avatar']");
    const inputFileAvatar = formAvatar.querySelector("input#profile_avatar_avatar");
    const btnEditAvatar = document.querySelector("div.avatar button#avatar_edit");
    const btnDeleteAvatar = document.querySelector("div.avatar button#avatar_delete");

    btnEditAvatar.addEventListener("click", () => {
        inputFileAvatar.click();
    });

    inputFileAvatar.addEventListener("change", () => {
        formAvatar.submit();
    });

    if (btnDeleteAvatar) {
        btnDeleteAvatar.addEventListener("click", () => {
            const deleteAvatar = () => {
                const formDeleteAvatar = document.createElement("form");
                formDeleteAvatar.action = "/profile/avatar/delete";
                formDeleteAvatar.method = "POST";

                document.querySelector("div.body").append(formDeleteAvatar);

                formDeleteAvatar.submit();
            }

            const modalConfirmDeleteAvatar = constructModalConfirm(
                "Êtes-vous sûr de vouloir supprimer votre avatar ?",
                deleteAvatar
            );

            modalConfirmDeleteAvatar.show();
        });
    }


    // CONTAINER PROFILE INFOS
    const containerFormInfos = document.getElementById("containerFormInfos");
    const containerShowInfos = document.getElementById("containerShowInfos");

    const btnUpdateInfos = containerShowInfos.querySelector("input#btnUpdateInfos");
    const btnCancelFormInfos = containerFormInfos.querySelector("input#btnCancelFormInfos");

    btnUpdateInfos.addEventListener("click", () => {

        containerShowInfos.classList.remove("d-flex");
        containerShowInfos.classList.add("d-none");
        containerFormInfos.classList.add("d-flex");
        containerFormInfos.classList.remove("d-none");
    });

    btnCancelFormInfos.addEventListener("click", () => {
        const username = containerShowInfos.querySelector("p[data-field='username']").textContent.trim();
        const email = containerShowInfos.querySelector("p[data-field='email']").textContent.trim();
        const inputUsername = containerFormInfos.querySelector("input#profile_infos_username");
        const inputEmail = containerFormInfos.querySelector("input#profile_infos_email");

        inputUsername.value = username;
        inputEmail.value = email;

        containerFormInfos.classList.remove("d-flex");
        containerFormInfos.classList.add("d-none");
        containerShowInfos.classList.add("d-flex");
        containerShowInfos.classList.remove("d-none");
    });
});