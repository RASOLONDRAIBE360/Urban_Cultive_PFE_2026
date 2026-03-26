window.addEventListener("scroll", function() {
    let header = document.querySelector("header");
    
    if (window.scrollY > 50) { /* Change le style après 50px de scroll */
        header.classList.add("header-scroll");
    } else {
        header.classList.remove("header-scroll");
    }
});