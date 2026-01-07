$(document).ready(function () {
  // Initialize First Carousel
  $("#hero-carousel").owlCarousel({
    loop: true,
    margin: 0,
    nav: false,
    dots: true,
    responsive: {
      0: {
        items: 1,
      },
    },
  });

  // dolllbear carousel
  var owl = $("#dollbear-carousel");
  owl.owlCarousel({
    // Owl Carousel options here
    loop: true,
    margin: 30,
    nav: true,
    dots: false,
    navText: [
      `<svg width="6" height="10" viewBox="0 0 6 10" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5.5 10L0.5 5L5.5 0V10Z" fill="black"/></svg>`,
      `<svg width="6" height="10" viewBox="0 0 6 10" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M0.500001 -4.37114e-07L5.5 5L0.5 10L0.500001 -4.37114e-07Z" fill="black"/></svg>`,
    ],
    responsive: {
      0: {
        items: 1,
      },
      575: {
        items: 2,
      },
      992: {
        items: 3,
      },
      1200: {
        items: 4,
      },
    },
  });

  // Check viewport width
  function checkWidth() {
    var windowWidth = $(window).width();
    if (windowWidth < 768) {
      // Adjust this value based on your mobile breakpoint
      owl.trigger("destroy.owl.carousel"); // Stop carousel on mobile
    } else {
      owl.owlCarousel(); // Restart carousel on larger screens
    }
  }

  // Call checkWidth() on load
  checkWidth();

  // Call checkWidth() on window resize
  $(window).resize(function () {
    checkWidth();
  });
});

// Get all accordion items
const accordionItems = document.querySelectorAll(".recomonded-accordion-item");

// Loop through each accordion item
accordionItems.forEach((item) => {
  // Add event listeners for hover
  item.addEventListener("mouseover", () => {
    // Remove 'active' class from all item-text elements
    document.querySelectorAll(".item-text").forEach((text) => {
      text.classList.remove("active");
    });
    document.querySelectorAll(".item-text").forEach((text) => {
      text.classList.remove("active");
    });
    // Add 'active' class to item-text in current accordion item
    item.querySelector(".item-text").classList.add("active");
  });

  // Add mouseout event to keep 'active' class when mouse leaves the item
  item.addEventListener("mouseout", () => {
    item.querySelector(".item-text").classList.add("active");
  });
});

// navbar slide effect

// const menu = document.querySelector(".menu");
// const menuMain = menu.querySelector(".menu-main");
// const goBack = menu.querySelector(".go-back");
// const menuTrigger = document.querySelector(".mobile-menu-trigger");
// const closeMenu = menu.querySelector(".mobile-menu-close");
// let subMenu;

// menuMain.addEventListener("click", (e) => {
//   if (!menu.classList.contains("active")) {
//     return;
//   }
//   if (e.target.closest(".menu-item-has-children")) {
//     const hasChildren = e.target.closest(".menu-item-has-children");
//     showSubMenu(hasChildren);
//   }
// });

// goBack.addEventListener("click", () => {
//   hideSubMenu();
// });

// menuTrigger.addEventListener("click", () => {
//   toggleMenu();
// });

// closeMenu.addEventListener("click", () => {
//   toggleMenu();
// });

// document.querySelector(".menu-overlay").addEventListener("click", () => {
//   toggleMenu();
// });

// function toggleMenu() {
//   menu.classList.toggle("active");
//   document.querySelector(".menu-overlay").classList.toggle("active");
// }

// function showSubMenu(hasChildren) {
//   subMenu = hasChildren.querySelector(".sub-menu");
//   subMenu.classList.add("active");
//   subMenu.style.animation = "slideLeft 0.5s ease forwards";
//   const menuTitle = hasChildren.querySelector("a").textContent; // Changed from "i" to "a"
//   menu.querySelector(".current-menu-title").innerHTML = menuTitle;
//   menu.querySelector(".mobile-menu-head").classList.add("active"); // Adding active class to mobile-menu-head
// }

// function hideSubMenu() {
//   subMenu.style.animation = "slideRight 0.5s ease forwards";
//   setTimeout(() => {
//     subMenu.classList.remove("active");
//   }, 300);
//   menu.querySelector(".current-menu-title").innerHTML = "";
//   menu.querySelector(".mobile-menu-head").classList.remove("active"); // Removing active class from mobile-menu-head
// }

// window.onresize = function () {
//   if (this.innerWidth > 991) {
//     if (menu.classList.contains("active")) {
//       toggleMenu();
//     }
//   }
// };

// input plus minus number increased in cart section
document.addEventListener("DOMContentLoaded", function () {
  const quantityInput = document.querySelector(
    ".cart-item-details-btn-quantity"
  );
  quantityInput.value = "0"; // Set the initial value

  const minusButton = document.querySelector(".minus");
  minusButton.addEventListener("click", () => {
    let currentQuantity = parseInt(quantityInput.value);
    if (currentQuantity > 0) {
      currentQuantity--;
      quantityInput.value = currentQuantity;
    }
  });

  const plusButton = document.querySelector(".plus");
  plusButton.addEventListener("click", () => {
    let currentQuantity = parseInt(quantityInput.value);
    currentQuantity++;
    quantityInput.value = currentQuantity;
  });
});

// mobile search
// Get elements
// const searchIcon = document.querySelector(".mobile-serach-icon");
// const mobileSearch = document.querySelector(".mobile-search");
// const closeSearch = document.querySelector(".close-search");

// // Add event listener to search icon
// searchIcon.addEventListener("click", function () {
//   // mobileSearch.style.display = 'block'; // Show mobile search
//   mobileSearch.classList.add("show"); // Add 'show' class to mobile search
// });

// // Add event listener to close button
// closeSearch.addEventListener("click", function () {
//   mobileSearch.classList.remove("show"); // Remove 'show' class from mobile search
// });
