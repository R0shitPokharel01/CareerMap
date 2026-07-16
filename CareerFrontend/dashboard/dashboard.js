
document.querySelector(".welcome button").onclick = function () {
  alert("Opening your learning roadmap...");

};


const menus = document.querySelectorAll(".menu");

menus.forEach((menu) => {
  menu.addEventListener("click", function () {

    
    if (this.classList.contains("logout")) {
      if (confirm("Are you sure you want to log out?")) {
        alert("Logged out successfully!");
        window.location.href = "login.html"; // Change page name if needed
      }
      return;
    }

    
    menus.forEach((item) => {
      if (!item.classList.contains("logout")) {
        item.classList.remove("active");
      }
    });

    this.classList.add("active");
  });
});


document.querySelector(".add").onclick = function () {
  alert("Opening Add New Task...");
  // window.location.href = "tasks.html";
};


document.querySelector(".float").onclick = function () {
  alert("Quick Add feature coming soon!");
};


const ctx = document.getElementById("activityChart");

new Chart(ctx, {
  type: "line",

  data: {
    labels: ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"],

    datasets: [{
      label: "Activity",
      data: [5, 8, 6, 10, 9, 13, 11],
      borderColor: "#5b42f3",
      backgroundColor: "rgba(91,66,243,0.12)",
      borderWidth: 3,
      tension: 0.4,
      fill: true,
      pointBackgroundColor: "#5b42f3",
      pointRadius: 5,
    }]
  },

  options: {
    responsive: true,

    plugins: {
      legend: {
        display: false,
      },
    },

    scales: {
      y: {
        beginAtZero: true,
        grid: {
          color: "#eeeeee",
        },
      },

      x: {
        grid: {
          display: false,
        },
      },
    },
  },
});