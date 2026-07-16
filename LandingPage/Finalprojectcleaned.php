<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Titan Strength and Performance</title>

    <script src="https://cdn.jsdelivr.net/npm/react@18/umd/react.development.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/react-dom@18/umd/react-dom.development.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@babel/standalone/babel.min.js"></script>
    <link rel="stylesheet" href="FinalProjetctstyle.css">
    
  </head>

  <body>
    <div id="root"></div>
     
    <script type="text/babel">
      function App() {
        const goToSignUp = () => {
          window.location.href = "../signupsheet/signup.php";
        };

        const logIn = async (event) => {
          event.preventDefault();

          const username = document.querySelector(".searchInput").value.trim();
          const password = document.querySelector(".passwordInput").value;

          if (!username || !password) {
            alert("Please enter both username and password.");
            return;
          }

          try {
            const response = await fetch("http://localhost/Finalweek2project/signupsheet/account.php", {
              method: "POST",
              credentials: "include",
              headers: { "Content-Type": "application/json" },
              body: JSON.stringify({ action: "login", username, password })
            });
            const result = await response.json();

            if (!response.ok || !result.success) {
              throw new Error(result.message || "Invalid username or password.");
            }

            window.location.href = "../Homeonpage/Homeonpage.php";
          } catch (error) {
            alert(error.message || "The login service is unavailable.");
          }
        };
        return (
          <div className="border">
            <form className="mainContainer" onSubmit={logIn}>
              <p className="eyebrow">Member Access</p>
              <h1 className="header">Titan Strength and Performance</h1>
              <p className="subtitle">Sign in to continue to your training dashboard.</p>
              <div className="fieldGroup">
                <label htmlFor="username">Username</label>
                <input id="username" className="searchInput" type="text" placeholder="Enter your username" autoComplete="username" required />
                <label htmlFor="password">Password</label>
                <input id="password" className="passwordInput" type="password" placeholder="Enter your password" autoComplete="current-password" required />
              </div>
              <div className="buttonContainer">
                <button className="loginButton" type="submit">Log In</button>
                <button className="signupButton" type="button" onClick={goToSignUp}>Create Account</button>
              </div>
            </form>
          </div>
        );
      }

      const rootElement = document.getElementById("root");
      const root = ReactDOM.createRoot(rootElement);
      root.render(<App />);
    </script>

  </body>
</html>
