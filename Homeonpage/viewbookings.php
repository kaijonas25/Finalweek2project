<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="viewbookings.css?v=1">
  <link rel="stylesheet" href="profile-widget.css?v=1">
  <script src="profile-widget.js?v=1" defer></script>
  <title>My Training Calendar | Titan Strength</title>
</head>
<body>
  <main class="calendar-shell">
    <header class="calendar-hero">
      <a class="back-link" href="BookingPage.php">&larr; Back to booking</a>
      <p class="eyebrow">Titan Strength and Performance</p>
      <h1>My Training Calendar</h1>
      <p id="calendarStatus" class="calendar-status" role="status" aria-live="polite">Loading your training sessions...</p>
    </header>

    <section class="calendar-panel" aria-labelledby="calendarMonth">
      <div class="calendar-toolbar">
        <button id="previousMonth" type="button">Previous month</button>
        <h2 id="calendarMonth"></h2>
        <button id="nextMonth" type="button">Next month</button>
      </div>

      <div class="calendar-scroll">
        <table class="calendar-table">
          <thead>
            <tr>
              <th scope="col">Sunday</th>
              <th scope="col">Monday</th>
              <th scope="col">Tuesday</th>
              <th scope="col">Wednesday</th>
              <th scope="col">Thursday</th>
              <th scope="col">Friday</th>
              <th scope="col">Saturday</th>
            </tr>
          </thead>
          <tbody id="calendarBody"></tbody>
        </table>
      </div>
    </section>
  </main>

  <script>
    const loginPage = "../LandingPage/Finalprojectcleaned.php";
    const calendarStatus = document.getElementById("calendarStatus");
    const calendarMonth = document.getElementById("calendarMonth");
    const calendarBody = document.getElementById("calendarBody");
    const previousMonth = document.getElementById("previousMonth");
    const nextMonth = document.getElementById("nextMonth");

    let displayedMonth = new Date();
    displayedMonth.setDate(1);
    let bookingsByDate = new Map();

    function dateKey(year, month, day) {
      return `${year}-${String(month + 1).padStart(2, "0")}-${String(day).padStart(2, "0")}`;
    }

    function formatTime(time) {
      const [hours, minutes] = time.split(":").map(Number);
      return new Intl.DateTimeFormat("en-US", {
        hour: "numeric",
        minute: "2-digit"
      }).format(new Date(2000, 0, 1, hours, minutes));
    }

    function renderCalendar() {
      const year = displayedMonth.getFullYear();
      const month = displayedMonth.getMonth();
      const firstWeekday = new Date(year, month, 1).getDay();
      const daysInMonth = new Date(year, month + 1, 0).getDate();

      calendarMonth.textContent = displayedMonth.toLocaleDateString("en-US", {
        month: "long",
        year: "numeric"
      });
      calendarBody.replaceChildren();

      let day = 1;
      while (day <= daysInMonth) {
        const row = document.createElement("tr");

        for (let weekday = 0; weekday < 7; weekday += 1) {
          const cell = document.createElement("td");

          if ((day === 1 && weekday < firstWeekday) || day > daysInMonth) {
            row.appendChild(cell);
            continue;
          }

          const dayNumber = document.createElement("strong");
          dayNumber.className = "calendar-day-number";
          dayNumber.textContent = day;
          cell.appendChild(dayNumber);

          const bookings = bookingsByDate.get(dateKey(year, month, day)) || [];
          if (bookings.length > 0) {
            dayNumber.classList.add("has-booking");
            const list = document.createElement("ul");

            bookings.forEach((booking) => {
              const item = document.createElement("li");
              item.textContent = `${formatTime(booking.booking_time)} — ${booking.program} with ${booking.trainer}`;

              if (booking.notes) {
                const notes = document.createElement("p");
                notes.textContent = booking.notes;
                item.appendChild(notes);
              }

              list.appendChild(item);
            });

            cell.appendChild(list);
          }

          row.appendChild(cell);
          day += 1;
        }

        calendarBody.appendChild(row);
      }
    }

    async function loadBookings() {
      try {
        const response = await fetch("get-bookings.php", {
          credentials: "same-origin",
          cache: "no-store"
        });
        const result = await response.json();

        if (response.status === 401) {
          window.location.replace(loginPage);
          return;
        }

        if (!response.ok || !result.success) {
          throw new Error(result.message || "Your bookings could not be loaded.");
        }

        bookingsByDate = new Map();
        result.bookings.forEach((booking) => {
          const dayBookings = bookingsByDate.get(booking.booking_date) || [];
          dayBookings.push(booking);
          bookingsByDate.set(booking.booking_date, dayBookings);
        });

        calendarStatus.textContent = result.bookings.length === 0
          ? "You do not have any booked training sessions yet."
          : `${result.bookings.length} booked training session${result.bookings.length === 1 ? "" : "s"}.`;
        renderCalendar();
      } catch (error) {
        calendarStatus.textContent = error.message || "The booking calendar is unavailable.";
      }
    }

    previousMonth.addEventListener("click", () => {
      displayedMonth.setMonth(displayedMonth.getMonth() - 1);
      renderCalendar();
    });

    nextMonth.addEventListener("click", () => {
      displayedMonth.setMonth(displayedMonth.getMonth() + 1);
      renderCalendar();
    });

    loadBookings();
  </script>
</body>
</html>
