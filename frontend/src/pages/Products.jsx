import { useState, useEffect, useMemo } from "react";
import { Link } from "react-router-dom";
import { useCart } from "../context/CartContext";
import SEO from "../components/SEO";

function Products() {
  const { addToCart } = useCart();
  const [products, setProducts] = useState([]);
  const [categories, setCategories] = useState([]);
  const [selectedCategory, setSelectedCategory] = useState("");
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [searchQuery, setSearchQuery] = useState("");
  const [sortBy, setSortBy] = useState("default");
  const [quantities, setQuantities] = useState({});

  const getQty = (id) => quantities[id] ?? 1;
  const setQty = (id, value) => {
    const v = Math.max(1, Math.min(99, Number(value) || 1));
    setQuantities((prev) => ({ ...prev, [id]: v }));
  };

  useEffect(() => {
    const base = import.meta.env.VITE_API_BASE_URL || "/api";

    const fetchProducts = async () => {
      try {
        const res = await fetch(`${base}/products`);
        const data = await res.json();
        if (!res.ok)
          throw new Error(
            data.message || data.error || "Failed to fetch products",
          );
        const productsData = data.success ? data.products : data;
        setProducts(productsData);
      } catch (err) {
        console.error("Gabim në ngarkimin e produkteve:", err);
        setError(err.message);
      } finally {
        setLoading(false);
      }
    };

    const fetchCategories = async () => {
      try {
        const res = await fetch(`${base}/products/categories`);
        const data = await res.json();
        if (data.success && Array.isArray(data.categories)) {
          setCategories(data.categories);
        }
      } catch {
        // categories are optional — fail silently
      }
    };

    fetchProducts();
    fetchCategories();
  }, []);

  const handleAddToCart = (product) => {
    // Check if product has sizes
    if (product.has_sizes && product.sizes && product.sizes.length > 0) {
      // Show alert to select size on product detail page
      const toast = document.createElement("div");
      toast.className =
        "fixed bottom-4 right-4 bg-yellow-600 text-white px-6 py-3 rounded-lg shadow-lg z-50";
      toast.textContent = "Ju lutem zgjidhni madhësinë në faqen e produktit";
      document.body.appendChild(toast);
      setTimeout(() => toast.remove(), 3000);
      return;
    }

    const qty = getQty(product.id);
    addToCart({ ...product, quantity: qty });
    // Show toast notification
    const toast = document.createElement("div");
    toast.className =
      "fixed bottom-4 right-4 bg-green-600 text-white px-6 py-3 rounded-lg shadow-lg z-50";
    toast.textContent = `${product.name} × ${qty} u shtua në shportë!`;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
  };

  const sortedProducts = useMemo(() => {
    let filtered = products.filter((product) => {
      const matchesSearch = product.name
        .toLowerCase()
        .includes(searchQuery.toLowerCase());
      const matchesCategory =
        selectedCategory === "" ||
        (product.category ?? "") === selectedCategory;
      return matchesSearch && matchesCategory;
    });

    switch (sortBy) {
      case "price-low":
        return filtered.sort((a, b) => a.price - b.price);
      case "price-high":
        return filtered.sort((a, b) => b.price - a.price);
      case "name":
        return filtered.sort((a, b) => a.name.localeCompare(b.name));
      default:
        return filtered;
    }
  }, [products, searchQuery, sortBy, selectedCategory]);

  if (loading) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="text-center w-full">
          <div className="animate-spin rounded-full h-16 w-16 border-b-4 border-gray-700 mb-4 mx-auto"></div>
          <p className="text-xl text-gray-600">Duke ngarkuar produktet...</p>
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="text-center">
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
              Gabim në ngarkimin e produkteve
            </p>
            <p className="text-sm text-gray-600 mt-2">{error}</p>
          </div>
          <button
            onClick={() => window.location.reload()}
            className="px-6 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-900 transition-colors"
          >
            Provo Përsëri
          </button>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-50 py-12">
      <SEO
        title="Veshje për fëmijë — minimodaks"
        description="Bli online veshje cilësore për fëmijë me çmimet më të mira në treg. Dorëzim në Kosovë 1–3 ditë pune, Shqipëri & Maqedoni 2–5 ditë pune."
        canonical="https://minimodaks.com/products"
        jsonLd={{
          "@context": "https://schema.org",
          "@type": "CollectionPage",
          name: "Produktet — minimodaks",
          url: "https://minimodaks.com/products",
          inLanguage: "sq",
          isPartOf: {
            "@type": "WebSite",
            name: "minimodaks",
            url: "https://minimodaks.com",
          },
        }}
      />
      <div className="container mx-auto px-4 sm:px-6 lg:px-8">
        {/* Header */}
        <div className="mb-8">
          <h1 className="text-4xl font-bold text-gray-900 mb-4">
            <a href="https://minimodaks.com">MINIMODAKS</a>
          </h1>
          <p className="text-xl text-gray-600">
            CILESI E GARANTUAR QMIMET ME TE MIRA NE TREG.
          </p>
        </div>

        {/* Search and Filters */}
        <div className="bg-white rounded-lg shadow-md p-6 mb-6">
          {/* Category pills */}
          {categories.length > 0 && (
            <div className="mb-5">
              <div className="flex gap-2 overflow-x-auto pb-1 scrollbar-none">
                <button
                  onClick={() => setSelectedCategory("")}
                  className={`flex-shrink-0 px-4 py-1.5 rounded-full text-sm font-medium transition-all duration-150 ${
                    selectedCategory === ""
                      ? "bg-gray-800 text-white shadow-sm"
                      : "bg-white border border-gray-300 text-gray-700 hover:border-gray-500"
                  }`}
                >
                  Të gjitha
                </button>
                {categories.map((cat) => (
                  <button
                    key={cat}
                    onClick={() =>
                      setSelectedCategory(cat === selectedCategory ? "" : cat)
                    }
                    className={`flex-shrink-0 px-4 py-1.5 rounded-full text-sm font-medium transition-all duration-150 ${
                      selectedCategory === cat
                        ? "bg-gray-800 text-white shadow-sm"
                        : "bg-white border border-gray-300 text-gray-700 hover:border-gray-500"
                    }`}
                  >
                    {cat}
                  </button>
                ))}
              </div>
            </div>
          )}

          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            {/* Search */}
            <div>
              <label className="block text-sm font-semibold text-gray-700 mb-2">
                Kërko
              </label>
              <input
                type="text"
                placeholder="Kërko produkte..."
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-transparent"
              />
            </div>

            {/* Sort */}
            <div>
              <label className="block text-sm font-semibold text-gray-700 mb-2">
                Rendit sipas
              </label>
              <select
                value={sortBy}
                onChange={(e) => setSortBy(e.target.value)}
                className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-transparent"
              >
                <option value="default">Të parazgjedhura</option>
                <option value="price-low">Çmimi: nga i ulët në të lartë</option>
                <option value="price-high">
                  Çmimi: nga i lartë në të ulët
                </option>
                <option value="name">Emri A-Z</option>
              </select>
            </div>
          </div>
        </div>

        {/* Results Count */}
        <div className="mb-6">
          <p className="text-gray-600">
            Duke shfaqur{" "}
            <span className="font-semibold">{sortedProducts.length}</span>{" "}
            produkte
          </p>
        </div>

        {/* Products Grid - 2 columns on mobile, 4 on desktop */}
        {sortedProducts.length > 0 ? (
          <div className="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-6">
            {sortedProducts.map((product) => (
              <Link
                key={product.id}
                to={`/products/${product.id}`}
                className="group bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-all transform hover:-translate-y-1"
              >
                <div className="relative h-48 sm:h-64 overflow-hidden bg-gray-100">
                  <img
                    src={product.image}
                    alt={product.name}
                    loading="lazy"
                    decoding="async"
                    draggable="false"
                    onContextMenu={(e) => e.preventDefault()}
                    className="w-full h-full object-contain p-2 select-none pointer-events-none group-hover:scale-105 transition-transform duration-300"
                    onError={(e) => {
                      const svg =
                        "%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22400%22 height=%22300%22%3E%3Crect fill=%22%23ddd%22 width=%22400%22 height=%22300%22/%3E%3Ctext fill=%22%23999%22 x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22%3ENo Image%3C/text%3E%3C/svg%3E";
                      e.target.src = "data:image/svg+xml," + svg;
                    }}
                  />
                  {!(product.available ?? true) && (
                    <div className="absolute top-2 left-2 bg-red-500 text-white px-2 py-1 rounded-full text-xs font-semibold">
                      Mbaruar
                    </div>
                  )}
                  {Number(product.sale_percent) > 0 && (
                    <div className="absolute top-2 right-2 bg-amber-500 text-white px-2 py-1 rounded-full text-xs font-bold shadow">
                      -{Math.round(Number(product.sale_percent))}%
                    </div>
                  )}
                </div>
                <div className="p-3 sm:p-5">
                  <h3 className="text-sm sm:text-lg font-semibold text-gray-900 mb-2 group-hover:text-gray-800 transition-colors line-clamp-2">
                    {product.name}
                  </h3>
                  <p className="text-gray-600 text-xs sm:text-sm mb-3 sm:mb-4 line-clamp-2 hidden sm:block">
                    {product.description}
                  </p>
                  <div className="flex items-center justify-between mb-2 sm:mb-3">
                    {Number(product.sale_percent) > 0 ? (
                      <div className="flex flex-col">
                        <span className="text-xs sm:text-sm line-through text-gray-400">
                          €{Number(product.price).toFixed(2)}
                        </span>
                        <span className="text-lg sm:text-2xl font-bold text-amber-600">
                          €
                          {Number(product.sale_price ?? product.price).toFixed(
                            2,
                          )}
                        </span>
                      </div>
                    ) : (
                      <span className="text-lg sm:text-2xl font-bold text-gray-800">
                        €{Number(product.price).toFixed(2)}
                      </span>
                    )}
                    {(product.available ?? true) && (
                      <span className="text-xs sm:text-sm text-green-600 font-medium">
                        Në stok
                      </span>
                    )}
                  </div>

                  {/* Quantity stepper (only if product is available and has no sizes) */}
                  {(product.available ?? true) &&
                    !(
                      product.has_sizes &&
                      product.sizes &&
                      product.sizes.length > 0
                    ) && (
                      <div
                        className="flex items-center justify-center mb-2 sm:mb-3"
                        onClick={(e) => e.preventDefault()}
                      >
                        <div className="inline-flex items-center bg-gray-50 border border-gray-200 rounded-lg overflow-hidden">
                          <button
                            type="button"
                            onClick={(e) => {
                              e.preventDefault();
                              e.stopPropagation();
                              setQty(product.id, getQty(product.id) - 1);
                            }}
                            disabled={getQty(product.id) <= 1}
                            className="w-8 h-8 sm:w-9 sm:h-9 flex items-center justify-center text-gray-700 hover:bg-gray-100 transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
                            aria-label="Zvogëlo"
                          >
                            −
                          </button>
                          <input
                            type="number"
                            min="1"
                            max="99"
                            value={getQty(product.id)}
                            onClick={(e) => {
                              e.preventDefault();
                              e.stopPropagation();
                            }}
                            onChange={(e) => {
                              e.stopPropagation();
                              setQty(product.id, e.target.value);
                            }}
                            className="w-10 sm:w-12 h-8 sm:h-9 text-center text-sm font-semibold bg-transparent border-0 focus:outline-none focus:ring-0 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                          />
                          <button
                            type="button"
                            onClick={(e) => {
                              e.preventDefault();
                              e.stopPropagation();
                              setQty(product.id, getQty(product.id) + 1);
                            }}
                            className="w-8 h-8 sm:w-9 sm:h-9 flex items-center justify-center text-gray-700 hover:bg-gray-100 transition-colors"
                            aria-label="Rrit"
                          >
                            +
                          </button>
                        </div>
                      </div>
                    )}

                  <button
                    onClick={(e) => {
                      e.preventDefault();
                      if (product.available ?? true) {
                        handleAddToCart(product);
                      }
                    }}
                    disabled={!(product.available ?? true)}
                    className={`w-full py-2 rounded-lg transition-colors font-semibold text-sm ${
                      (product.available ?? true)
                        ? "bg-gray-800 hover:bg-gray-900 text-white"
                        : "bg-gray-300 text-gray-500 cursor-not-allowed"
                    }`}
                  >
                    {(product.available ?? true)
                      ? "Shto në Shportë"
                      : "Mbaruar"}
                  </button>
                </div>
              </Link>
            ))}
          </div>
        ) : (
          <div className="text-center py-16">
            <svg
              className="w-24 h-24 text-gray-400 mx-auto mb-4"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                strokeLinecap="round"
                strokeLinejoin="round"
                strokeWidth={2}
                d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
              />
            </svg>
            <h3 className="text-2xl font-semibold text-gray-900 mb-2">
              Nuk u gjet asnjë produkt
            </h3>
            <p className="text-gray-600 mb-6">Provo të rregullosh kërkimin</p>
            <button
              onClick={() => {
                setSearchQuery("");
                setSortBy("default");
                setSelectedCategory("");
              }}
              className="px-6 py-3 bg-gray-800 text-white rounded-lg hover:bg-gray-900 transition-colors font-semibold"
            >
              Pastroni Filtrat
            </button>
          </div>
        )}
      </div>
    </div>
  );
}

export default Products;
