// Dashboard Statistics

let totalUsers = 1248;
let totalCareers = 32;
let totalCourses = 89;
let avgProgress = 74;

document.getElementById("users").innerText = totalUsers;
document.getElementById("careers").innerText = totalCareers;
document.getElementById("courses").innerText = totalCourses;
document.getElementById("progress").innerText = avgProgress + "%";

// Quick Action Buttons

const buttons = document.querySelectorAll(".actions button");

buttons.forEach(button => {
    button.addEventListener("click", () => {
        alert(button.innerText + " feature will be connected to backend later.");
    });
});