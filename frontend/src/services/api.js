/**
 * API Service — Laravel Backend
 *
 * This file replaces the original api.js that pointed to plain PHP endpoints.
 * It now targets the Laravel API routes and uses Sanctum bearer-token
 * authentication instead of PHP session cookies.
 *
 * Environment variable: VITE_API_BASE_URL  (default: /api)
 */

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || "/api";

/** Read / write the Sanctum token in localStorage */
const getToken = () => localStorage.getItem("auth_token");
const setToken = (token) => {
  if (token) localStorage.setItem("auth_token", token);
  else localStorage.removeItem("auth_token");
};

/** Build default headers, injecting the bearer token when available. */
const authHeaders = (extra = {}) => {
  const headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
    ...extra,
  };
  const token = getToken();
  if (token) headers["Authorization"] = `Bearer ${token}`;
  return headers;
};

export const api = {
  // -------------------------------------------------------------------
  // Products (public)
  // -------------------------------------------------------------------
  async getProducts() {
    try {
      const response = await fetch(`${API_BASE_URL}/products`);
      const data = await response.json();
      return data;
    } catch (error) {
      console.error("Error fetching products:", error);
      throw error;
    }
  },

  async getProduct(id) {
    try {
      const response = await fetch(`${API_BASE_URL}/products/${id}`);
      const data = await response.json();
      return data;
    } catch (error) {
      console.error("Error fetching product:", error);
      throw error;
    }
  },

  // -------------------------------------------------------------------
  // Auth
  // -------------------------------------------------------------------
  login: async (email, password) => {
    try {
      const response = await fetch(`${API_BASE_URL}/login`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
        },
        body: JSON.stringify({ email, password }),
      });

      const data = await response.json();

      if (!response.ok) {
        throw new Error(data.error || "Login failed");
      }

      // Store the Sanctum token for subsequent authenticated requests
      if (data.token) setToken(data.token);

      return data;
    } catch (error) {
      console.error("Login error:", error);
      throw error;
    }
  },

  register: async (
    email,
    password,
    name,
    phone = "",
    address = "",
    city = "",
    country = "",
  ) => {
    try {
      const response = await fetch(`${API_BASE_URL}/register`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
        },
        body: JSON.stringify({
          email,
          password,
          name,
          phone,
          address,
          city,
          country,
        }),
      });

      const data = await response.json();

      if (!response.ok) {
        throw new Error(data.message || "Registration failed");
      }

      // Auto-store token after registration
      if (data.token) setToken(data.token);

      return data;
    } catch (error) {
      console.error("Registration error:", error);
      throw error;
    }
  },

  // -------------------------------------------------------------------
  // Profile (authenticated)
  // -------------------------------------------------------------------
  getProfile: async () => {
    try {
      const response = await fetch(`${API_BASE_URL}/profile`, {
        method: "GET",
        headers: authHeaders(),
      });

      const data = await response.json();

      if (!response.ok) {
        throw new Error(data.error || "Failed to fetch profile");
      }

      return data;
    } catch (error) {
      console.error("Get profile error:", error);
      throw error;
    }
  },

  updateProfile: async (profileData) => {
    try {
      const response = await fetch(`${API_BASE_URL}/profile`, {
        method: "POST",
        headers: authHeaders(),
        body: JSON.stringify(profileData),
      });

      const data = await response.json();

      if (!response.ok) {
        throw new Error(data.error || "Failed to update profile");
      }

      return data;
    } catch (error) {
      console.error("Update profile error:", error);
      throw error;
    }
  },

  // -------------------------------------------------------------------
  // Logout
  // -------------------------------------------------------------------
  logout: async () => {
    try {
      const response = await fetch(`${API_BASE_URL}/logout`, {
        method: "POST",
        headers: authHeaders(),
      });

      // Always clear the local token regardless of server response
      setToken(null);

      return response.ok;
    } catch (error) {
      console.error("Logout error:", error);
      setToken(null);
      return false;
    }
  },
};
