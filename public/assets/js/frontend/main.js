// Hand Burger Js

const hamburger = document.getElementById("hamburger");
const navbar = document.getElementById("navbar");

hamburger.addEventListener("click", () => {
  navbar.classList.toggle("active");

  hamburger.classList.toggle("active");
});

// Tabs Js

function openCity(event, cityName) {
  let city = document.getElementsByClassName("city");

  for (let i = 0; i < city.length; i++) {
    city[i].style.display = "none";
  }

  let buttons = document.getElementsByClassName("tab-btn");

  for (let i = 0; i < buttons.length; i++) {
    buttons[i].classList.remove("active");
  }

  document.getElementById(cityName).style.display = "block";

  event.currentTarget.classList.add("active");
}