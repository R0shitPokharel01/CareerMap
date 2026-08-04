document.addEventListener("DOMContentLoaded", () => {

    const searchInput =
        document.querySelector(".search-box input");

    searchInput.addEventListener("keyup", () => {

        const value =
            searchInput.value.toLowerCase();

        const cards =
            document.querySelectorAll(
                ".big-card,.skill-card,.knowledge-card,.locked-card"
            );

        cards.forEach(card => {

            const text =
                card.innerText.toLowerCase();

            if (text.includes(value)) {
                card.style.display = "block";
            } else {
                card.style.display = "none";
            }

        });

    });

});