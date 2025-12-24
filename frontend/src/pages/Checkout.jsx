import { useState, useEffect, useRef } from "react";
import { useNavigate } from "react-router-dom";
import { useCart } from "../context/CartContext";
import { useAuth } from "../context/AuthContext";
import { api } from "../services/api";

function Checkout() {
  const navigate = useNavigate();
  const { cart, clearCart } = useCart();
  const { user, isAuthenticated } = useAuth();
  const isSubmitting = useRef(false);

  const [formData, setFormData] = useState({
    name: "",
    phone: "",
    address: "",
    city: "",
    country: "kosovo",
    notes: "",
    acceptedPolicy: false,
  });

  const [errors, setErrors] = useState({});
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");

  // Auto-fill form with user profile data if logged in
  useEffect(() => {
    const loadUserProfile = async () => {
      if (isAuthenticated && user) {
        try {
          const response = await api.getProfile();
          if (response.success && response.user) {
            setFormData((prev) => ({
              ...prev,
              name: response.user.name || "",
              phone: response.user.phone || "",
              address: response.user.address || "",
              city: response.user.city || "",
              country: response.user.country || "kosovo",
            }));
          }
        } catch (error) {
          console.error("Error loading profile:", error);
          if (user) {
            setFormData((prev) => ({
              ...prev,
              name: user.name || "",
              phone: user.phone || "",
              address: user.address || "",
              city: user.city || "",
              country: user.country || "kosovo",
            }));
          }
        }
      }
    };

    loadUserProfile();
  }, [isAuthenticated, user]);

  // Redirect if cart is empty (but not during submission)
  useEffect(() => {
    if (cart && cart.length === 0 && !isSubmitting.current) {
      const timer = setTimeout(() => {
        navigate("/products");
      }, 2000);

      return () => clearTimeout(timer);
    }
  }, [cart, navigate]);

  // Calculate shipping based on country
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
      return 2.0; // flat shipping for Kosovo
    }

    if (normalized === "albania" || normalized === "north macedonia") {
      return 5.0;
    }

    return 5.0;
  };

  // Calculate totals
  const subtotal =
    cart?.reduce((sum, item) => sum + item.price * item.quantity, 0) || 0;
  const shipping = calculateShipping(formData.country, subtotal);
  const tax = 0;
  const total = subtotal + shipping;

  const handleInputChange = (e) => {
    const { name, type, value, checked } = e.target;
    const nextValue = type === "checkbox" ? checked : value;
    setFormData((prev) => ({
      ...prev,
      [name]: nextValue,
    }));
    if (errors[name]) {
      setErrors((prev) => ({
        ...prev,
        [name]: "",
      }));
    }
  };

  const validateForm = () => {
    const newErrors = {};

    if (!formData.name.trim()) {
      newErrors.name = "Emri është i detyrueshëm";
    }

    // Email removed from checkout per request

    if (!formData.phone.trim()) {
      newErrors.phone = "Numri i telefonit është i detyrueshëm";
    }

    if (!formData.address.trim()) {
      newErrors.address = "Adresa është e detyrueshme";
    }

    if (!formData.city.trim()) {
      newErrors.city = "Qyteti është i detyrueshëm";
    }

    if (!formData.country.trim()) {
      newErrors.country = "Shteti është i detyrueshëm";
    }

    if (!formData.acceptedPolicy) {
      newErrors.acceptedPolicy =
        "Ju lutemi pranoni politikën e privatësisë për të vazhduar";
    }

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = async (e) => {
    e.preventDefault();

    if (!validateForm()) {
      setError("Ju lutem plotësoni të gjitha fushat e detyrueshme");
      return;
    }

    setLoading(true);
    setError("");
    isSubmitting.current = true; // Prevent redirect during submission

    try {
      const orderData = {
        customer_name: formData.name,
        customer_phone: formData.phone,
        customer_address: formData.address,
        customer_city: formData.city,
        customer_country: formData.country,
        subtotal: subtotal,
        shipping_cost: shipping,
        tax: 0,
        total_amount: subtotal + shipping,
        payment_method: "cash",
        notes: formData.notes || "",
        items: cart.map((item) => ({
          product_id: item.id,
          product_name: item.name,
          quantity: item.quantity,
          price: item.price,
          selected_size: item.selectedSize || null,
        })),
      };

      console.log("Sending order data:", orderData);

      const response = await fetch(
        "http://localhost/hotel-ks/backend/create_order.php",
        {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          credentials: "include",
          body: JSON.stringify(orderData),
        }
      );

      const contentType = response.headers.get("content-type");
      if (!contentType || !contentType.includes("application/json")) {
        const text = await response.text();
        console.error("Non-JSON response:", text);
        throw new Error(
          "Server returned an invalid response. Please check the backend logs."
        );
      }

      const data = await response.json();
      console.log("Order response:", data);

      if (data.success) {
        // Store cart items before clearing
        const cartItems = [...cart];

        // Clear cart
        clearCart();

        // Navigate to success page with order data
        navigate("/order-success", {
          replace: true,
          state: {
            orderNumber: data.order_number,
            orderData: {
              name: formData.name,
              phone: formData.phone,
              address: formData.address,
              city: formData.city,
              country: formData.country,
              subtotal: subtotal,
              shipping: shipping,
              tax: tax,
              total: total,
              items: cartItems,
            },
          },
        });
      } else {
        isSubmitting.current = false;
        setError(data.error || "Ndodhi një gabim gjatë krijimit të porosisë");
        console.error("Backend error:", data.error);
      }
    } catch (err) {
      isSubmitting.current = false;
      console.error("Order error:", err);
      setError(
        err.message ||
          "Ndodhi një gabim gjatë krijimit të porosisë. Ju lutem provoni përsëri."
      );
    } finally {
      setLoading(false);
    }
  };

  // Show loading state while checking cart
  if (!cart) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="text-center">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
          <p className="mt-4 text-gray-600">Duke ngarkuar...</p>
        </div>
      </div>
    );
  }

  if (cart.length === 0 && !isSubmitting.current) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="text-center">
          <p className="text-gray-600">
            Shporta është bosh. Duke ju ridrejtuar...
          </p>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-50 py-12">
      <div className="container mx-auto px-4 sm:px-6 lg:px-8">
        <div className="max-w-4xl mx-auto">
          {/* Header */}
          <div className="mb-8">
            <h1 className="text-3xl font-bold text-gray-900">
              Përfundimi i Porosisë
            </h1>
            <p className="text-gray-600 mt-2">
              Plotësoni të dhënat tuaja për të përfunduar porosinë
            </p>
          </div>

          {error && (
            <div className="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
              {error}
            </div>
          )}

          <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {/* Checkout Form */}
            <div className="lg:col-span-2">
              <form
                onSubmit={handleSubmit}
                className="bg-white rounded-lg shadow-md p-6"
              >
                <h2 className="text-xl font-bold text-gray-900 mb-6">
                  Informacioni i Dërgimit
                </h2>

                <div className="space-y-4">
                  {/* Name */}
                  <div>
                    <label className="block text-sm font-semibold text-gray-700 mb-2">
                      Emri i Plotë <span className="text-red-500">*</span>
                    </label>
                    <input
                      type="text"
                      name="name"
                      value={formData.name}
                      onChange={handleInputChange}
                      className={`w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent ${
                        errors.name ? "border-red-500" : "border-gray-300"
                      }`}
                      placeholder="Emri dhe Mbiemri"
                    />
                    {errors.name && (
                      <p className="text-red-500 text-sm mt-1">{errors.name}</p>
                    )}
                  </div>

                  {/* Email removed per request */}

                  {/* Phone */}
                  <div>
                    <label className="block text-sm font-semibold text-gray-700 mb-2">
                      Numri i Telefonit <span className="text-red-500">*</span>
                    </label>
                    <input
                      type="tel"
                      name="phone"
                      value={formData.phone}
                      onChange={handleInputChange}
                      className={`w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent ${
                        errors.phone ? "border-red-500" : "border-gray-300"
                      }`}
                      placeholder="+383 XX XXX XXX"
                    />
                    {errors.phone && (
                      <p className="text-red-500 text-sm mt-1">
                        {errors.phone}
                      </p>
                    )}
                  </div>

                  {/* Address */}
                  <div>
                    <label className="block text-sm font-semibold text-gray-700 mb-2">
                      Adresa <span className="text-red-500">*</span>
                    </label>
                    <input
                      type="text"
                      name="address"
                      value={formData.address}
                      onChange={handleInputChange}
                      className={`w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent ${
                        errors.address ? "border-red-500" : "border-gray-300"
                      }`}
                      placeholder="Rruga, Numri i Shtëpisë"
                    />
                    {errors.address && (
                      <p className="text-red-500 text-sm mt-1">
                        {errors.address}
                      </p>
                    )}
                  </div>

                  {/* City and Country */}
                  <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <label className="block text-sm font-semibold text-gray-700 mb-2">
                        Qyteti <span className="text-red-500">*</span>
                      </label>
                      <input
                        type="text"
                        name="city"
                        value={formData.city}
                        onChange={handleInputChange}
                        className={`w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent ${
                          errors.city ? "border-red-500" : "border-gray-300"
                        }`}
                        placeholder="Qyteti"
                      />
                      {errors.city && (
                        <p className="text-red-500 text-sm mt-1">
                          {errors.city}
                        </p>
                      )}
                    </div>

                    <div>
                      <label className="block text-sm font-semibold text-gray-700 mb-2">
                        Shteti <span className="text-red-500">*</span>
                      </label>
                      <select
                        name="country"
                        value={formData.country}
                        onChange={handleInputChange}
                        className={`w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent ${
                          errors.country ? "border-red-500" : "border-gray-300"
                        }`}
                      >
                        <option value="kosovo">Kosovë</option>
                        <option value="albania">Shqipëri</option>
                        <option value="north macedonia">
                          Maqedoni e Veriut
                        </option>
                      </select>
                      {errors.country && (
                        <p className="text-red-500 text-sm mt-1">
                          {errors.country}
                        </p>
                      )}
                    </div>
                  </div>

                  {/* Notes */}
                  <div>
                    <label className="block text-sm font-semibold text-gray-700 mb-2">
                      Shënime (opsionale)
                    </label>
                    <textarea
                      name="notes"
                      value={formData.notes}
                      onChange={handleInputChange}
                      rows={3}
                      className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                      placeholder="Shënime për porosinë..."
                    ></textarea>
                  </div>

                  {/* Privacy policy acceptance */}
                  <div>
                    <label className="flex items-start space-x-3">
                      <input
                        type="checkbox"
                        name="acceptedPolicy"
                        checked={formData.acceptedPolicy}
                        onChange={handleInputChange}
                        className={`mt-1 h-4 w-4 text-blue-600 rounded border-gray-300 focus:ring-2 focus:ring-blue-500 ${
                          errors.acceptedPolicy ? "ring-2 ring-red-500" : ""
                        }`}
                      />
                      <span className="text-sm text-gray-700">
                        Kam lexuar dhe pranoj{" "}
                        <a href="/policy" className="text-blue-600 underline">
                          Politikën e Privatësisë
                        </a>
                        <span className="text-red-500">*</span>
                      </span>
                    </label>
                    {errors.acceptedPolicy && (
                      <p className="text-red-500 text-sm mt-1">
                        {errors.acceptedPolicy}
                      </p>
                    )}
                  </div>
                </div>

                <button
                  type="submit"
                  disabled={loading}
                  className="mt-8 w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200 flex items-center justify-center"
                >
                  {loading ? (
                    <>
                      <svg
                        className="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                      >
                        <circle
                          className="opacity-25"
                          cx="12"
                          cy="12"
                          r="10"
                          stroke="currentColor"
                          strokeWidth="4"
                        ></circle>
                        <path
                          className="opacity-75"
                          fill="currentColor"
                          d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                        ></path>
                      </svg>
                      Po procesohet...
                    </>
                  ) : (
                    `Porositë me Pagesë në Lokal`
                  )}
                </button>
              </form>
            </div>

            {/* Order Summary */}
            <div className="lg:col-span-1">
              <div className="bg-white rounded-lg shadow-md p-6 sticky top-8">
                <h2 className="text-xl font-bold text-gray-900 mb-4">
                  Përmbajtja e Porosisë
                </h2>

                <div className="space-y-3 mb-6">
                  {cart.map((item) => (
                    <div
                      key={item.id}
                      className="flex justify-between items-center"
                    >
                      <div>
                        <p className="font-medium text-gray-900">{item.name}</p>
                        <p className="text-sm text-gray-500">
                          x{item.quantity}
                        </p>
                      </div>
                      <p className="font-medium text-gray-900">
                        {(item.price * item.quantity).toFixed(2)}€
                      </p>
                    </div>
                  ))}
                </div>

                <div className="border-t border-gray-200 pt-4 space-y-2">
                  <div className="flex justify-between">
                    <span className="text-gray-600">Nëntotal:</span>
                    <span className="text-gray-900">
                      {subtotal.toFixed(2)}€
                    </span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-gray-600">Dërgesa:</span>
                    <span className="text-gray-900">
                      {shipping.toFixed(2)}€
                    </span>
                  </div>
                  <div className="flex justify-between font-bold text-lg mt-2 pt-2 border-t border-gray-200">
                    <span>Totali:</span>
                    <span>{total.toFixed(2)}€</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

export default Checkout;
