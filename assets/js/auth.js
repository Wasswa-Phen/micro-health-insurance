document.addEventListener("DOMContentLoaded", function () {

    // -------------------------------
    // SHARED HELPERS
    // -------------------------------

    const PROFILE_KEY = "afyamic ro_demo_profile".replace(" ", "");

    function showError(id, message) {
        const element = document.getElementById(id);
        if (element) element.textContent = message;
    }

    function clearErrors() {
        document.querySelectorAll(".field-error").forEach(function (element) {
            element.textContent = "";
        });
    }

    function showFeedback(element, message, type) {
        if (!element) return;

        element.textContent = message;
        element.className = "auth-feedback " + type;
    }

    function getPhoneNumber(value) {
        let phone = value.replace(/\s+/g, "").replace(/-/g, "");

        if (phone.startsWith("+256")) {
            phone = "0" + phone.substring(4);
        }

        return phone;
    }

    function validUgandanPhone(value) {
        return /^07[0-9]{8}$/.test(getPhoneNumber(value));
    }

    function getSavedProfile() {
        try {
            const saved = localStorage.getItem(PROFILE_KEY);
            return saved ? JSON.parse(saved) : null;
        } catch (error) {
            return null;
        }
    }

    // -------------------------------
    // SHOW / HIDE PASSWORD
    // -------------------------------

    document.querySelectorAll("[data-password-toggle]").forEach(function (button) {
        button.addEventListener("click", function () {
            const inputId = button.getAttribute("data-password-toggle");
            const passwordInput = document.getElementById(inputId);

            if (!passwordInput) return;

            const isPassword = passwordInput.type === "password";

            passwordInput.type = isPassword ? "text" : "password";
            button.textContent = isPassword ? "Hide" : "Show";
            button.setAttribute(
                "aria-label",
                isPassword ? "Hide password" : "Show password"
            );
        });
    });

    // -------------------------------
    // LOGIN FORM
    // -------------------------------

    const loginForm = document.getElementById("loginForm");

    if (loginForm) {
        const phoneInput = document.getElementById("loginPhone");
        const passwordInput = document.getElementById("loginPassword");
        const rememberMe = document.getElementById("rememberMe");
        const feedback = document.getElementById("loginFeedback");

        // Restore remembered phone number.
        try {
            const rememberedPhone = localStorage.getItem("afyamic ro_remembered_phone".replace(" ", ""));
            if (rememberedPhone && phoneInput) {
                phoneInput.value = rememberedPhone;
                rememberMe.checked = true;
            }
        } catch (error) {
            console.warn("Remembered phone number could not be loaded.");
        }

        loginForm.addEventListener("submit", function (event) {
            event.preventDefault();
            clearErrors();

            if (feedback) {
                feedback.className = "auth-feedback";
                feedback.textContent = "";
            }

            const phone = getPhoneNumber(phoneInput.value.trim());
            const password = passwordInput.value;

            let valid = true;

            if (!validUgandanPhone(phone)) {
                showError(
                    "loginPhoneError",
                    "Enter a valid Ugandan number, for example 0712345678."
                );
                valid = false;
            }

            if (password.length < 6) {
                showError(
                    "loginPasswordError",
                    "Your password must contain at least 6 characters."
                );
                valid = false;
            }

            if (!valid) {
                showFeedback(
                    feedback,
                    "Please correct the highlighted fields.",
                    "error"
                );
                return;
            }

            const profile = getSavedProfile();

            if (!profile || profile.phone !== phone) {
                showFeedback(
                    feedback,
                    "No demonstration account was found for this phone number in this browser. Please create an account first.",
                    "error"
                );
                return;
            }

            try {
                if (rememberMe.checked) {
                    localStorage.setItem("afyamic ro_remembered_phone".replace(" ", ""), phone);
                } else {
                    localStorage.removeItem("afyamic ro_remembered_phone".replace(" ", ""));
                }
            } catch (error) {
                console.warn("Remember Me preference could not be saved.");
            }

            showFeedback(
                feedback,
                "Welcome back, " + profile.fullName +
                "! Your demonstration sign-in was successful. Real authentication will require a secure backend.",
                "success"
            );
        });

        const forgotLink = document.getElementById("forgotPasswordLink");

        if (forgotLink) {
            forgotLink.addEventListener("click", function (event) {
                event.preventDefault();

                showFeedback(
                    feedback,
                    "Password recovery is not connected yet. It will be added when the secure authentication backend is implemented.",
                    "error"
                );
            });
        }
    }

    // -------------------------------
    // REGISTRATION FORM
    // -------------------------------

    const registerForm = document.getElementById("registerForm");

    if (registerForm) {
        registerForm.addEventListener("submit", function (event) {
            event.preventDefault();
            clearErrors();

            const feedback = document.getElementById("registerFeedback");

            if (feedback) {
                feedback.className = "auth-feedback";
                feedback.textContent = "";
            }

            const fullName = document.getElementById("fullName").value.trim();
            const phone = getPhoneNumber(
                document.getElementById("registerPhone").value.trim()
            );
            const nin = document.getElementById("nin").value.trim();
            const homePharmacy = document.getElementById("homePharmacy").value.trim();
            const password = document.getElementById("registerPassword").value;
            const confirmPassword = document.getElementById("confirmPassword").value;
            const acceptTerms = document.getElementById("acceptTerms").checked;

            let valid = true;

            if (fullName.length < 2) {
                showError("fullNameError", "Please enter your full name.");
                valid = false;
            }

            if (!validUgandanPhone(phone)) {
                showError(
                    "registerPhoneError",
                    "Enter a valid Ugandan number, for example 0712345678."
                );
                valid = false;
            }

            if (homePharmacy.length < 2) {
                showError(
                    "homePharmacyError",
                    "Please enter your preferred home pharmacy."
                );
                valid = false;
            }

            if (password.length < 6) {
                showError(
                    "registerPasswordError",
                    "Your password must contain at least 6 characters."
                );
                valid = false;
            }

            if (confirmPassword !== password || confirmPassword.length === 0) {
                showError(
                    "confirmPasswordError",
                    "Your passwords do not match."
                );
                valid = false;
            }

            if (!acceptTerms) {
                showError(
                    "termsError",
                    "You must agree to the Terms of Use and Privacy Policy."
                );
                valid = false;
            }

            if (!valid) {
                showFeedback(
                    feedback,
                    "Please correct the highlighted fields before continuing.",
                    "error"
                );
                return;
            }

            const profile = {
                fullName: fullName,
                phone: phone,
                nin: nin,
                homePharmacy: homePharmacy
            };

            try {
                localStorage.setItem(PROFILE_KEY, JSON.stringify(profile));

                showFeedback(
                    feedback,
                    "Your demonstration profile has been created successfully! You can now sign in using your phone number. This is a frontend demonstration, not a real insurance registration.",
                    "success"
                );

                registerForm.reset();

            } catch (error) {
                showFeedback(
                    feedback,
                    "We could not save your demonstration profile in this browser. Please check your browser storage settings.",
                    "error"
                );
            }
        });
    }

});
