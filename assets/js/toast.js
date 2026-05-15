/**
 * Toast — tiny vanilla-JS notification utility.
 *
 * Usage:
 *   Toast.success("Product added");
 *   Toast.error("Something went wrong");
 *   Toast.warning("Stock is low");
 *   Toast.info("Heads up");
 *   Toast.show("Custom", { type: "success", duration: 3000 });
 *
 * No dependencies. Injects its own CSS once on load.
 */
(function () {
  if (window.Toast) return;

  const STYLE_ID = "toast-style";
  const CONTAINER_ID = "toast-container";

  const ICONS = {
    success: '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>',
    error:   '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>',
    warning: '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
    info:    '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>',
  };

  function injectStyle() {
    if (document.getElementById(STYLE_ID)) return;
    const css = `
      #${CONTAINER_ID} {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 2147483647;
        display: flex;
        flex-direction: column;
        gap: 12px;
        max-width: calc(100vw - 40px);
        pointer-events: none;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
      }
      .toast {
        --toast-color: #2c5364;
        position: relative;
        min-width: 280px;
        max-width: 380px;
        padding: 14px 44px 14px 50px;
        background: #ffffff;
        color: #1f2937;
        border-radius: 12px;
        box-shadow: 0 12px 32px rgba(15, 32, 39, 0.18), 0 2px 6px rgba(15, 32, 39, 0.08);
        font-size: 0.92rem;
        line-height: 1.4;
        overflow: hidden;
        pointer-events: auto;
        opacity: 0;
        transform: translateX(120%);
        transition: transform 0.32s cubic-bezier(0.22, 1, 0.36, 1), opacity 0.32s ease;
        border-left: 4px solid var(--toast-color);
      }
      .toast.toast--visible {
        opacity: 1;
        transform: translateX(0);
      }
      .toast.toast--leaving {
        opacity: 0;
        transform: translateX(120%);
      }
      .toast__icon {
        position: absolute;
        top: 14px;
        left: 16px;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--toast-color);
      }
      .toast__close {
        position: absolute;
        top: 8px;
        right: 8px;
        width: 28px;
        height: 28px;
        border: none;
        background: transparent;
        color: #9ca3af;
        cursor: pointer;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        line-height: 1;
        transition: background 0.15s, color 0.15s;
      }
      .toast__close:hover {
        background: rgba(0, 0, 0, 0.05);
        color: #374151;
      }
      .toast__body { word-wrap: break-word; }
      .toast__progress {
        position: absolute;
        left: 0;
        bottom: 0;
        height: 3px;
        width: 100%;
        background: var(--toast-color);
        opacity: 0.7;
        transform-origin: left;
        animation: toastProgress linear forwards;
      }
      @keyframes toastProgress {
        from { transform: scaleX(1); }
        to   { transform: scaleX(0); }
      }
      .toast--success { --toast-color: #16a34a; }
      .toast--error   { --toast-color: #dc2626; }
      .toast--warning { --toast-color: #d97706; }
      .toast--info    { --toast-color: #2563eb; }

      @media (max-width: 480px) {
        #${CONTAINER_ID} {
          top: 12px;
          right: 12px;
          left: 12px;
          max-width: none;
        }
        .toast { min-width: 0; max-width: none; width: 100%; }
      }
    `;
    const style = document.createElement("style");
    style.id = STYLE_ID;
    style.textContent = css;
    document.head.appendChild(style);
  }

  function getContainer() {
    let el = document.getElementById(CONTAINER_ID);
    if (!el) {
      el = document.createElement("div");
      el.id = CONTAINER_ID;
      document.body.appendChild(el);
    }
    return el;
  }

  function show(message, options = {}) {
    if (!document.body) {
      // Defer until DOM is ready
      document.addEventListener("DOMContentLoaded", () => show(message, options), { once: true });
      return;
    }
    injectStyle();

    const type = ["success", "error", "warning", "info"].includes(options.type) ? options.type : "info";
    const duration = typeof options.duration === "number" ? options.duration : 4000;

    const toast = document.createElement("div");
    toast.className = `toast toast--${type}`;
    toast.setAttribute("role", type === "error" ? "alert" : "status");
    toast.innerHTML = `
      <span class="toast__icon">${ICONS[type]}</span>
      <div class="toast__body"></div>
      <button class="toast__close" aria-label="Close">&times;</button>
      ${duration > 0 ? `<span class="toast__progress" style="animation-duration:${duration}ms"></span>` : ""}
    `;
    toast.querySelector(".toast__body").textContent = String(message ?? "");

    const container = getContainer();
    container.appendChild(toast);

    // Trigger enter animation
    requestAnimationFrame(() => toast.classList.add("toast--visible"));

    const remove = () => {
      if (toast.classList.contains("toast--leaving")) return;
      toast.classList.add("toast--leaving");
      toast.addEventListener("transitionend", () => toast.remove(), { once: true });
    };

    toast.querySelector(".toast__close").addEventListener("click", remove);

    let timer = null;
    if (duration > 0) {
      timer = setTimeout(remove, duration);
      // Pause on hover
      toast.addEventListener("mouseenter", () => {
        clearTimeout(timer);
        const bar = toast.querySelector(".toast__progress");
        if (bar) bar.style.animationPlayState = "paused";
      });
      toast.addEventListener("mouseleave", () => {
        timer = setTimeout(remove, 1500);
        const bar = toast.querySelector(".toast__progress");
        if (bar) bar.style.animationPlayState = "running";
      });
    }

    return { close: remove };
  }

  window.Toast = {
    show,
    success: (msg, opts = {}) => show(msg, { ...opts, type: "success" }),
    error:   (msg, opts = {}) => show(msg, { ...opts, type: "error" }),
    warning: (msg, opts = {}) => show(msg, { ...opts, type: "warning" }),
    info:    (msg, opts = {}) => show(msg, { ...opts, type: "info" }),
  };
})();
