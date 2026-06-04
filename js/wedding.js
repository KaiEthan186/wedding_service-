(function () {
  "use strict";

  var navToggle = document.querySelector("[data-nav-toggle]");
  var nav = document.querySelector("[data-nav]");
  var header = document.querySelector("[data-header]");

  if (navToggle && nav) {
    navToggle.addEventListener("click", function () {
      var open = nav.classList.toggle("is-open");
      navToggle.setAttribute("aria-expanded", open ? "true" : "false");
      navToggle.setAttribute("aria-label", open ? "Close menu" : "Open menu");
    });

    nav.querySelectorAll("a").forEach(function (link) {
      link.addEventListener("click", function () {
        if (window.matchMedia("(max-width: 768px)").matches) {
          nav.classList.remove("is-open");
          navToggle.setAttribute("aria-expanded", "false");
          navToggle.setAttribute("aria-label", "Open menu");
        }
      });
    });
  }

  /** Close mobile nav on resize to desktop */
  window.addEventListener("resize", function () {
    if (!nav || !navToggle) return;
    if (window.innerWidth > 768) {
      nav.classList.remove("is-open");
      navToggle.setAttribute("aria-expanded", "false");
      navToggle.setAttribute("aria-label", "Open menu");
    }
  });

  /** Hero stat counters */
  function animateValue(el, target, duration) {
    var start = 0;
    var startTime = null;

    function step(ts) {
      if (!startTime) startTime = ts;
      var p = Math.min((ts - startTime) / duration, 1);
      var eased = 1 - Math.pow(1 - p, 3);
      var current = Math.round(start + (target - start) * eased);
      el.textContent = String(current);
      if (p < 1) requestAnimationFrame(step);
    }

    requestAnimationFrame(step);
  }

  function runCounters() {
    var nodes = document.querySelectorAll("[data-counter]");
    if (!nodes.length) return;

    var io = new IntersectionObserver(
      function (entries, obs) {
        entries.forEach(function (entry) {
          if (!entry.isIntersecting) return;
          var el = entry.target;
          var raw = el.getAttribute("data-counter");
          var target = parseInt(raw || "0", 10);
          if (!isNaN(target)) {
            el.textContent = "0";
            animateValue(el, target, 1200);
          }
          obs.unobserve(el);
        });
      },
      { threshold: 0.35 }
    );

    nodes.forEach(function (n) {
      io.observe(n);
    });
  }

  if ("IntersectionObserver" in window) {
    runCounters();
  } else {
    document.querySelectorAll("[data-counter]").forEach(function (el) {
      var t = el.getAttribute("data-counter");
      if (t) el.textContent = t;
    });
  }

  /** Subtle tilt on gallery (desktop only) */
  var gallery = document.querySelector("[data-tilt]");
  if (gallery && window.matchMedia("(hover: hover) and (min-width: 900px)").matches) {
    gallery.addEventListener("mousemove", function (e) {
      var r = gallery.getBoundingClientRect();
      var x = (e.clientX - r.left) / r.width - 0.5;
      var y = (e.clientY - r.top) / r.height - 0.5;
      gallery.style.transform =
        "perspective(900px) rotateY(" + (x * 6).toFixed(2) + "deg) rotateX(" + (-y * 6).toFixed(2) + "deg)";
    });
    gallery.addEventListener("mouseleave", function () {
      gallery.style.transform = "";
    });
  }

  /** Rotate testimonial quotes */
  var quotes = [
    {
      text:
        "“They turned our vague Pinterest dreams into a night that felt like us—warm, sparkly, and stress-free.”",
      by: "— Mira & Jordan, vineyard wedding",
    },
    {
      text: "“Our families still talk about the flow of the evening. Every detail felt intentional and joyful.”",
      by: "— Priya & Sam, city loft celebration",
    },
    {
      text: "“Calm, organized, and wildly creative. We actually enjoyed planning because of this team.”",
      by: "— Elena & Theo, coastal weekend",
    },
  ];

  var block = document.querySelector("[data-quote-rotate]");
  /** Vendor registration: toggle business name */
  var regRole = document.getElementById("reg-role");
  var businessWrap = document.getElementById("business-wrap");
  if (regRole && businessWrap) {
    function toggleBusiness() {
      businessWrap.classList.toggle("is-hidden", regRole.value !== "vendor");
    }
    regRole.addEventListener("change", toggleBusiness);
    toggleBusiness();
  }

  /** Profile dropdown (click on mobile / touch) */
  var profileToggle = document.querySelector("[data-profile-toggle]");
  var profileMenu = profileToggle ? profileToggle.closest(".profile-menu") : null;

  if (profileToggle && profileMenu) {
    profileToggle.addEventListener("click", function (e) {
      e.stopPropagation();
      var open = profileMenu.classList.toggle("is-open");
      profileToggle.setAttribute("aria-expanded", open ? "true" : "false");
    });

    document.addEventListener("click", function (e) {
      if (!profileMenu.contains(e.target)) {
        profileMenu.classList.remove("is-open");
        profileToggle.setAttribute("aria-expanded", "false");
      }
    });
  }

  if (block && quotes.length > 1) {
    var p = block.querySelector(".quote__text");
    var foot = block.querySelector("footer");
    var i = 0;

    function prefersReducedMotion() {
      return window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    }

    function show(idx) {
      var q = quotes[idx];
      if (p) p.textContent = q.text;
      if (foot) foot.textContent = q.by;
    }

    if (!prefersReducedMotion()) {
      setInterval(function () {
        i = (i + 1) % quotes.length;
        show(i);
      }, 7000);
    }
  }
})();
