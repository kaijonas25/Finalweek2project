<?php
declare(strict_types=1);

session_start();

if (!isset($_SESSION["titanCurrentUser"])) {
    header("Location: ../LandingPage/Finalprojectcleaned.html");
    exit;
}

header("Cache-Control: no-store");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <link rel="stylesheet" href="home.css">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Titan Strength and Performance</title>
  
  
</head>
<body>

  <main class="home-card" id="home">
    <header class="hero">
      <p class="eyebrow">Titan Strength and Performance</p>
      <h1>Train Stronger. Move Faster.</h1>
      <p class="intro">Meet your coaches and explore programs designed to build strength, speed, and sport-specific performance.</p>
    </header>
    <section id="booking" aria-labelledby="trainers-heading">
      <h2 id="trainers-heading" class="section-title">Trainer Bios</h2>
      <div class="content-grid">
        <article class="info-card">
          <h3>Coach Damon Okafor</h3>
          <ul>
            <li>Was happy in the haze of a drunken hour</li>
            <li>Heaven knew he was miserable now</li>
            <li>He was looking for a job and he found a job</li>
          </ul>
        </article>
        <article class="info-card">
          <h3>Coach Goblinstein</h3>
          <ul>
            <li>Was happy in the haze of a drunken hour</li>
            <li>Heaven knew he was miserable now</li>
            <li>He was looking for a job and he found a job</li>
          </ul>
        </article>
      </div>
    </section>
    <section id="programs" aria-labelledby="programs-heading">
      <h2 id="programs-heading" class="section-title">Program Breakdown</h2>
      <div class="program-grid">
        <article class="program-card" onclick="window.location.href='wheightlifting.html'" ><span class="program-number">01</span><h3>Weightlifting</h3><p>Continuous weight training for building strength.</p></article>
        <article class="program-card" onclick="window.location.href='speed.html'"><span class="program-number" >02</span><h3>Speed Drills</h3><p>For speedy athletes who want to stay at their best.</p></article>
        <article class="program-card" onclick="window.location.href='sport.html'"><span class="program-number" >03</span><h3>Sport-Specific Conditioning</h3><p>Football, soccer, basketball, and baseball are all supported.</p></article>
      </div>
    </section>
  </main>
  
  <button class="chat-button" type="button" onclick="toggleChat()" aria-label="Open AI assistant">
    💬
  </button>

  <aside class="chat-box" id="chatBox" aria-label="AI assistant">
    <div class="chat-header">
      <img src="images/TechBot.png" alt="Assistant" onerror="this.style.display='none'">
      <div>
        <strong>Gym Bro</strong><br>
        <small>Ask me anything!</small>
      </div>
    </div>
    
    <div class="chat-messages" id="chatLog">
      <div class="message-row ai">
        <img class="avatar" src="images/TechBot.png" alt="AI" onerror="this.style.display='none'">
        <div class="message-bubble">
          Hi! How can I help you today?
        </div>
      </div>
    </div>
    
    <form class="chat-input-area" onsubmit="sendMessage(event)">
      <input
        id="userInput"
        type="text"
        placeholder="Type your message..."
        autocomplete="off"
      >
      <button type="submit">Send</button>
    </form>
  </aside>


  <script>
    // Handles opening and closing the window panel
    function toggleChat() {
      const chatBox = document.getElementById('chatBox');
      if (chatBox.style.display === 'none' || chatBox.style.display === '') {
        chatBox.style.display = 'flex';
      } else {
        chatBox.style.display = 'none';
      }
    }

    // Handles sending messages and triggering auto-reply
    async function sendMessage(event) {
      event.preventDefault(); // Prevents the page from refreshing on form submit
    
      const userInput = document.getElementById('userInput');
      const chatLog = document.getElementById('chatLog');
      const messageText = userInput.value.trim();
    
      if (messageText === '') return;

      // 1. Append User Message Bubble
      const userRow = document.createElement('div');
      userRow.className = 'message-row user';
      userRow.innerHTML = `<div class="message-bubble">${messageText}</div>`;
      chatLog.appendChild(userRow);

      // Clear the text bar instantly
      userInput.value = '';

      chatLog.innerHTML += `
        <div class="message-row ai" id="thinkingMessage">
          <img class="avatar" src="images/TechBot.png" alt="AI">
          <div class="message-bubble thinking">Thinking...</div>
        </div>
      `;

      chatLog.scrollTop = chatLog.scrollHeight;

      try {
  // Call your local XAMPP PHP backend instead
  const response = await fetch("chat.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json"
    },
    body: JSON.stringify({ message: messageText }) 
  });

  const data = await response.json();
  document.getElementById("thinkingMessage").remove();

  if (data.error) {
    chatLog.innerHTML += `
      <div class="message-row ai">
        <img class="avatar" src="images/TechBot.png" alt="AI">
        <div class="message-bubble">Error: ${data.error.message}</div>
      </div>`;
    return;
  }

  const reply = data.choices[0].message.content.trim();
  chatLog.innerHTML += `
    <div class="message-row ai">
      <img class="avatar" src="images/TechBot.png" alt="AI">
      <div class="message-bubble">${reply}</div>
    </div>`;

  chatLog.scrollTop = chatLog.scrollHeight;

}

    catch (error) {

      document.getElementById("thinkingMessage")?.remove();

      chatLog.innerHTML += `
        <div class="message-row ai">
          <img class="avatar" src="images/TechBot.png" alt="AI">
          <div class="message-bubble">
            Network error. Check your API connection.
          </div>
        </div>
      `;

    }
  }
  </script>

  <nav class="bottom-nav" aria-label="Homepage navigation">
    <a class="nav-item nav-home" href="#home">
      <span class="nav-icon" aria-hidden="true">⌂</span>
      <span>Home</span>
    </a>
    <a class="nav-item" href="Booking.html">
      <span class="nav-icon" aria-hidden="true">◆</span>
      <span>Booking</span>
    </a>
    <a class="nav-item" href="about.html">
      <span class="nav-icon" aria-hidden="true">★</span>
      <span>Programs</span>
    </a>
    <a class="nav-item" href="Reviews.html">
      <span class="nav-icon" aria-hidden="true">☆</span>
      <span>Reviews</span>
    </a>
    <button class="nav-item" type="button" onclick="toggleChat()">
      <span class="nav-icon" aria-hidden="true">●</span>
      <span>Chat</span>
    </button>
  </nav>

</body>
</html>
