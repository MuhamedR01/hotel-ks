import { useEffect } from "react";

/**
 * Lightweight SEO component — sets document.title, meta description,
 * canonical URL, Open Graph tags, and optional JSON-LD without any
 * external dependency.
 *
 * Props:
 *   - title:       string (required)
 *   - description: string
 *   - canonical:   absolute URL (defaults to current location)
 *   - image:       absolute URL for og:image
 *   - type:        og:type (default "website")
 *   - noindex:     boolean — set robots to noindex,nofollow
 *   - jsonLd:      object | object[] — JSON-LD structured data
 */
const SITE_NAME = "minimodaks";
const DEFAULT_IMAGE = "https://minimodaks.com/logominimodaks.png";

const setMeta = (selector, attr, value) => {
  if (!value) return null;
  let el = document.head.querySelector(selector);
  if (!el) {
    el = document.createElement("meta");
    const [, key, name] = selector.match(/^meta\[(.+?)="(.+?)"\]$/) || [];
    if (key && name) el.setAttribute(key, name);
    document.head.appendChild(el);
  }
  el.setAttribute(attr, value);
  return el;
};

const setLink = (rel, href) => {
  if (!href) return null;
  let el = document.head.querySelector(`link[rel="${rel}"]`);
  if (!el) {
    el = document.createElement("link");
    el.setAttribute("rel", rel);
    document.head.appendChild(el);
  }
  el.setAttribute("href", href);
  return el;
};

const SEO = ({
  title,
  description,
  canonical,
  image = DEFAULT_IMAGE,
  type = "website",
  noindex = false,
  jsonLd = null,
}) => {
  useEffect(() => {
    const fullTitle = title
      ? title.includes(SITE_NAME)
        ? title
        : `${title} | ${SITE_NAME}`
      : SITE_NAME;
    document.title = fullTitle;

    const url =
      canonical ||
      (typeof window !== "undefined" ? window.location.href : undefined);

    setMeta('meta[name="description"]', "content", description);
    setMeta(
      'meta[name="robots"]',
      "content",
      noindex ? "noindex, nofollow" : "index, follow, max-image-preview:large",
    );
    setLink("canonical", url);

    // Open Graph
    setMeta('meta[property="og:title"]', "content", fullTitle);
    setMeta('meta[property="og:description"]', "content", description);
    setMeta('meta[property="og:url"]', "content", url);
    setMeta('meta[property="og:image"]', "content", image);
    setMeta('meta[property="og:type"]', "content", type);

    // Twitter
    setMeta('meta[name="twitter:title"]', "content", fullTitle);
    setMeta('meta[name="twitter:description"]', "content", description);
    setMeta('meta[name="twitter:image"]', "content", image);

    // JSON-LD (route-scoped)
    const ldNodes = [];
    if (jsonLd) {
      const arr = Array.isArray(jsonLd) ? jsonLd : [jsonLd];
      for (const data of arr) {
        const script = document.createElement("script");
        script.type = "application/ld+json";
        script.dataset.seo = "route";
        script.textContent = JSON.stringify(data);
        document.head.appendChild(script);
        ldNodes.push(script);
      }
    }

    return () => {
      ldNodes.forEach((n) => n.remove());
    };
  }, [title, description, canonical, image, type, noindex, jsonLd]);

  return null;
};

export default SEO;
