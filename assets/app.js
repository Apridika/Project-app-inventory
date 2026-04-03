const sidebar = document.getElementById("sidebar");
const collapseBtn = document.getElementById("collapseBtn");
const main = document.getElementById("main");

// Toggle collapse
collapseBtn.addEventListener("click", () => {
  sidebar.classList.toggle("collapsed");
});

/* Date */
document.getElementById("todayDate").textContent =
  new Date().toLocaleDateString("id-ID", {
    weekday: "short",
    day: "numeric",
    month: "short",
    year: "numeric",
  });

// Active menu switching
const navItems = document.querySelectorAll(".nav-item");
navItems.forEach((item) => {
  item.addEventListener("click", function (e) {
    // Skip logout
    if (this.querySelector(".nav-text")?.textContent === "Logout") return;
    navItems.forEach((n) => n.classList.remove("active"));
    this.classList.add("active");
  });
});

// Dropdown toggle
const dropdowns = document.querySelectorAll(".dropdown");

dropdowns.forEach((drop) => {
  drop.addEventListener("click", () => {
    drop.classList.toggle("open");
  });
});
