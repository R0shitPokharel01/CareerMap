// ===========================================
// Progress Bar Animation
// ===========================================

document.addEventListener("DOMContentLoaded", () => {

    const progress = document.querySelector(".progress-fill");

    let width = 0;
    const target = 74;

    const animate = setInterval(() => {

        if (width >= target) {
            clearInterval(animate);
        } else {
            width++;
            progress.style.width = width + "%";
        }

    }, 15);

});

// ===========================================
// Search Box
// ===========================================

const searchInput = document.querySelector(".search input");

if (searchInput) {

    searchInput.addEventListener("keyup", function () {

        console.log("Searching:", this.value);

    });

}

// ===========================================
// Edit Profile Button
// ===========================================

const editBtn = document.querySelector(".edit-btn");

if (editBtn) {

    editBtn.addEventListener("click", () => {

        alert("Edit Profile clicked!");

    });

}

// ===========================================
// Share Button
// ===========================================

const shareBtn = document.querySelector(".share-btn");

if (shareBtn) {

    shareBtn.addEventListener("click", () => {

        if (navigator.share) {

            navigator.share({

                title: "CareerPath Profile",

                text: "Check out my CareerPath profile!",

                url: window.location.href

            });

        } else {

            navigator.clipboard.writeText(window.location.href);

            alert("Profile link copied!");

        }

    });

}

// ===========================================
// Card Hover Effect
// ===========================================

const cards = document.querySelectorAll(".card");

cards.forEach(card => {

    card.addEventListener("mouseenter", () => {

        card.style.transform = "translateY(-6px)";
        card.style.transition = ".3s";

    });

    card.addEventListener("mouseleave", () => {

        card.style.transform = "translateY(0px)";

    });

});

// ===========================================
// Sidebar Active Item
// ===========================================

const menuItems = document.querySelectorAll(".sidebar li");

menuItems.forEach(item => {

    item.addEventListener("click", () => {

        menuItems.forEach(i => i.classList.remove("active"));

        item.classList.add("active");

    });

});

// ===========================================
// Skill Chips Hover
// ===========================================

const chips = document.querySelectorAll(".chips span");

chips.forEach(chip => {

    chip.addEventListener("mouseenter", () => {

        chip.style.transform = "scale(1.05)";

    });

    chip.addEventListener("mouseleave", () => {

        chip.style.transform = "scale(1)";

    });

});

// ===========================================
// Project Image Zoom
// ===========================================

const projects = document.querySelectorAll(".project img");

projects.forEach(image => {

    image.addEventListener("mouseenter", () => {

        image.style.transition = ".35s";
        image.style.transform = "scale(1.05)";

    });

    image.addEventListener("mouseleave", () => {

        image.style.transform = "scale(1)";

    });

});

// ===========================================
// Notification Icon
// ===========================================

const bell = document.querySelector(".fa-bell");

if (bell) {

    bell.addEventListener("click", () => {

        alert("No new notifications.");

    });

}

// ===========================================
// Smooth Scroll
// ===========================================

document.documentElement.style.scrollBehavior = "smooth";

// ===========================================
// Page Fade In
// ===========================================

document.body.style.opacity = "0";

window.onload = () => {

    document.body.style.transition = "opacity .6s ease";
    document.body.style.opacity = "1";

};