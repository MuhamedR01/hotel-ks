import { useState, useEffect } from "react";
import { Link, useNavigate } from "react-router-dom";
import { useCart } from "../context/CartContext";
import { useAuth } from "../context/AuthContext";
import { Trash2, Plus, Minus, ShoppingBag } from "lucide-react";

const Cart = () => {
  const PLACEHOLDER_IMAGE =
    "data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==";
  const navigate = useNavigate();
  const { cart, removeFromCart, updateQuantity, getCartTotal, clearCart } =
    useCart();
  const [fetchedImages, setFetchedImages] = useState({});
  const [promoCode, setPromoCode] = useState("");
  const [discount, setDiscount] = useState(0);
  const [promoError, setPromoError] = useState("");

  const subtotal = getCartTotal();

  const normalizeCountry = (country) => {
    if (!country) return "";
    const cleaned = country
      .toString()
      .toLowerCase()
      .normalize("NFD")
      .replace(/\p{Diacritic}/gu, "")
      .replace(/[^a-z0-9\s]/g, " ")
      .replace(/\s+/g, " ")
      .trim();

    if (cleaned.includes("kosov")) return "kosovo";
    if (cleaned.includes("alban")) return "albania";
    if (cleaned.includes("maced") || cleaned.includes("maqed"))
      return "north macedonia";
    return cleaned;
  };

  const calculateShipping = (country, subtotal) => {
    const normalized = normalizeCountry(country || "");

    if (normalized === "kosovo") {
      return 2.0;
    }

    if (normalized === "albania" || normalized === "north macedonia") {
      return 5.0;
    }

    return 5.0;
  };

  const { user } = useAuth();
  const userCountry = user?.country || "";
  const hasShipping = !!user && userCountry.toString().trim() !== "";
  const shipping = hasShipping
    ? calculateShipping(userCountry, subtotal)
    : null;
  const tax = 0;
  const total = subtotal + (shipping || 0) - discount;

  const handleQuantityChange = (itemId, newQuantity, selectedSize) => {
    if (newQuantity < 1) return;
    updateQuantity(itemId, selectedSize, newQuantity);
  };

  const handleRemoveItem = (productId, selectedSize) => {
    if (
      window.confirm(
        "Jeni të sigurt që dëshironi të hiqni këtë produkt nga shporta?",
      )
    ) {
      removeFromCart(productId, selectedSize);
    }
  };

  const handleClearCart = () => {
    if (window.confirm("Jeni të sigurt që dëshironi të zbrazni shportën?")) {
      clearCart();
    }
  };

  const handleApplyPromo = () => {
    setPromoError("");
    // Mock promo codes
    const promoCodes = {
      SAVE10: 10,
      SAVE20: 20,
      WELCOME: 15,
    };

    if (promoCodes[promoCode.toUpperCase()]) {
      setDiscount(promoCodes[promoCode.toUpperCase()]);
      setPromoError("");
    } else if (promoCode) {
      setPromoError("Kodi promocional i pavlefshëm");
      setDiscount(0);
    }
  };

  const handleCheckout = () => {
    if (cart.length === 0) return;
    navigate("/checkout");
  };

  // Fetch product images for items that don't have an image stored (avoid storing base64 in localStorage)
  useEffect(() => {
    let active = true;

    const toFetch = cart.filter((item) => !item.image);
    if (toFetch.length === 0) return;

    toFetch.forEach((item) => {
      const key = `${item.id}-${item.selectedSize || "no-size"}`;
      // avoid refetching
      if (fetchedImages[key]) return;

      const base = import.meta.env.VITE_API_BASE_URL || "/api";
      fetch(`${base}/products/${item.id}`)
        .then((res) => res.json())
        .then((data) => {
          if (!active) return;
          if (data && data.success && data.product) {
            const img =
              data.product.image ||
              (data.product.images && data.product.images[0]) ||
              null;
            setFetchedImages((prev) => ({ ...prev, [key]: img }));
          }
        })
        .catch((err) => {
          console.error("Failed to fetch product image for", item.id, err);
        });
    });

    return () => {
      active = false;
    };
  }, [cart, fetchedImages]);

  if (cart.length === 0) {
    return (
      <div className="min-h-screen bg-gray-50 py-12">
        <div className="container mx-auto px-4 sm:px-6 lg:px-8">
          <div className="max-w-2xl mx-auto text-center">
            <ShoppingBag className="w-24 h-24 mx-auto text-gray-400 mb-6" />
            <h2 className="text-3xl font-bold text-gray-900 mb-4">
              Shporta është bosh
            </h2>
            <p className="text-gray-600 mb-8">
              Nuk keni shtuar asnjë produkt në shportë ende.
            </p>
            <Link
              to="/products"
              className="inline-block px-8 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-semibold"
            >
              Vazhdo Blerjen
            </Link>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-50 py-12">
      <div className="container mx-auto px-4 sm:px-6 lg:px-8">
        {/* Header */}
        <div className="mb-8">
          <h1 className="text-3xl font-bold text-gray-900">
            Shporta e Blerjeve
          </h1>
          <p className="text-gray-600 mt-2">
            {cart.length} {cart.length === 1 ? "artikull" : "artikuj"} në
            shportën tuaj
          </p>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
          {/* Cart Items */}
          <div className="lg:col-span-2">
            <div className="bg-white rounded-lg shadow-md overflow-hidden">
              {/* Desktop View */}
              <div className="hidden md:block">
                <table className="w-full">
                  <thead className="bg-gray-50 border-b border-gray-200">
                    <tr>
                      <th className="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        Produkti
                      </th>
                      <th className="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        Madhësia
                      </th>
                      <th className="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        Çmimi
                      </th>
                      <th className="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        Sasia
                      </th>
                      <th className="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        Totali
                      </th>
                      <th className="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        Veprime
                      </th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-gray-200">
                    {cart.map((item) => {
                      // Create unique key combining product id and size
                      const itemKey = `${item.id}-${
                        item.selectedSize || "no-size"
                      }`;
                      const displayImage =
                        item.image ||
                        fetchedImages[itemKey] ||
                        PLACEHOLDER_IMAGE;
                      return (
                        <tr
                          key={itemKey}
                          className="hover:bg-gray-50 transition-colors"
                        >
                          <td className="px-6 py-4">
                            <div className="flex items-center space-x-4">
                              <img
                                src={displayImage}
                                alt={item.name}
                                className="w-20 h-20 object-cover rounded-lg"
                                onError={(e) => {
                                  e.target.onerror = null;
                                  e.target.src = PLACEHOLDER_IMAGE;
                                }}
                              />
                              <div>
                                <h3 className="font-semibold text-gray-900">
                                  {item.name}
                                </h3>
                                <p className="text-sm text-gray-500 line-clamp-1">
                                  {item.description}
                                </p>
                              </div>
                            </div>
                          </td>
                          <td className="px-6 py-4">
                            <span className="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-blue-100 text-blue-800">
                              {(() => {
                                if (
                                  !item.selectedSize &&
                                  item.selectedSize !== 0
                                )
                                  return "N/A";
                                if (
                                  typeof item.selectedSize === "string" ||
                                  typeof item.selectedSize === "number"
                                )
                                  return String(item.selectedSize);
                                if (
                                  item.selectedSize &&
                                  typeof item.selectedSize === "object"
                                ) {
                                  return (
                                    item.selectedSize.size ||
                                    item.selectedSize.label ||
                                    "N/A"
                                  );
                                }
                                return "N/A";
                              })()}
                            </span>
                          </td>
                          <td className="px-6 py-4">
                            <span className="font-semibold text-gray-900">
                              €{parseFloat(item.price).toFixed(2)}
                            </span>
                          </td>
                          <td className="px-6 py-4">
                            <div className="flex items-center space-x-2">
                              <button
                                onClick={() =>
                                  handleQuantityChange(
                                    item.id,
                                    item.quantity - 1,
                                    item.selectedSize,
                                  )
                                }
                                className="w-8 h-8 rounded-lg border border-gray-300 hover:border-blue-600 hover:bg-blue-50 flex items-center justify-center transition-colors"
                                disabled={item.quantity <= 1}
                              >
                                <Minus className="w-4 h-4" />
                              </button>
                              <span className="w-12 text-center font-semibold">
                                {item.quantity}
                              </span>
                              <button
                                onClick={() =>
                                  handleQuantityChange(
                                    item.id,
                                    item.quantity + 1,
                                    item.selectedSize,
                                  )
                                }
                                className="w-8 h-8 rounded-lg border border-gray-300 hover:border-blue-600 hover:bg-blue-50 flex items-center justify-center transition-colors"
                              >
                                <Plus className="w-4 h-4" />
                              </button>
                            </div>
                          </td>
                          <td className="px-6 py-4">
                            <span className="font-bold text-gray-900">
                              €{(item.price * item.quantity).toFixed(2)}
                            </span>
                          </td>
                          <td className="px-6 py-4">
                            <button
                              onClick={() =>
                                handleRemoveItem(item.id, item.selectedSize)
                              }
                              className="text-red-600 hover:text-red-800 transition-colors"
                              title="Hiq nga shporta"
                            >
                              <Trash2 className="w-5 h-5" />
                            </button>
                          </td>
                        </tr>
                      );
                    })}
                  </tbody>
                </table>
              </div>

              {/* Mobile View */}
              <div className="md:hidden space-y-4">
                {cart.map((item) => {
                  const itemKey = `${item.id}-${
                    item.selectedSize || "no-size"
                  }`;
                  const displayImage =
                    item.image || fetchedImages[itemKey] || PLACEHOLDER_IMAGE;
                  return (
                    <div key={itemKey} className="p-4 border-b border-gray-200">
                      <div className="flex space-x-4 mb-4">
                        <img
                          src={displayImage}
                          alt={item.name}
                          className="w-24 h-24 object-cover rounded-lg"
                          onError={(e) => {
                            e.target.onerror = null;
                            e.target.src = PLACEHOLDER_IMAGE;
                          }}
                        />
                        <div className="flex-1">
                          <h3 className="font-semibold text-gray-900 mb-1">
                            {item.name}
                          </h3>
                          <div className="flex items-center gap-2 mb-2">
                            <span className="text-sm text-gray-600">
                              Madhësia:
                            </span>
                            <span className="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                              {(() => {
                                if (
                                  !item.selectedSize &&
                                  item.selectedSize !== 0
                                )
                                  return "N/A";
                                if (
                                  typeof item.selectedSize === "string" ||
                                  typeof item.selectedSize === "number"
                                )
                                  return String(item.selectedSize);
                                if (
                                  item.selectedSize &&
                                  typeof item.selectedSize === "object"
                                ) {
                                  return (
                                    item.selectedSize.size ||
                                    item.selectedSize.label ||
                                    "N/A"
                                  );
                                }
                                return "N/A";
                              })()}
                            </span>
                          </div>
                          <p className="text-lg font-bold text-blue-600">
                            €{parseFloat(item.price).toFixed(2)}
                          </p>
                        </div>
                      </div>

                      <div className="flex items-center justify-between pt-4 border-t border-gray-200">
                        <div className="flex items-center space-x-3">
                          <button
                            onClick={() =>
                              handleQuantityChange(
                                item.id,
                                item.quantity - 1,
                                item.selectedSize,
                              )
                            }
                            className="w-8 h-8 rounded-lg border border-gray-300 hover:border-blue-600 hover:bg-blue-50 flex items-center justify-center transition-colors"
                            disabled={item.quantity <= 1}
                          >
                            <Minus className="w-4 h-4" />
                          </button>
                          <span className="w-12 text-center font-semibold">
                            {item.quantity}
                          </span>
                          <button
                            onClick={() =>
                              handleQuantityChange(
                                item.id,
                                item.quantity + 1,
                                item.selectedSize,
                              )
                            }
                            className="w-8 h-8 rounded-lg border border-gray-300 hover:border-blue-600 hover:bg-blue-50 flex items-center justify-center transition-colors"
                          >
                            <Plus className="w-4 h-4" />
                          </button>
                        </div>

                        <div className="flex items-center space-x-4">
                          <span className="font-bold text-gray-900">
                            €{(item.price * item.quantity).toFixed(2)}
                          </span>
                          <button
                            onClick={() =>
                              handleRemoveItem(item.id, item.selectedSize)
                            }
                            className="text-red-600 hover:text-red-800 transition-colors p-2"
                          >
                            <Trash2 className="w-5 h-5" />
                          </button>
                        </div>
                      </div>
                    </div>
                  );
                })}
              </div>

              {/* Clear Cart Button */}
              <div className="p-6 bg-gray-50">
                <button
                  onClick={handleClearCart}
                  className="text-red-600 hover:text-red-700 font-semibold transition-colors"
                >
                  Zbraz Shportën
                </button>
              </div>
            </div>
          </div>

          {/* Order Summary */}
          <div className="lg:col-span-1">
            <div className="bg-white rounded-lg shadow-md p-6 sticky top-4">
              <h2 className="text-xl font-bold text-gray-900 mb-4">
                Përmbledhje e Porosisë
              </h2>

              {/* Promo Code */}
              <div className="mb-6">
                <label className="block text-sm font-semibold text-gray-700 mb-2">
                  Kodi Promocional
                </label>
                <div className="flex gap-2">
                  <input
                    type="text"
                    value={promoCode}
                    onChange={(e) => setPromoCode(e.target.value)}
                    placeholder="Vendosni kodin"
                    className="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                  <button
                    onClick={handleApplyPromo}
                    className="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors font-semibold"
                  >
                    Apliko
                  </button>
                </div>
                {promoError && (
                  <p className="text-red-600 text-sm mt-1">{promoError}</p>
                )}
                {discount > 0 && (
                  <p className="text-green-600 text-sm mt-1">
                    Kodi promocional u aplikua! -€{discount.toFixed(2)}
                  </p>
                )}
              </div>

              {/* Price Breakdown */}
              <div className="space-y-3 mb-6">
                <div className="flex justify-between text-gray-600">
                  <span>Nëntotali</span>
                  <span>€{subtotal.toFixed(2)}</span>
                </div>
                <div className="flex justify-between text-gray-600">
                  <span>Dërgesa</span>
                  <span>
                    {shipping === null ? (
                      <Link to="/checkout" className="text-blue-600 underline">
                        Do të llogaritet në përfundim
                      </Link>
                    ) : shipping === 0 ? (
                      "FALAS"
                    ) : (
                      `€${shipping.toFixed(2)}`
                    )}
                  </span>
                </div>
                {discount > 0 && (
                  <div className="flex justify-between text-green-600">
                    <span>Zbritje</span>
                    <span>-€{discount.toFixed(2)}</span>
                  </div>
                )}
                <div className="border-t border-gray-200 pt-3">
                  <div className="flex justify-between text-lg font-bold text-gray-900">
                    <span>Totali</span>
                    <span>
                      €{(subtotal + (shipping || 0) - discount).toFixed(2)}
                    </span>
                  </div>
                  {shipping === null && (
                    <p className="text-sm text-gray-500 mt-2">
                      Dërgesa do të kalkulohet në faqen e përfundimit të
                      porosisë.
                    </p>
                  )}
                </div>
              </div>

              {/* Free shipping incentive removed per request */}

              {/* Checkout Button */}
              <button
                onClick={handleCheckout}
                className="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 transition-colors duration-200 font-semibold mb-3"
              >
                Vazhdo në Pagesë
              </button>

              {/* Continue Shopping */}
              <Link
                to="/products"
                className="block w-full text-center bg-gray-200 text-gray-700 py-3 rounded-lg hover:bg-gray-300 transition-colors duration-200 font-semibold"
              >
                Vazhdoni Blerjet
              </Link>

              {/* Security Badges */}
              <div className="mt-6 pt-6 border-t border-gray-200">
                <div className="flex items-center justify-center gap-4 text-gray-500">
                  <svg
                    className="w-8 h-8"
                    fill="currentColor"
                    viewBox="0 0 20 20"
                  >
                    <path
                      fillRule="evenodd"
                      d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                      clipRule="evenodd"
                    />
                  </svg>
                  <svg
                    className="w-8 h-8"
                    fill="currentColor"
                    viewBox="0 0 20 20"
                  >
                    <path
                      fillRule="evenodd"
                      d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                      clipRule="evenodd"
                    />
                  </svg>
                  <svg
                    className="w-8 h-8"
                    fill="currentColor"
                    viewBox="0 0 20 20"
                  >
                    <path
                      fillRule="evenodd"
                      d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                      clipRule="evenodd"
                    />
                  </svg>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default Cart;
