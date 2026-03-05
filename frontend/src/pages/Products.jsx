import { useState, useEffect, useMemo } from "react";
import { Link } from "react-router-dom";
import { useCart } from "../context/CartContext";

function Products() {
  const { addToCart } = useCart();
  const [products, setProducts] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [searchQuery, setSearchQuery] = useState("");
  const [sortBy, setSortBy] = useState("default");

  useEffect(() => {
    const fetchProducts = async () => {
      try {
        const base = import.meta.env.VITE_API_BASE_URL || "/backend";
        const res = await fetch(`${base}/get_products.php`);
        const data = await res.json();

        if (!res.ok) {
          console.error("Server error details:", data);
          throw new Error(
            data.message || data.error || "Failed to fetch products"
          );
        }

        // Handle both old format (array) and new format (object with success property)
        const productsData = data.success ? data.products : data;
        setProducts(productsData);
      } catch (err) {
        console.error("Gabim në ngarkimin e produkteve:", err);
        setError(err.message);
      } finally {
        setLoading(false);
      }
    };

    fetchProducts();
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

    addToCart(product);
    // Show toast notification
    const toast = document.createElement("div");
    toast.className =
      "fixed bottom-4 right-4 bg-green-600 text-white px-6 py-3 rounded-lg shadow-lg z-50";
    toast.textContent = `${product.name} u shtua në shportë!`;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
  };

  const sortedProducts = useMemo(() => {
    let filtered = products.filter((product) =>
      product.name.toLowerCase().includes(searchQuery.toLowerCase())
    );

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
  }, [products, searchQuery, sortBy]);

  if (loading) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="text-center w-full">
          <div className="animate-spin rounded-full h-16 w-16 border-b-4 border-blue-600 mb-4 mx-auto"></div>
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
            className="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
          >
            Provo Përsëri
          </button>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-50 py-12">
      <div className="container mx-auto px-4 sm:px-6 lg:px-8">
        {/* Header */}
        <div className="mb-8">
          <h1 className="text-4xl font-bold text-gray-900 mb-4">
            Produktet Tona
          </h1>
          <p className="text-xl text-gray-600">
            Zbuloni koleksionin tonë të produkteve me cilësi hoteli
          </p>
        </div>

        {/* Search and Filters */}
        <div className="bg-white rounded-lg shadow-md p-6 mb-8">
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
                className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
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
                className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
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
                    className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300"
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
                </div>
                <div className="p-3 sm:p-5">
                  <h3 className="text-sm sm:text-lg font-semibold text-gray-900 mb-2 group-hover:text-blue-600 transition-colors line-clamp-2">
                    {product.name}
                  </h3>
                  <p className="text-gray-600 text-xs sm:text-sm mb-3 sm:mb-4 line-clamp-2 hidden sm:block">
                    {product.description}
                  </p>
                  <div className="flex items-center justify-between mb-2 sm:mb-3">
                    <span className="text-lg sm:text-2xl font-bold text-blue-600">
                      €{Number(product.price).toFixed(2)}
                    </span>
                    {(product.available ?? true) && (
                      <span className="text-xs sm:text-sm text-green-600 font-medium">
                        Në stok
                      </span>
                    )}
                  </div>
                  <button
                    onClick={(e) => {
                      e.preventDefault();
                      if (product.available ?? true) {
                        handleAddToCart(product);
                      }
                    }}
                    disabled={!(product.available ?? true)}
                    className={`w-full py-2 rounded-lg transition-colors font-semibold text-sm ${
                      product.available ?? true
                        ? "bg-blue-600 hover:bg-blue-700 text-white"
                        : "bg-gray-300 text-gray-500 cursor-not-allowed"
                    }`}
                  >
                    {product.available ?? true ? "Shto në Shportë" : "Mbaruar"}
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
              }}
              className="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-semibold"
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
