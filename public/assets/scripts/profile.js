window.addEventListener("DOMContentLoaded", () => {
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