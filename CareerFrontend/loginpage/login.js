      
      // CareerFlow Authentication
       

      const BASE_URL = "http://localhost:8000/api";

      // DOM Elements
      const form = document.getElementById("authForm");

      const loginTab = document.getElementById("loginTab");
      const registerTab = document.getElementById("registerTab");

      const formTitle = document.getElementById("formTitle");
      const formSubtitle = document.getElementById("formSubtitle");

      const submitBtn = document.getElementById("submitBtn");

      const footerMessage = document.getElementById("footerMessage");
      const switchMode = document.getElementById("switchMode");

      const registerFields = document.querySelectorAll(".register-field");
      const loginOnly = document.querySelector(".login-only");

      const togglePassword = document.getElementById("togglePassword");

      const password = document.getElementById("password");
      const email = document.getElementById("email");
      const nameInput = document.getElementById("name");

      const message = document.getElementById("message");

       
      // Current Mode
       

      let isLogin = false;

       
      // Initialize
       

      showRegister();

       
      // Password Toggle
       

      togglePassword.addEventListener("click", () => {
        if (password.type === "password") {
          password.type = "text";
          togglePassword.textContent = "🙈";
        } else {
          password.type = "password";
          togglePassword.textContent = "👁";
        }
      });

       
      // Login Tab
       

      loginTab.addEventListener("click", showLogin);

       
      // Register Tab
       

      registerTab.addEventListener("click", showRegister);

       
      // Footer Switch
       

      switchMode.addEventListener("click", (e) => {
        e.preventDefault();

        if (isLogin) {
          showRegister();
        } else {
          showLogin();
        }
      });

       
      // Login Layout
       

      function showLogin() {
        isLogin = true;

        formTitle.textContent = "Welcome Back";

        formSubtitle.textContent = "Login to continue your career journey.";

        submitBtn.textContent = "Login";

        footerMessage.textContent = "Don't have an account?";

        switchMode.textContent = "Register";

        loginTab.classList.add("active");
        registerTab.classList.remove("active");

        registerFields.forEach((field) => {
          field.style.display = "none";
        });

        loginOnly.style.display = "flex";

        message.textContent = "";
      }

       
      // Register Layout
       

      function showRegister() {
        isLogin = false;

        formTitle.textContent = "Create an Account";

        formSubtitle.textContent = "Start your career roadmap journey.";

        submitBtn.textContent = "Create Account";

        footerMessage.textContent = "Already have an account?";

        switchMode.textContent = "Login";

        registerTab.classList.add("active");
        loginTab.classList.remove("active");

        registerFields.forEach((field) => {
          field.style.display = "block";
        });

        loginOnly.style.display = "none";

        message.textContent = "";
      }

       
      // Form Submit
       

      form.addEventListener("submit", async (e) => {
        e.preventDefault();

        message.textContent = "";

        // Validation

        if (!email.value.trim()) {
          showMessage("Email is required.", "red");
          return;
        }

        if (!password.value.trim()) {
          showMessage("Password is required.", "red");
          return;
        }

        if (!isLogin && !nameInput.value.trim()) {
          showMessage("Full name is required.", "red");
          return;
        }

        submitBtn.disabled = true;
        submitBtn.textContent = "Please wait...";

        try {
          const endpoint = isLogin ? "/login" : "/register";

          const body = isLogin
            ? {
                email: email.value,
                password: password.value,
              }
            : {
                name: nameInput.value,
                email: email.value,
                password: password.value,
              };

          const response = await fetch(BASE_URL + endpoint, {
            method: "POST",

            headers: {
              "Content-Type": "application/json",
            },

            body: JSON.stringify(body),
          });

          const result = await response.json();

          if (response.ok) {
            showMessage(result.message || "Success!", "green");

            // Save Token

            if (result.token) {
              localStorage.setItem("token", result.token);
            }

            // Redirect after Login

            if (isLogin) {
              setTimeout(() => {
                window.location.href = "dashboard.html";
              }, 1200);
            } else {
              setTimeout(() => {
                showLogin();
              }, 1500);
            }
          } else {
            showMessage(
              result.message || "Something went wrong.",

              "red",
            );
          }
        } catch (error) {
          console.error(error);

          showMessage(
            "Unable to connect to server.",

            "red",
          );
        } finally {
          submitBtn.disabled = false;

          submitBtn.textContent = isLogin ? "Login" : "Create Account";
        }
      });

       
      // Message
       

      function showMessage(text, color) {
        message.textContent = text;
        message.style.color = color;
      }
