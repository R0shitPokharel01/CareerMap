document.querySelector(".welcome button").onclick = function () {
  alert("Continue learning roadmap!");
};


const menus = document.querySelectorAll(".menu");

menus.forEach((menu) => {
  menu.addEventListener("click", function () {
    
    menus.forEach((item) => {
      item.classList.remove("active");
    });

    
    this.classList.add("active");
  });
});


function clicked(btn) {
  btn.style.background = "#4328d7";
  btn.style.color = "white";
}
document.querySelector(".float").onclick = function () {
  alert("Add new task clicked");
};
const ctx = document.getElementById("activityChart");

new Chart(ctx, {
  type: "line",

  data: {
    labels: ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"],

    datasets: [
      {
        label: "Activity",

        data: [5, 8, 6, 10, 9, 13, 11],

        borderColor: "#5b42f3",

        backgroundColor: "rgba(91,66,243,0.12)",

        borderWidth: 3,

        tension: 0.4,

        fill: true,

        pointBackgroundColor: "#5b42f3",

        pointRadius: 5,
      },
    ],
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
