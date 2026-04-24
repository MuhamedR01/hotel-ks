import { StrictMode } from "react";
import { createRoot } from "react-dom/client";
import "./index.css";
import App from "./App.jsx";

// Discourage saving images: block right-click and drag on <img> elements.
// This is a UX deterrent, not true protection.
if (typeof window !== "undefined") {
  document.addEventListener("contextmenu", (e) => {
    if (e.target && e.target.tagName === "IMG") {
      e.preventDefault();
    }
  });
  document.addEventListener("dragstart", (e) => {
    if (e.target && e.target.tagName === "IMG") {
      e.preventDefault();
    }
  });
}

createRoot(document.getElementById("root")).render(
  <StrictMode>
    <App />
  </StrictMode>,
);
