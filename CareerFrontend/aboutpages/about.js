let buttons = document.querySelectorAll("button");

buttons.forEach(button => {

    button.addEventListener("click", function(){

        button.style.background = "#3516a8";
        button.style.color = "white";

        setTimeout(()=>{
            button.style.background = "";
            button.style.color = "";
        },300);

    });

});