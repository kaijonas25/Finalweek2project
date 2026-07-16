<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
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

        const logIn = (event) => {
          event.preventDefault();

          const username = document.querySelector(".searchInput").value.trim();
          const password = document.querySelector(".passwordInput").value;

          if (!username || !password) {
            alert("Please enter both username and password.");
            return;
          }

          // Developer reset: remove all account data for this app.
          if (username === "developer-reset" && password === "admin-overide") {
            localStorage.removeItem("titanAccounts");
            localStorage.removeItem("titanCurrentUser");
            alert("Titan account storage has been reset.");
            window.location.href = "../signupsheet/signup.php";
            return;
          }

          let accounts = [];

          try {
            accounts = JSON.parse(localStorage.getItem("titanAccounts")) || [];
          } catch (error) {
            alert("The saved account information could not be read.");
            return;
          }

          const validAccount = accounts.find(function(account) {
            return account.username.toLowerCase() === username.toLowerCase() &&
              account.password === password;
          });

          if (!validAccount) {
            alert("Invalid username or password.");
            return;
          }

          localStorage.setItem("titanCurrentUser", validAccount.username);
            window.location.href = "../Homeonpage/Homeonpage.php";

        return (
          <div className="border">
          <form className="mainContainer" onSubmit={logIn}>
            <h1 className="header">Titan Strength and Performance</h1>

            <input
              className="searchInput"
              type="text"
              placeholder="Enter username"
              autoComplete="username"
            />

            <input
              className="passwordInput"
              type="password"
              placeholder="Enter password"
              autoComplete="current-password"
            />

            <div className="buttonContainer">
              <button className="loginButton" type="submit">
                Enter
              </button>

              <button
                className="signupButton"
                type="button"
                onClick={goToSignUp}
              >
                Sign Up
              </button>
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
