document.addEventListener("DOMContentLoaded", function () {
  const toggles = document.querySelectorAll(".toggle-password");
  toggles.forEach(icon => {
    icon.addEventListener("click", () => {
      const inputId = icon.dataset.target;
      const input = document.getElementById(inputId);

      if (!input) return;

      if (input.type === "password") {
        input.type = "text";
        icon.textContent = "⌣";
      } else {
        input.type = "password";
        icon.textContent = "👁";
      }
    });
  });

  const authLinks = document.querySelectorAll(".auth-wrap a");
  const authWrap = document.querySelector(".auth-wrap");

  authLinks.forEach(link => {
      link.addEventListener("click", function(e) {
          if (this.getAttribute("href") && 
              this.getAttribute("href") !== "#" && 
              !this.getAttribute("href").startsWith("javascript")) {
              
              e.preventDefault(); 
              const targetUrl = this.href;
              authWrap.classList.add("fade-out");
              setTimeout(() => {
                  window.location.href = targetUrl;
              }, 400);
          }
      });
  });

  const authForms = document.querySelectorAll(".auth-wrap form");
  
  authForms.forEach(form => {
      form.addEventListener("submit", function(e) {
          e.preventDefault();
          authWrap.classList.add("fade-out");
          setTimeout(() => {
              form.submit();
          }, 400);
      });
  });


  const sidebar = document.getElementById("sidebar");
  const openSidebarBtn = document.getElementById("openSidebar");
  const closeSidebarBtn = document.getElementById("closeSidebar");

  if (openSidebarBtn && sidebar) {
      openSidebarBtn.onclick = () => sidebar.classList.add("show");
  }
  if (closeSidebarBtn && sidebar) {
      closeSidebarBtn.onclick = () => sidebar.classList.remove("show");
  }


  if (typeof feather !== 'undefined') {
      feather.replace();
  }
});
