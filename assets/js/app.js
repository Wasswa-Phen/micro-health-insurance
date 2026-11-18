/* =====================================================================
   AfyaMicro Kampala — Vanilla JS UI Controller
   Zero external dependencies; progressively enhances PHP/HTML forms.
   ===================================================================== */

(function () {
  "use strict";

  const formatUGX = (value) => {
    const num = Math.max(0, Math.round(Number(value) || 0));
    return "UGX " + num.toLocaleString("en-UG");
  };

  // -------------------------------------------------------------------
  // 1. Public Header Mobile Menu
  // -------------------------------------------------------------------
  const menuBtn = document.querySelector("[data-menu-toggle]");
  const publicNav = document.getElementById("public-nav");
  if (menuBtn && publicNav) {
    menuBtn.addEventListener("click", () => {
      const open = publicNav.classList.toggle("nav-open");
      menuBtn.setAttribute("aria-expanded", String(open));
    });
    publicNav.querySelectorAll("a").forEach((link) => {
      link.addEventListener("click", () => {
        publicNav.classList.remove("nav-open");
        menuBtn.setAttribute("aria-expanded", "false");
      });
    });
  }

  // -------------------------------------------------------------------
  // 2. Portal Sidebar Mobile Drawer
  // -------------------------------------------------------------------
  const sidebar = document.getElementById("portal-sidebar");
  const scrim = document.getElementById("nav-scrim");
  const openSidebarBtn = document.querySelector("[data-sidebar-open]");
  const closeSidebarBtn = document.querySelector("[data-sidebar-close]");

  const setSidebarOpen = (open) => {
    if (!sidebar || !scrim) return;
    sidebar.classList.toggle("sidebar-open", open);
    scrim.classList.toggle("is-open", open);
  };

  if (openSidebarBtn && sidebar && scrim) {
    openSidebarBtn.addEventListener("click", () => setSidebarOpen(true));
    if (closeSidebarBtn) {
      closeSidebarBtn.addEventListener("click", () => setSidebarOpen(false));
    }
    scrim.addEventListener("click", () => setSidebarOpen(false));
    sidebar.querySelectorAll("nav a").forEach((link) => {
      link.addEventListener("click", () => setSidebarOpen(false));
    });
    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape") setSidebarOpen(false);
    });
  }

  // -------------------------------------------------------------------
  // 3. Dismissible Alert Banners
  // -------------------------------------------------------------------
  document.querySelectorAll("[data-alert-dismiss]").forEach((btn) => {
    btn.addEventListener("click", () => {
      const alertBox = btn.closest(".alert");
      if (alertBox) alertBox.remove();
    });
  });

  // -------------------------------------------------------------------
  // 4. Password Visibility Toggle
  // -------------------------------------------------------------------
  document.querySelectorAll("[data-toggle-password]").forEach((btn) => {
    btn.addEventListener("click", () => {
      const wrap = btn.closest(".input-wrap");
      const input = wrap ? wrap.querySelector("input") : null;
      if (!input) return;
      const showing = input.type === "text";
      input.type = showing ? "password" : "text";
      btn.setAttribute("aria-pressed", String(!showing));
      btn.setAttribute("aria-label", showing ? "Show password" : "Hide password");
    });
  });

  // -------------------------------------------------------------------
  // 5. Tabbed Activity / Report Panels
  // -------------------------------------------------------------------
  document.querySelectorAll("[data-tab-group]").forEach((group) => {
    const groupName = group.getAttribute("data-tab-group");
    const buttons = group.querySelectorAll("[data-tab-target]");
    const panels = document.querySelectorAll(`[data-tab-panel-group="${groupName}"]`);

    buttons.forEach((btn) => {
      btn.addEventListener("click", () => {
        const targetId = btn.getAttribute("data-tab-target");
        buttons.forEach((b) => b.classList.toggle("active", b === btn));
        panels.forEach((p) => p.classList.toggle("is-hidden", p.id !== targetId));
      });
    });
  });

  // -------------------------------------------------------------------
  // 6. Client Deposit Preset UGX Pills & Payment Radios
  // -------------------------------------------------------------------
  const depositAmount = document.getElementById("deposit-amount");
  const presetPills = document.querySelectorAll("[data-deposit-amount]");

  if (depositAmount && presetPills.length) {
    const syncPresetPills = () => {
      const current = String(Math.round(Number(depositAmount.value) || 0));
      presetPills.forEach((pill) => {
        pill.classList.toggle("active", pill.getAttribute("data-deposit-amount") === current);
      });
    };

    presetPills.forEach((pill) => {
      pill.addEventListener("click", () => {
        depositAmount.value = pill.getAttribute("data-deposit-amount") || "";
        syncPresetPills();
        depositAmount.focus();
      });
    });

    depositAmount.addEventListener("input", syncPresetPills);
    syncPresetPills();
  }

  const paymentRadios = document.querySelectorAll('.payment-option input[type="radio"]');
  if (paymentRadios.length) {
    const syncMethodCards = () => {
      paymentRadios.forEach((radio) => {
        const card = radio.closest(".payment-option");
        if (card) card.classList.toggle("active", radio.checked);
      });
    };
    paymentRadios.forEach((radio) => radio.addEventListener("change", syncMethodCards));
    syncMethodCards();
  }

  // -------------------------------------------------------------------
  // 7. Live Attendant Co-Pay Calculator
  // -------------------------------------------------------------------
  const claimForm = document.getElementById("claim-form");
  const costInput = document.getElementById("claim-total-cost");
  const coveredOut = document.getElementById("preview-covered");
  const copayOut = document.getElementById("preview-copay");
  const copayNote = document.getElementById("preview-copay-note");
  const copayBox = document.getElementById("preview-copay-box");
  const claimNoteText = document.getElementById("claim-note-text");

  if (claimForm && costInput && coveredOut && copayOut) {
    const balance = Math.max(0, Number(claimForm.getAttribute("data-balance")) || 0);

    const updateCopayPreview = () => {
      const cost = Math.max(0, Number(costInput.value) || 0);
      const covered = Math.min(balance, cost);
      const copay = Math.max(0, cost - covered);

      coveredOut.textContent = formatUGX(covered);
      copayOut.textContent = formatUGX(copay);

      if (copayBox) {
        copayBox.classList.toggle("due", copay > 0);
      }
      if (copayNote) {
        if (cost <= 0) {
          copayNote.textContent = "Enter prescription cost";
        } else if (copay > 0) {
          copayNote.textContent = "Collect cash from client";
        } else {
          copayNote.textContent = "Nothing to collect";
        }
      }
      if (claimNoteText) {
        if (balance <= 0) {
          claimNoteText.textContent =
            "Client wallet balance is UGX 0. Submitting will reject with insufficient_balance (client pays full bill in cash).";
        } else if (cost > 0 && copay > 0) {
          claimNoteText.textContent = `Partial cover: deducts ${formatUGX(covered)} from wallet; collect ${formatUGX(copay)} cash co-pay.`;
        } else {
          claimNoteText.textContent = `Claim will deduct ${formatUGX(covered)} from the client's health wallet.`;
        }
      }
    };

    costInput.addEventListener("input", updateCopayPreview);
    updateCopayPreview();
  }

  // -------------------------------------------------------------------
  // 8. Admin Collapsible Panels (e.g. Add New Pharmacy)
  // -------------------------------------------------------------------
  document.querySelectorAll("[data-toggle-target]").forEach((btn) => {
    btn.addEventListener("click", () => {
      const targetId = btn.getAttribute("data-toggle-target");
      const target = targetId ? document.getElementById(targetId) : null;
      if (!target) return;
      const hidden = target.classList.toggle("is-hidden");
      const openLabel = btn.getAttribute("data-label-open") || "Add new pharmacy";
      const closeLabel = btn.getAttribute("data-label-close") || "Cancel";
      const labelSpan = btn.querySelector("[data-toggle-label]");
      if (labelSpan) {
        labelSpan.textContent = hidden ? openLabel : closeLabel;
      }
      btn.setAttribute("aria-expanded", String(!hidden));
    });
  });

  // -------------------------------------------------------------------
  // 9. Print Triggers (Receipt & Admin Report)
  // -------------------------------------------------------------------
  document.querySelectorAll("[data-print]").forEach((btn) => {
    btn.addEventListener("click", () => window.print());
  });
})();

