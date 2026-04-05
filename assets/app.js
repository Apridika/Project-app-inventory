const sidebar = document.getElementById("sidebar");
const collapseBtn = document.getElementById("collapseBtn");
const todayDate = document.getElementById("todayDate");

if (collapseBtn && sidebar) {
  collapseBtn.addEventListener("click", () => {
    sidebar.classList.toggle("collapsed");
  });
}

if (todayDate) {
  todayDate.textContent = new Date().toLocaleDateString("id-ID", {
    weekday: "short",
    day: "numeric",
    month: "short",
    year: "numeric",
  });
}

const navItems = document.querySelectorAll(".nav-item");
navItems.forEach((item) => {
  item.addEventListener("click", function () {
    if (this.querySelector(".nav-text")?.textContent === "Logout") return;
    navItems.forEach((n) => n.classList.remove("active"));
    this.classList.add("active");
  });
});

const dropdowns = document.querySelectorAll(".dropdown");
dropdowns.forEach((drop) => {
  drop.addEventListener("click", () => {
    drop.classList.toggle("open");
  });
});