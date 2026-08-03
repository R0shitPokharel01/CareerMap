// Progress animation

window.addEventListener("load", () => {

    const progress = document.querySelector(".progress-fill");

    let width = 0;

    const interval = setInterval(() => {

        if(width >= 65){
            clearInterval(interval);
        }else{
            width++;
            progress.style.width = width + "%";
        }

    }, 20);

});


// Roadmap Button

const roadmapBtn = document.querySelector(".roadmap-btn");

roadmapBtn.addEventListener("click", () => {
    alert("Redirecting to Full Roadmap...");
});


// Continue Learning Button

const continueBtn = document.querySelector(".continue-btn");

continueBtn.addEventListener("click", () => {
    alert("Continue Learning Clicked!");
});
