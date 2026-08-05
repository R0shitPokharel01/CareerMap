function renderSidebar() {
  sidebarMenu.innerHTML = "";

  menus.forEach((menu) => {
    const template = menuTemplate.content.cloneNode(true);
    const link = template.querySelector("a");
    const icon = template.querySelector("i");
    const text = template.querySelector("span");

    icon.className = `fa-solid ${menu.icon}`;
    text.textContent = menu.title;
    link.href = menu.link || "#";

    if (menu.active) {
      link.classList.add("active");
    }

    link.addEventListener("click", () => {
      document
        .querySelectorAll("#sidebarMenu a")
        .forEach((item) => item.classList.remove("active"));
      link.classList.add("active");
    });

    sidebarMenu.appendChild(template);
  });
}