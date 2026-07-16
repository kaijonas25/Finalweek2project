(function () {
  "use strict";

  async function addProfileWidget() {
    if (document.querySelector(".account-widget")) {
      return;
    }

    try {
      const response = await fetch("../signupsheet/session.php", {
        credentials: "same-origin",
        cache: "no-store"
      });
      const session = await response.json();

      if (!response.ok || !session.authenticated) {
        return;
      }

      const widget = document.createElement("aside");
      widget.className = "account-widget";
      widget.setAttribute("aria-label", "Current account");

      const avatar = document.createElement("img");
      avatar.className = "account-avatar";
      avatar.src = "images/profile.png";
      avatar.alt = "Profile picture";

      const details = document.createElement("div");
      details.className = "account-details";

      const label = document.createElement("span");
      label.textContent = "Signed in as";

      const username = document.createElement("strong");
      username.textContent = session.username || "Titan Member";

      details.append(label, username);
      widget.append(avatar, details);
      document.body.prepend(widget);
    } catch (error) {
      console.warn("Titan profile widget could not be loaded.", error);
    }
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", addProfileWidget);
  } else {
    addProfileWidget();
  }
})();
