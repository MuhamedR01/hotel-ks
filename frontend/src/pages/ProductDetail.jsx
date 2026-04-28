import { useParams, Link, useNavigate } from "react-router-dom";
import {
  ChevronLeft,
  ChevronRight,
  ShoppingCart,
  Zap,
  Check,
  Minus,
  Plus,
  ShieldCheck,
  Truck,
  RotateCcw,
} from "lucide-react";
import { useCart } from "../context/CartContext";
import { useState, useEffect } from "react";
import SEO from "../components/SEO";

const ProductDetail = () => {
  const { id } = useParams();
  const { addToCart } = useCart();
  const navigate = useNavigate();

  const [product, setProduct] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [quantity, setQuantity] = useState(1);
  const [selectedImage, setSelectedImage] = useState(0);
  const [selectedSize, setSelectedSize] = useState("");
  const [touchStartX, setTouchStartX] = useState(null);
  const [touchDeltaX, setTouchDeltaX] = useState(0);

  // Fallback sizes list (used only if product.sizes is not provided)
  const fallbackSizes = ["XS", "S", "M", "L", "XL", "XXL"];

  useEffect(() => {
    setLoading(true);
    setError(null);

    const base = import.meta.env.VITE_API_BASE_URL || "/api";

    fetch(`${base}/products/${id}`)
      .then((res) => {
        if (!res.ok) {
          throw new Error(`HTTP error! status: ${res.status}`);
        }
        return res.json();
      })
      .then((data) => {
        if (data.success) {
          // Normalize sizes to a consistent shape: { size: 'M', available: true }
          const prod = { ...data.product };
          if (prod.has_sizes && prod.sizes && Array.isArray(prod.sizes)) {
            const normalized = prod.sizes
              .map((entry) => {
                if (!entry) return null;
                if (typeof entry === "string") {
                  return { size: String(entry).toUpperCase(), available: true };
                }
                // If it's an object, try to extract size and availability
                const s = entry.size || entry.label || entry.name || "";
                const available =
                  typeof entry.available !== "undefined"
                    ? Boolean(entry.available)
                    : true;
                return { size: String(s).toUpperCase(), available };
              })
              .filter(Boolean);

            prod.sizes = normalized;
            // Default to first available size
            const firstAvailable =
              normalized.find((s) => s.available) || normalized[0];
            if (firstAvailable) setSelectedSize(firstAvailable.size);
          }

          setProduct(prod);
        } else {
          throw new Error(data.message || "Failed to fetch product");
        }
        setLoading(false);
      })
      .catch((err) => {
        console.error("Gabim në ngarkimin e produktit:", err);
        setError(err.message);
        setLoading(false);
      });
  }, [id]);

  // Determine boolean availability (no numeric counts)
  const normalizedSelectedSize = selectedSize
    ? String(selectedSize).toUpperCase().trim()
    : "";
  const selectedSizeObj =
    product?.has_sizes && normalizedSelectedSize
      ? (product.sizes || []).find(
          (s) => String(s.size).toUpperCase().trim() === normalizedSelectedSize,
        )
      : null;

  const isAvailable = selectedSizeObj
    ? (selectedSizeObj.available ?? true)
    : (product?.available ?? true);

  const handleAddToCart = () => {
    if (!product) return;

    // Check if product has sizes and none is selected
    if (product.has_sizes && !selectedSize) {
      alert(
        product.variant_label
          ? `Ju lutem zgjidhni ${product.variant_label.toLowerCase()}n`
          : "Ju lutem zgjidhni një madhësi",
      );
      return;
    }

    // Pass the size to addToCart - it will be null if product doesn't have sizes
    const sizeToPass = product.has_sizes
      ? selectedSize
        ? String(selectedSize)
        : null
      : null;

    try {
      addToCart(
        {
          ...product,
          quantity,
          variant_label: product.variant_label || null,
        },
        sizeToPass,
      );

      // Show toast notification (safe insertion)
      const toast = document.createElement("div");
      toast.className =
        "fixed bottom-4 right-4 bg-green-600 text-white px-6 py-3 rounded-lg shadow-lg z-50";
      const safeName = String(product.name || "")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;");
      toast.innerHTML = `\n        <div class="flex items-center">\n          <i class="fas fa-check-circle mr-2"></i>\n          <span>${safeName}${
        sizeToPass ? ` (${String(sizeToPass)})` : ""
      } u shtua në shportë!</span>\n        </div>\n      `;
      document.body.appendChild(toast);
      setTimeout(() => toast.remove(), 3000);
    } catch (err) {
      console.error("Error adding to cart:", err);
      alert(
        "Ndodhi një gabim gjatë shtimit në shportë. Shikoni konsolën për detaje.",
      );
    }
  };

  const handleBuyNow = () => {
    if (!product) return;

    if (product.has_sizes && !selectedSize) {
      alert(
        product.variant_label
          ? `Ju lutem zgjidhni ${product.variant_label.toLowerCase()}n`
          : "Ju lutem zgjidhni një madhësi",
      );
      return;
    }

    const sizeToPass = product.has_sizes
      ? selectedSize
        ? String(selectedSize)
        : null
      : null;
    try {
      addToCart(
        {
          ...product,
          quantity,
          variant_label: product.variant_label || null,
        },
        sizeToPass,
      );
      navigate("/cart");
    } catch (err) {
      console.error("Error during buy now:", err);
      alert("Ndodhi një gabim. Ju lutem provoni përsëri.");
    }
  };

  const nextImage = () => {
    if (product && product.images && product.images.length > 1) {
      setSelectedImage((prev) => (prev + 1) % product.images.length);
    }
  };

  const prevImage = () => {
    if (product && product.images && product.images.length > 1) {
      setSelectedImage(
        (prev) => (prev - 1 + product.images.length) % product.images.length,
      );
    }
  };

  // Touch swipe handlers for mobile image gallery
  const SWIPE_THRESHOLD = 50; // px
  const handleTouchStart = (e) => {
    if (!product?.images || product.images.length <= 1) return;
    setTouchStartX(e.touches[0].clientX);
    setTouchDeltaX(0);
  };
  const handleTouchMove = (e) => {
    if (touchStartX === null) return;
    setTouchDeltaX(e.touches[0].clientX - touchStartX);
  };
  const handleTouchEnd = () => {
    if (touchStartX === null) return;
    if (touchDeltaX > SWIPE_THRESHOLD) {
      prevImage();
    } else if (touchDeltaX < -SWIPE_THRESHOLD) {
      nextImage();
    }
    setTouchStartX(null);
    setTouchDeltaX(0);
  };

  if (loading) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="text-center">
          <div className="animate-spin rounded-full h-16 w-16 border-b-2 border-gray-700 mx-auto mb-4"></div>
          <p className="text-xl text-gray-600">
            Duke ngarkuar detajet e produktit...
          </p>
        </div>
      </div>
    );
  }

  if (error || !product) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="text-center p-8">
          <div className="text-red-600 mb-4">
            <svg
              className="w-16 h-16 mx-auto mb-4"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                strokeLinecap="round"
                strokeLinejoin="round"
                strokeWidth={2}
                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
              />
            </svg>
            <p className="text-lg font-semibold">
              Gabim në ngarkimin e produktit
            </p>
            <p className="text-sm text-gray-600 mt-2">
              {error || "Produkti nuk u gjet."}
            </p>
          </div>
          <div className="flex gap-4 justify-center">
            <Link
              to="/products"
              className="px-6 py-3 bg-gray-800 text-white rounded-lg hover:bg-gray-900 transition-colors font-semibold"
            >
              Kthehu te Produktet
            </Link>
            <button
              onClick={() => window.location.reload()}
              className="px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors font-semibold"
            >
              Provo Përsëri
            </button>
          </div>
        </div>
      </div>
    );
  }

  const currentImage =
    product.images && product.images.length > 0
      ? product.images[selectedImage]
      : product.image || "https://via.placeholder.com/800x600?text=No+Image";

  // Build SEO meta + Product JSON-LD
  const productUrl = `https://minimodaks.com/products/${product.id}`;
  const ogImage =
    Array.isArray(product.images) &&
    product.images[0] &&
    !product.images[0].startsWith("data:")
      ? product.images[0]
      : "https://minimodaks.com/logominimodaks.png";
  const seoDescription = (
    product.description ||
    `${product.name} — bli online te minimodaks me çmim të mirë dhe dorëzim të shpejtët në Kosovë, Shqipëri & Maqedoni.`
  )
    .toString()
    .slice(0, 200);
  const productLd = {
    "@context": "https://schema.org",
    "@type": "Product",
    name: product.name,
    description: seoDescription,
    image: ogImage,
    sku: String(product.id),
    category: product.category || undefined,
    brand: { "@type": "Brand", name: "minimodaks" },
    offers: {
      "@type": "Offer",
      url: productUrl,
      priceCurrency: "EUR",
      price: Number(product.price).toFixed(2),
      availability: isAvailable
        ? "https://schema.org/InStock"
        : "https://schema.org/OutOfStock",
      itemCondition: "https://schema.org/NewCondition",
    },
    aggregateRating:
      product.rating && product.reviews
        ? {
            "@type": "AggregateRating",
            ratingValue: Number(product.rating).toFixed(1),
            reviewCount: Number(product.reviews) || 1,
          }
        : undefined,
  };
  const breadcrumbLd = {
    "@context": "https://schema.org",
    "@type": "BreadcrumbList",
    itemListElement: [
      {
        "@type": "ListItem",
        position: 1,
        name: "Ballina",
        item: "https://minimodaks.com/",
      },
      {
        "@type": "ListItem",
        position: 2,
        name: "Produktet",
        item: "https://minimodaks.com/products",
      },
      {
        "@type": "ListItem",
        position: 3,
        name: product.name,
        item: productUrl,
      },
    ],
  };

  return (
    <div className="min-h-screen bg-gray-50 py-8 sm:py-12">
      <SEO
        title={product.name}
        description={seoDescription}
        canonical={productUrl}
        image={ogImage}
        type="product"
        jsonLd={[productLd, breadcrumbLd]}
      />
      <div className="container mx-auto px-4 sm:px-6 lg:px-8 max-w-7xl">
        {/* Breadcrumb */}
        <nav className="mb-6 text-sm">
          <ol className="flex items-center gap-2 text-gray-500">
            <li>
              <Link to="/" className="hover:text-gray-900 transition-colors">
                Ballina
              </Link>
            </li>
            <ChevronRight className="w-3.5 h-3.5" />
            <li>
              <Link
                to="/products"
                className="hover:text-gray-900 transition-colors"
              >
                Produktet
              </Link>
            </li>
            <ChevronRight className="w-3.5 h-3.5" />
            <li className="text-gray-900 font-medium truncate max-w-[40vw]">
              {product.name}
            </li>
          </ol>
        </nav>

        {/* Product Details */}
        <div className="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 overflow-hidden">
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-10 p-4 sm:p-6 lg:p-10">
            {/* Images */}
            <div className="lg:sticky lg:top-24 lg:self-start">
              <div
                className="mb-4 rounded-2xl overflow-hidden bg-gray-50 relative group aspect-square touch-pan-y select-none"
                onTouchStart={handleTouchStart}
                onTouchMove={handleTouchMove}
                onTouchEnd={handleTouchEnd}
              >
                <img
                  src={currentImage}
                  alt={product.name}
                  draggable="false"
                  onContextMenu={(e) => e.preventDefault()}
                  style={{
                    transform:
                      touchStartX !== null
                        ? `translateX(${touchDeltaX * 0.4}px)`
                        : undefined,
                    transition:
                      touchStartX !== null ? "none" : "transform 300ms",
                  }}
                  className="w-full h-full object-contain p-4 sm:p-6 pointer-events-none"
                  onError={(e) => {
                    e.target.src =
                      "https://via.placeholder.com/800x600?text=No+Image";
                  }}
                />

                {/* Navigation Arrows - Only show if multiple images */}
                {product.images && product.images.length > 1 && (
                  <>
                    <button
                      onClick={prevImage}
                      className="hidden sm:block absolute left-3 top-1/2 -translate-y-1/2 bg-white/95 hover:bg-white text-gray-800 p-2.5 rounded-full shadow-md ring-1 ring-gray-200 opacity-0 group-hover:opacity-100 transition-all duration-300"
                      aria-label="Previous image"
                    >
                      <ChevronLeft className="w-5 h-5" />
                    </button>
                    <button
                      onClick={nextImage}
                      className="hidden sm:block absolute right-3 top-1/2 -translate-y-1/2 bg-white/95 hover:bg-white text-gray-800 p-2.5 rounded-full shadow-md ring-1 ring-gray-200 opacity-0 group-hover:opacity-100 transition-all duration-300"
                      aria-label="Next image"
                    >
                      <ChevronRight className="w-5 h-5" />
                    </button>

                    {/* Image Counter */}
                    <div className="absolute bottom-3 right-3 bg-black/60 backdrop-blur-sm text-white px-3 py-1 rounded-full text-xs font-medium">
                      {selectedImage + 1} / {product.images.length}
                    </div>

                    {/* Dot indicators (mobile-friendly) */}
                    <div className="absolute bottom-3 left-1/2 -translate-x-1/2 flex gap-1.5 sm:hidden">
                      {product.images.map((_, i) => (
                        <span
                          key={i}
                          className={`h-1.5 rounded-full transition-all ${
                            i === selectedImage
                              ? "w-5 bg-white"
                              : "w-1.5 bg-white/50"
                          }`}
                        />
                      ))}
                    </div>
                  </>
                )}
              </div>

              {/* Thumbnail Images */}
              {product.images && product.images.length > 1 && (
                <div className="grid grid-cols-5 gap-2 sm:gap-3">
                  {product.images.map((image, index) => (
                    <button
                      key={index}
                      onClick={() => setSelectedImage(index)}
                      className={`rounded-xl overflow-hidden border-2 transition-all aspect-square ${
                        selectedImage === index
                          ? "border-gray-900 ring-2 ring-gray-900/10"
                          : "border-gray-200 hover:border-gray-400"
                      }`}
                    >
                      <img
                        src={image}
                        alt={`${product.name} ${index + 1}`}
                        className="w-full h-full object-contain bg-gray-50 p-1"
                        onError={(e) => {
                          e.target.src =
                            "https://via.placeholder.com/100x100?text=No+Image";
                        }}
                      />
                    </button>
                  ))}
                </div>
              )}
            </div>

            {/* Product Info */}
            <div className="flex flex-col">
              <div className="mb-3">
                <span className="inline-block px-3 py-1 bg-gray-100 text-gray-700 text-xs font-semibold rounded-full uppercase tracking-wide">
                  {product.category || "Produkt"}
                </span>
              </div>

              <h1 className="text-2xl sm:text-3xl lg:text-4xl font-bold text-gray-900 mb-4 leading-tight">
                {product.name}
              </h1>

              <div className="flex items-baseline flex-wrap gap-3 mb-5 pb-5 border-b border-gray-100">
                {Number(product.sale_percent) > 0 ? (
                  <>
                    <span className="text-3xl sm:text-4xl font-bold text-amber-600 tracking-tight">
                      €
                      {parseFloat(product.sale_price ?? product.price).toFixed(
                        2,
                      )}
                    </span>
                    <span className="text-xl text-gray-400 line-through">
                      €{parseFloat(product.price).toFixed(2)}
                    </span>
                    <span className="inline-flex items-center px-2 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                      -{Math.round(Number(product.sale_percent))}%
                    </span>
                  </>
                ) : (
                  <span className="text-3xl sm:text-4xl font-bold text-gray-900 tracking-tight">
                    €{parseFloat(product.price).toFixed(2)}
                  </span>
                )}
                {isAvailable ? (
                  <span className="inline-flex items-center gap-1.5 text-sm font-medium text-green-700 bg-green-50 px-2.5 py-1 rounded-full">
                    <Check className="w-3.5 h-3.5" />
                    Në stok
                  </span>
                ) : (
                  <span className="inline-flex items-center gap-1.5 text-sm font-medium text-red-700 bg-red-50 px-2.5 py-1 rounded-full">
                    Nuk ka në magazinë
                  </span>
                )}
              </div>

              {product.description && (
                <p className="text-gray-600 text-base mb-6 leading-relaxed whitespace-pre-line">
                  {product.description}
                </p>
              )}

              {/* Size Selection - only show when enabled for this product */}
              {product.has_sizes && (
                <div className="mb-6">
                  <div className="flex items-center justify-between mb-3">
                    <label className="block text-sm font-semibold text-gray-900">
                      Zgjidhni{" "}
                      {product.variant_label
                        ? product.variant_label
                        : "Madhësinë"}{" "}
                      <span className="text-red-500">*</span>
                    </label>
                    {selectedSize && (
                      <span className="text-sm text-gray-500">
                        E zgjedhur:{" "}
                        <span className="font-semibold text-gray-900">
                          {selectedSize}
                        </span>
                      </span>
                    )}
                  </div>
                  <div className="flex flex-wrap gap-2">
                    {(product.sizes && product.sizes.length > 0
                      ? product.sizes
                      : fallbackSizes
                    ).map((s) => {
                      const raw =
                        typeof s === "string" ? s : s.size || s.label || s.name;
                      const sizeValue = raw
                        ? String(raw).toUpperCase().trim()
                        : "";
                      const isSelected =
                        (normalizedSelectedSize || "").toUpperCase() ===
                        sizeValue;

                      return (
                        <button
                          key={sizeValue}
                          onClick={() => setSelectedSize(sizeValue)}
                          className={`min-w-[56px] h-12 px-4 border-2 rounded-xl font-semibold text-sm transition-all ${
                            isSelected
                              ? "bg-gray-900 text-white border-gray-900 shadow-sm"
                              : "bg-white text-gray-700 border-gray-200 hover:border-gray-900 hover:bg-gray-50"
                          }`}
                        >
                          {sizeValue}
                        </button>
                      );
                    })}
                  </div>
                  {!selectedSize && (
                    <p className="text-xs text-red-600 mt-2">
                      Ju lutem zgjidhni një madhësi
                    </p>
                  )}
                </div>
              )}

              {/* Quantity Selector */}
              <div className="mb-6">
                <label className="block text-sm font-semibold text-gray-900 mb-3">
                  Sasia
                </label>
                <div className="inline-flex items-center bg-gray-50 rounded-xl border border-gray-200 overflow-hidden">
                  <button
                    onClick={() => setQuantity(Math.max(1, quantity - 1))}
                    className="w-11 h-11 flex items-center justify-center text-gray-700 hover:bg-gray-100 transition-colors disabled:opacity-40"
                    disabled={quantity <= 1}
                    aria-label="Zvogëlo sasinë"
                  >
                    <Minus className="w-4 h-4" />
                  </button>
                  <span className="w-12 text-center text-base font-semibold text-gray-900">
                    {quantity}
                  </span>
                  <button
                    onClick={() => setQuantity(quantity + 1)}
                    className="w-11 h-11 flex items-center justify-center text-gray-700 hover:bg-gray-100 transition-colors disabled:opacity-40"
                    disabled={!isAvailable}
                    aria-label="Rrit sasinë"
                  >
                    <Plus className="w-4 h-4" />
                  </button>
                </div>
              </div>

              {/* Action Buttons */}
              <div className="flex flex-col sm:flex-row gap-3 mb-6">
                <button
                  onClick={handleAddToCart}
                  disabled={!isAvailable}
                  className="flex-1 inline-flex items-center justify-center gap-2 bg-gray-900 hover:bg-black text-white font-semibold py-3.5 px-6 rounded-xl transition-all shadow-sm hover:shadow-md disabled:bg-gray-300 disabled:cursor-not-allowed disabled:shadow-none"
                >
                  <ShoppingCart className="w-5 h-5" />
                  Shto në Shportë
                </button>
                <button
                  onClick={handleBuyNow}
                  disabled={!isAvailable}
                  className="flex-1 inline-flex items-center justify-center gap-2 bg-white hover:bg-gray-50 text-gray-900 border-2 border-gray-900 font-semibold py-3.5 px-6 rounded-xl transition-all disabled:bg-gray-100 disabled:text-gray-400 disabled:border-gray-200 disabled:cursor-not-allowed"
                >
                  <Zap className="w-5 h-5" />
                  Bli Tani
                </button>
              </div>

              {/* Perks */}
              <div className="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-6 border-t border-gray-100">
                <div className="flex items-center gap-3 text-sm text-gray-600">
                  <div className="w-9 h-9 rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0">
                    <Truck className="w-4 h-4 text-gray-700" />
                  </div>
                  <span>Dorëzim i shpejtë</span>
                </div>
                <div className="flex items-center gap-3 text-sm text-gray-600">
                  <div className="w-9 h-9 rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0">
                    <RotateCcw className="w-4 h-4 text-gray-700" />
                  </div>
                  <span>Kthim i lehtë</span>
                </div>
                <div className="flex items-center gap-3 text-sm text-gray-600">
                  <div className="w-9 h-9 rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0">
                    <ShieldCheck className="w-4 h-4 text-gray-700" />
                  </div>
                  <span>Blerje e sigurt</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default ProductDetail;
