console.log("login.js loaded");
document.addEventListener("DOMContentLoaded", function () {
  console.log("DOM ready");
  const form = document.getElementById("loginForm");

  form.addEventListener("submit", function (e) {
    e.preventDefault(); // 🚫 stop refresh

    const formData = new FormData(form);

    fetch(form.action, {
      method: "POST",
      body: formData,
      headers: {
        "X-Requested-With": "XMLHttpRequest",
      },
    })
      .then((response) => response.json())
      .then((data) => {
        const alertBox = document.querySelector(".alert");
        if (data.success) {
          window.location.href = data.redirect;
        } else {
          alertBox.textContent = data.message;

          alertBox.style.display = "block";
        }
      })
      .catch((error) => console.error(error));
  });
});
