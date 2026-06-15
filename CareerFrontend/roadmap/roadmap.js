document.addEventListener("DOMContentLoaded", () => {

    console.log("CareerFlow Roadmap Loaded");

    // Continue Lesson Button

    const lessonBtn =
    document.querySelector(".lesson-btn");

    if(lessonBtn){

        lessonBtn.addEventListener("click", () => {

            alert("Opening JavaScript Core Lesson");

        });

    }

    // Mentor Button

    const mentorBtn =
    document.querySelector(".mentor-btn");

    if(mentorBtn){

        mentorBtn.addEventListener("click", () => {

            alert("Mentor Check-In Scheduled");

        });

    }

    // Goal Button

    const goalBtn =
    document.querySelector(".goal-btn");

    if(goalBtn){

        goalBtn.addEventListener("click", () => {

            alert("Create New Goal");

        });

    }

    // Card Click Effect

    const cards =
    document.querySelectorAll(".card");

    cards.forEach(card => {

        card.addEventListener("click", () => {

            cards.forEach(c => {

                c.classList.remove("active-card");

            });

            card.classList.add("active-card");

        });

    });

});