const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || "/backend";

export const api = {
  // Products
  async getProducts() {
    try {
      const response = await fetch(`${API_BASE_URL}/products.php`);
      const data = await response.json();
      return data;
    } catch (error) {
      console.error("Error fetching products:", error);
      throw error;
    }
  },

  async getProduct(id) {
    try {
      const response = await fetch(`${API_BASE_URL}/products.php?id=${id}`);
      const data = await response.json();
      return data;
    } catch (error) {
      console.error("Error fetching product:", error);
      throw error;
    }
  },

  // Auth
  login: async (email, password) => {
    try {
      const response = await fetch(`${API_BASE_URL}/login.php`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        credentials: "include",
        body: JSON.stringify({ email, password }),
      });

      const data = await response.json();

      if (!response.ok) {
        throw new Error(data.error || "Login failed");
      }

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
    country = ""
  ) => {
    try {
      const response = await fetch(`${API_BASE_URL}/register.php`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        credentials: "include",
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

      return data;
    } catch (error) {
      console.error("Registration error:", error);
      throw error;
    }
  },

  getProfile: async () => {
    try {
      const response = await fetch(`${API_BASE_URL}/profile.php`, {
        method: "GET",
        headers: {
          "Content-Type": "application/json",
        },
        credentials: "include",
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
      const response = await fetch(`${API_BASE_URL}/profile.php`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        credentials: "include",
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

  logout: async () => {
    try {
      const response = await fetch(`${API_BASE_URL}/logout.php`, {
        method: "POST",
        credentials: "include",
      });

      return response.ok;
    } catch (error) {
      console.error("Logout error:", error);
      return false;
    }
  },
};
