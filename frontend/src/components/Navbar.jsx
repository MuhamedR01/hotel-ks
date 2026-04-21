import { Link, useNavigate } from "react-router-dom";
import { useState, useEffect } from "react";
import { useAuth } from "../context/AuthContext";
import { useCart } from "../context/CartContext";
import { ShoppingCart, User, Menu, X, LogOut, Package } from "lucide-react";

function Navbar() {
  const [isMenuOpen, setIsMenuOpen] = useState(false);
  const [isProfileOpen, setIsProfileOpen] = useState(false);
  const [isScrolled, setIsScrolled] = useState(false);
  const { user, logout } = useAuth();
  const { cart } = useCart();
  const navigate = useNavigate();

  // Calculate cart total and items count
  const cartTotal =
    cart?.reduce((sum, item) => sum + item.price * item.quantity, 0) || 0;
  const cartItemsCount =
    cart?.reduce((total, item) => total + item.quantity, 0) || 0;

  // Handle scroll effect
  useEffect(() => {
    const handleScroll = () => {
      setIsScrolled(window.scrollY > 20);
    };
    window.addEventListener("scroll", handleScroll);
    return () => window.removeEventListener("scroll", handleScroll);
  }, []);

  // Close profile menu when clicking outside
  useEffect(() => {
    const handleClickOutside = (event) => {
      if (isProfileOpen && !event.target.closest(".profile-menu-container")) {
        setIsProfileOpen(false);
      }
    };
    document.addEventListener("mousedown", handleClickOutside);
    return () => document.removeEventListener("mousedown", handleClickOutside);
  }, [isProfileOpen]);

  const handleLogout = () => {
    logout();
    setIsProfileOpen(false);
    navigate("/");
  };

  const toggleMenu = () => {
    setIsMenuOpen(!isMenuOpen);
  };

  const closeMenu = () => {
    setIsMenuOpen(false);
  };

  return (
    <nav
      className={`bg-white shadow-lg sticky top-0 z-50 transition-all duration-300 ${
        isScrolled ? "shadow-xl" : ""
      }`}
    >
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex justify-between items-center h-16">
          {/* Left Side - Mobile Menu & Logo */}
          <div className="flex items-center space-x-3">
            {/* Mobile Menu Button */}
            <button
              onClick={toggleMenu}
              className="md:hidden text-gray-700 hover:text-blue-600 transition-colors"
            >
              {isMenuOpen ? (
                <X className="w-6 h-6" />
              ) : (
                <Menu className="w-6 h-6" />
              )}
            </button>

            {/* Logo */}
            <Link
              to="/"
              className="flex items-center space-x-2 group"
              onClick={closeMenu}
            >
              <img
                src="/icon.svg"
                alt="minimodaks Logo"
                className="w-10 h-10 transition-transform group-hover:scale-110 duration-200"
              />
              <span className="text-xl font-bold bg-gradient-to-r from-blue-600 to-blue-700 bg-clip-text text-transparent hidden sm:block">
                minimodaks
              </span>
            </Link>
          </div>

          {/* Desktop Navigation */}
          <div className="hidden md:flex items-center space-x-8">
            <Link
              to="/"
              className="text-gray-700 hover:text-blue-600 transition-colors font-medium"
            >
              Produktet
            </Link>
            <Link
              to="/contact"
              className="text-gray-700 hover:text-blue-600 transition-colors font-medium"
            >
              Kontakti
            </Link>
          </div>

          {/* Right Side - Cart & User Menu */}
          <div className="flex items-center space-x-2 md:space-x-4">
            {/* Mobile Cart Icon */}
            <Link to="/cart" className="md:hidden relative">
              <div className="relative p-2 hover:bg-gray-100 rounded-lg transition-colors">
                <ShoppingCart className="w-6 h-6 text-gray-700" />
                {cartItemsCount > 0 && (
                  <span className="absolute top-0 right-0 bg-blue-600 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center">
                    {cartItemsCount > 9 ? "9+" : cartItemsCount}
                  </span>
                )}
              </div>
            </Link>

            {/* Desktop Cart with Total Price */}
            <Link to="/cart" className="hidden md:block relative group">
              <div className="flex items-center space-x-2 px-3 py-2 rounded-lg hover:bg-gray-100 transition-colors">
                <div className="relative">
                  <ShoppingCart className="w-6 h-6 text-gray-700 group-hover:text-blue-600 transition-colors" />
                  {cartItemsCount > 0 && (
                    <span className="absolute -top-2 -right-2 bg-blue-600 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center">
                      {cartItemsCount > 9 ? "9+" : cartItemsCount}
                    </span>
                  )}
                </div>
                <div className="flex flex-col items-start">
                  <span className="text-xs text-gray-500">Shporta</span>
                  <span className="text-sm font-bold text-gray-900">
                    €{cartTotal.toFixed(2)}
                  </span>
                </div>
              </div>
            </Link>

            {/* Desktop User Menu */}
            <div className="hidden md:block">
              {user ? (
                <div className="relative profile-menu-container">
                  <button
                    onClick={() => setIsProfileOpen(!isProfileOpen)}
                    className="flex items-center space-x-2 px-3 py-2 rounded-lg hover:bg-gray-100 transition-colors"
                  >
                    <div className="w-8 h-8 bg-gradient-to-br from-blue-600 to-blue-700 text-white rounded-full flex items-center justify-center font-semibold">
                      {user.name ? user.name.charAt(0).toUpperCase() : "U"}
                    </div>
                    <span className="font-medium text-gray-700">
                      {user.name}
                    </span>
                    <svg
                      className={`w-4 h-4 transition-transform text-gray-500 ${
                        isProfileOpen ? "rotate-180" : ""
                      }`}
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        strokeWidth={2}
                        d="M19 9l-7 7-7-7"
                      />
                    </svg>
                  </button>

                  {/* Dropdown Menu */}
                  {isProfileOpen && (
                    <div className="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-xl border border-gray-200 py-2 animate-fadeIn">
                      <div className="px-4 py-3 border-b border-gray-200">
                        <p className="text-sm font-semibold text-gray-900">
                          {user.name}
                        </p>
                        <p className="text-xs text-gray-500 truncate">
                          {user.email}
                        </p>
                      </div>

                      <Link
                        to="/profile"
                        onClick={() => setIsProfileOpen(false)}
                        className="flex items-center space-x-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
                      >
                        <User className="w-4 h-4" />
                        <span>Profili Im</span>
                      </Link>

                      {/* Porositë e Mia temporarily removed */}

                      <div className="border-t border-gray-200 mt-2 pt-2">
                        <button
                          onClick={handleLogout}
                          className="flex items-center space-x-3 px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors w-full"
                        >
                          <LogOut className="w-4 h-4" />
                          <span>Dilni</span>
                        </button>
                      </div>
                    </div>
                  )}
                </div>
              ) : (
                <div className="flex items-center space-x-2">
                  <Link
                    to="/login"
                    className="px-4 py-2 text-gray-700 hover:text-blue-600 transition-colors font-medium"
                  >
                    Kyçu
                  </Link>
                  <Link
                    to="/signup"
                    className="px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all font-medium shadow-md hover:shadow-lg"
                  >
                    Regjistrohu
                  </Link>
                </div>
              )}
            </div>
          </div>
        </div>
      </div>

      {/* Mobile Menu */}
      {isMenuOpen && (
        <div className="md:hidden bg-white border-t border-gray-200 shadow-lg">
          <div className="px-4 py-3 space-y-3">
            <Link
              to="/"
              onClick={closeMenu}
              className="block text-gray-700 hover:text-blue-600 transition-colors font-medium py-2"
            >
              Produktet
            </Link>
            <Link
              to="/contact"
              onClick={closeMenu}
              className="block text-gray-700 hover:text-blue-600 transition-colors font-medium py-2"
            >
              Kontakti
            </Link>

            {/* Mobile Cart */}
            <Link
              to="/cart"
              onClick={closeMenu}
              className="flex items-center justify-between py-2 border-t border-gray-200 pt-3"
            >
              <div className="flex items-center space-x-2">
                <ShoppingCart className="w-5 h-5 text-gray-700" />
                <span className="text-gray-700 font-medium">Shporta</span>
              </div>
              <div className="flex items-center space-x-2">
                <span className="text-sm font-bold text-blue-600">
                  €{cartTotal.toFixed(2)}
                </span>
                {cartItemsCount > 0 && (
                  <span className="bg-blue-600 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center">
                    {cartItemsCount}
                  </span>
                )}
              </div>
            </Link>

            <div className="border-t border-gray-200 pt-3">
              {user ? (
                <div className="space-y-3">
                  <Link
                    to="/profile"
                    onClick={closeMenu}
                    className="flex items-center space-x-3 text-gray-700 hover:text-blue-600 transition-colors font-medium py-2"
                  >
                    <User className="w-5 h-5" />
                    <span>Profili Im</span>
                  </Link>
                  {/* Porositë e Mia temporarily removed from mobile menu */}
                  <button
                    onClick={() => {
                      handleLogout();
                      closeMenu();
                    }}
                    className="flex items-center space-x-3 text-red-600 hover:bg-red-50 transition-colors w-full font-medium py-2 rounded-md"
                  >
                    <LogOut className="w-5 h-5" />
                    <span>Dilni</span>
                  </button>
                </div>
              ) : (
                <div className="space-y-2">
                  <Link
                    to="/login"
                    onClick={closeMenu}
                    className="block text-center px-4 py-2 text-gray-700 hover:text-blue-600 transition-colors font-medium"
                  >
                    Kyçu
                  </Link>
                  <Link
                    to="/signup"
                    onClick={closeMenu}
                    className="block text-center px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all font-medium shadow-md hover:shadow-lg"
                  >
                    Regjistrohu
                  </Link>
                </div>
              )}
            </div>
          </div>
        </div>
      )}
    </nav>
  );
}

export default Navbar;
