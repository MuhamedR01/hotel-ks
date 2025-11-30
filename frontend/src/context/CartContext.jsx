
import { createContext, useContext, useState, useEffect } from 'react'

const CartContext = createContext()

export const useCart = () => {
  const context = useContext(CartContext)
  if (!context) {
    throw new Error('useCart must be used within a CartProvider')
  }
  return context
}

export const CartProvider = ({ children }) => {
  const [cart, setCart] = useState(() => {
    const savedCart = localStorage.getItem('cart')
    if (savedCart) {
      try {
        const parsedCart = JSON.parse(savedCart)
        return parsedCart
      } catch (error) {
        console.error('Error parsing cart:', error)
        return []
      }
    }
    return []
  })

  useEffect(() => {
    localStorage.setItem('cart', JSON.stringify(cart))
  }, [cart])

  const addToCart = (product, selectedSize = null) => {
    console.log('Adding to cart:', { product, selectedSize }) // Debug log
    
    setCart(prevCart => {
      // Check if item with same product id and size already exists
      const existingItemIndex = prevCart.findIndex(item => {
        // Both must match: product ID and size (or both have no size)
        if (selectedSize) {
          return item.id === product.id && item.selectedSize === selectedSize
        } else {
          return item.id === product.id && !item.selectedSize
        }
      })

      if (existingItemIndex > -1) {
        // Item exists, update quantity
        const updatedCart = [...prevCart]
        updatedCart[existingItemIndex] = {
          ...updatedCart[existingItemIndex],
          quantity: updatedCart[existingItemIndex].quantity + (product.quantity || 1)
        }
        console.log('Updated existing item:', updatedCart[existingItemIndex]) // Debug log
        return updatedCart
      } else {
        // Item doesn't exist, add new item
        const newItem = {
          ...product,
          selectedSize: selectedSize,
          quantity: product.quantity || 1
        }
        console.log('Adding new item:', newItem) // Debug log
        return [...prevCart, newItem]
      }
    })
  }

  const removeFromCart = (productId, selectedSize = null) => {
    setCart(prevCart => {
      return prevCart.filter(item => {
        // Remove item that matches both product ID and size
        if (selectedSize) {
          return !(item.id === productId && item.selectedSize === selectedSize)
        } else {
          return !(item.id === productId && !item.selectedSize)
        }
      })
    })
  }

  const updateQuantity = (productId, selectedSize, newQuantity) => {
    if (newQuantity < 1) return

    setCart(prevCart => {
      return prevCart.map(item => {
        // Match by both product ID and size
        if (item.id === productId) {
          if (selectedSize) {
            // If size is provided, match it exactly
            if (item.selectedSize === selectedSize) {
              return { ...item, quantity: newQuantity }
            }
          } else {
            // If no size provided, match items without size
            if (!item.selectedSize) {
              return { ...item, quantity: newQuantity }
            }
          }
        }
        return item
      })
    })
  }

  const clearCart = () => {
    setCart([])
    localStorage.removeItem('cart')
  }

  const getCartTotal = () => {
    return cart.reduce((total, item) => {
      return total + (parseFloat(item.price) * item.quantity)
    }, 0)
  }

  const getCartCount = () => {
    return cart.reduce((count, item) => count + item.quantity, 0)
  }

  return (
    <CartContext.Provider value={{
      cart,
      addToCart,
      removeFromCart,
      updateQuantity,
      clearCart,
      getCartTotal,
      getCartCount
    }}>
      {children}
    </CartContext.Provider>
  )
}
