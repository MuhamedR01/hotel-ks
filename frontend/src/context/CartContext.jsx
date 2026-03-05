import { createContext, useContext, useState, useEffect } from "react";

const CartContext = createContext();

export const useCart = () => {
  const context = useContext(CartContext);
  if (!context) {
    throw new Error("useCart must be used within a CartProvider");
  }
  return context;
};

export const CartProvider = ({ children }) => {
  const [cart, setCart] = useState(() => {
    const savedCart = localStorage.getItem("cart");
    if (savedCart) {
      try {
        const parsedCart = JSON.parse(savedCart);
        // Sanitize any legacy items that may contain large base64 blobs
        const sanitized = (Array.isArray(parsedCart) ? parsedCart : [])
          .map((item) => {
            const imageVal =
              typeof item?.image === "string" ? item.image : null;
            const imageToStore =
              imageVal &&
              typeof imageVal === "string" &&
              imageVal.startsWith("data:")
                ? null
                : imageVal;

            const idNum = Number(item?.id) || 0;
            const priceNum = parseFloat(item?.price) || 0;
            const qtyNum = Number(item?.quantity) || 1;

            return {
              id: idNum,
              name: item?.name || "",
              price: priceNum,
              image: imageToStore,
              description: item?.description
                ? String(item.description).slice(0, 200)
                : "",
              selectedSize: item?.selectedSize
                ? String(item.selectedSize)
                : null,
              quantity: qtyNum,
            };
          })
          // Filter out obviously invalid legacy items (id must be an integer >= 0, price finite, quantity >=1)
          .filter(
            (it) =>
              Number.isInteger(it.id) &&
              it.id >= 0 &&
              Number.isFinite(it.price) &&
              it.quantity >= 1
          );

        return sanitized;
      } catch (error) {
        console.error("Error parsing cart:", error);
        return [];
      }
    }
    return [];
  });

  useEffect(() => {
    const payload = JSON.stringify(cart);
    try {
      localStorage.setItem("cart", payload);
    } catch (err) {
      console.error("Failed to save cart to localStorage:", err);
      try {
        sessionStorage.setItem("cart", payload);
        if (
          typeof window !== "undefined" &&
          window &&
          !window._cartQuotaWarnShown
        ) {
          window._cartQuotaWarnShown = true;
          alert(
            "Kujdes: Ruajtja e shportës në ruajtjen lokale dështoi (kapacitet i tejkaluar). Shporta do të ruhet vetëm gjatë sesionit të tanishëm."
          );
        }
      } catch (err2) {
        console.error("Failed to save cart to sessionStorage:", err2);
      }
    }
  }, [cart]);

  const addToCart = (product, selectedSize = null) => {
    console.log("Adding to cart:", { id: product?.id, selectedSize }); // Debug log (sanitized)

    const prodId = Number(product?.id);
    if (!Number.isInteger(prodId) || prodId < 0) {
      console.error("addToCart called with invalid product id:", product);
      return; // ignore invalid product additions
    }

    setCart((prevCart) => {
      // Check if item with same product id and size already exists
      const existingItemIndex = prevCart.findIndex((item) => {
        // Both must match: normalized product ID and size (or both have no size)
        if (selectedSize) {
          return (
            item.id === prodId &&
            String(item.selectedSize) === String(selectedSize)
          );
        } else {
          return item.id === prodId && !item.selectedSize;
        }
      });

      if (existingItemIndex > -1) {
        // Item exists, update quantity
        const updatedCart = [...prevCart];
        updatedCart[existingItemIndex] = {
          ...updatedCart[existingItemIndex],
          quantity:
            updatedCart[existingItemIndex].quantity +
            (Number(product.quantity) || 1),
        };
        console.log("Updated existing item:", updatedCart[existingItemIndex]); // Debug log
        return updatedCart;
      } else {
        // Item doesn't exist, add new item — sanitize stored data to avoid large blobs
        const imageVal =
          typeof product?.image === "string"
            ? product.image
            : Array.isArray(product?.images) && product.images[0]
            ? product.images[0]
            : null;
        const imageToStore =
          typeof imageVal === "string" && imageVal.startsWith("data:")
            ? null
            : imageVal;

        const newItem = {
          id: prodId,
          name: product.name || "",
          price: parseFloat(product.price) || 0,
          image: imageToStore,
          description: product.description
            ? String(product.description).slice(0, 200)
            : "",
          selectedSize: selectedSize ? String(selectedSize) : null,
          quantity: Number(product.quantity) || 1,
        };
        console.log("Adding new item:", {
          id: newItem.id,
          selectedSize: newItem.selectedSize,
        });
        return [...prevCart, newItem];
      }
    });
  };

  const removeFromCart = (productId, selectedSize = null) => {
    setCart((prevCart) => {
      return prevCart.filter((item) => {
        // Remove item that matches both product ID and size
        if (selectedSize) {
          return !(item.id === productId && item.selectedSize === selectedSize);
        } else {
          return !(item.id === productId && !item.selectedSize);
        }
      });
    });
  };

  const updateQuantity = (productId, selectedSize, newQuantity) => {
    if (newQuantity < 1) return;

    setCart((prevCart) => {
      return prevCart.map((item) => {
        // Match by both product ID and size
        if (item.id === productId) {
          if (selectedSize) {
            // If size is provided, match it exactly
            if (item.selectedSize === selectedSize) {
              return { ...item, quantity: newQuantity };
            }
          } else {
            // If no size provided, match items without size
            if (!item.selectedSize) {
              return { ...item, quantity: newQuantity };
            }
          }
        }
        return item;
      });
    });
  };

  const clearCart = () => {
    setCart([]);
    localStorage.removeItem("cart");
  };

  const getCartTotal = () => {
    return cart.reduce((total, item) => {
      return total + parseFloat(item.price) * item.quantity;
    }, 0);
  };

  const getCartCount = () => {
    return cart.reduce((count, item) => count + item.quantity, 0);
  };

  return (
    <CartContext.Provider
      value={{
        cart,
        addToCart,
        removeFromCart,
        updateQuantity,
        clearCart,
        getCartTotal,
        getCartCount,
      }}
    >
      {children}
    </CartContext.Provider>
  );
};
